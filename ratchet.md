# Using the WebSocket Ratchet with Symfony

## Installation

1. Ratchet
    - `composer require cboden/ratchet`
2. Lexik JWT (used to validate user through WebSocket)
    - `composer require lexik/jwt-authentication-bundle`
    - `php bin/console lexik:jwt:generate-keypair`
3. KNP time bundle (Used to format Entity objects dates such as `createdAt` & `updatedAt` dates) 
    - `composer require knplabs/knp-time-bundle`

## Add WebSocket Exception file

```php
<?php
# src/Security/Exception
namespace App\Security\Exception;

class WebSocketInvalidRequestException extends \Exception
{
    public const FATAL_ERROR = true;
    public const NOT_FATAL_ERROR = false;

    public function __construct(string $message = 'Invalid request', private bool $isFatal = self::NOT_FATAL_ERROR)
    {
        parent::__construct($message);
    }

    public function isFatal()
    {
        return $this->isFatal;
    }
}
```

## Create a Notification handler file

This template file validate the user connecting with th WebSocket is registered & logged in.
Copy this file & add your own logic in onMessage() (Data checks, dispatching actions, sending responses).

```php
<?php
# src/WebSocket
namespace App\WebSocket;

use App\Controller\ConversationController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Exception\WebSocketInvalidRequestException;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\TimeBundle\DateTimeFormatter;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Notification implements MessageComponentInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private JWTTokenManagerInterface $JWTManager,
        private DateTimeFormatter $dateTimeFormatter,
        private $clients = new \SplObjectStorage,
    ) {
    }

    public function onOpen(ConnectionInterface $conn) {
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $query);

        $token = new JWTUserToken();
        $token->setRawToken($query['token'] ?? null);

        try {
            $payload = $this->JWTManager->decode($token);
        } catch (\Exception $e) {
            throw new WebSocketInvalidRequestException(isFatal:  WebSocketInvalidRequestException::FATAL_ERROR);            
        }

        $user = $this->getUserByEmail($payload['username']);

        if (null === $user) {
            throw new WebSocketInvalidRequestException(isFatal:  WebSocketInvalidRequestException::FATAL_ERROR);
        }

        $conn->userId = $user->getId();

        $this->clients->attach($conn);
        echo "New connection ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Clear previous cache
        $this->entityManager->clear();

        //? Checking datas
        if (!is_string($msg)) {
            throw new WebSocketInvalidRequestException(isFatal:  WebSocketInvalidRequestException::FATAL_ERROR);
        }
        $messageData = json_decode(trim($msg));

        if (!isset($messageData->action) || !isset($messageData->data)) {
            throw new WebSocketInvalidRequestException(isFatal:  WebSocketInvalidRequestException::FATAL_ERROR);
        }

        //? Dispatching action
        $data = null;

        switch ($messageData->action) {
            case 'action':
                // Do action
                $data = "data to send";
                break;
            default:
                throw new WebSocketInvalidRequestException;
                break;
        }

        //? Sending response
        foreach ($this->clients as $client) {
            // TODO Add a condition if you don't want to send everyone a response (e.g. Using $client->userId)
            $this->send($client, $messageData->action, $data);
        }

        echo "Message {$messageData->action} sent by connection {$from->resourceId}\n";
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        if ($e instanceof WebSocketInvalidRequestException && !$e->isFatal()) {
            $this->send($conn, 'error', "{\"message\":\"{$e->getMessage()}\"}");
            return;
        }
        
        $this->send($conn, 'fatalError', "{\"message\":\"{$e->getMessage()}\"}");
        $conn->close();
        echo "An error has occurred: {$e->getMessage()}\n";
    }

    /**
     * @param string $email
     * @return User|null
     */
    private function getUserByEmail(string $email)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        return $userRepository->findOneBy(['email' => $email]);
    }

    private function send(ConnectionInterface $conn, string $action, string $data)
    {
        $conn->send(json_encode([
            'action' => $action,
            'data' => $data,
        ]));
    }
}
```

## Create a command to start the WebSocket server

```php
<?php
# src/Command
namespace App\Command;

use App\WebSocket\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\TimeBundle\DateTimeFormatter;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:wsserver:start',
    description: 'Start the notification web socket server',
)]
class StartWSServerCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private JWTTokenManagerInterface $JWTManager,
        private DateTimeFormatter $dateTimeFormatter,
    ) {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function execute(OutputInterface $output)
    {
        //! If changing the port, make sure to change to port used in unit tests
        $port = 8080;
        $output->writeln("Starting server on port " . $port);

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new Notification(
                        $this->entityManager, 
                        $this->JWTManager, 
                        $this->dateTimeFormatter
                    )
                )
            ),
            $port
        );

        $server->run();
    }
}
```

## Start the WebSocket connection in your JavaScript file

Controller :

```php
#[Route('/', name: 'app_main_home')]
public function home(JWTTokenManagerInterface $JWTManager): Response
{
    /** @var User $user */
    return $this->render('main/home.html.twig', [
        'token' => $JWTManager->create($user),
    ]);
}
```

Twig template :

```html
<script id="JS-CONST">
    const BASE_URL = {{ app.request.server.get('SERVER_NAME')|json_encode|raw }};
    const JWTToken = {{ token|json_encode|raw }};
</script>
```

JavaScript :

```javascript
const webSocket = {
    init: function () {
        this.conn = new WebSocket(`ws://${BASE_URL}:8080?token=${JWTToken}`);
        this.initConn();

        console.log('Messaging websocket OK')
    },
    initConn: function () {
        this.conn.onopen = () => this.handleWebSocketOpen();
        this.conn.onclose = () => this.handleWebSocketClose();
        this.conn.onmessage = (event) => this.handleWebSocketMessage(event);
    },
    conn: null,
    fatalErrorMessage: null,
    send: function (action, data) {
        if (1 !== webSocket.conn.readyState) return;
    
        webSocket.conn.send(
            JSON.stringify({
                action: action, 
                data: data, 
            })
        );
    },
    handleWebSocketOpen: function () {
        console.log("Connexion établie !");
    },
    handleWebSocketClose: function () {
        console.log("Connexion interrompue !");
    },
    handleWebSocketMessage: async function (event) {
        const message = JSON.parse(event.data);
        const data = JSON.parse(message.data);
    
        switch (message.action) {
            case 'show':
                // Do something
                break;
            case 'fatalError':
                this.fatalErrorMessage = data.message;
                break;
        }
    },
};

export default webSocket;
```

That's it, when using `php bin/console app:wsserver:start` your WebSocket server should be running and reachable through this page. 🚀

## Extra : Add a WebSocketCoreController for a better serialization support in controllers

```php
<?php
# src/Controller
namespace App\Controller;

use Doctrine\Common\Annotations\AnnotationReader;
use Knp\Bundle\TimeBundle\DateTimeFormatter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class WebSocketCoreController
{
    /**
     * Serialize $data according to the given serialization $group
     *
     * @param mixed $data The data that need to be serialized
     * @param string $group The serialization group
     * @param DateTimeFormatter $dateTimeFormatter Instance of DateTimeFomatter that will be used to format dates
     * @return string
     */
    protected static function serialize(mixed $data, string $group, DateTimeFormatter $dateTimeFormatter): string
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $dateCallback = function ($innerObject) use ($dateTimeFormatter) {
            return ($innerObject instanceof \DateTime && null !== $innerObject) ? $dateTimeFormatter->formatDiff($innerObject, new \DateTime()) : '';
        };
        
        $defaultContext = [
            AbstractNormalizer::CALLBACKS => [
                'createdAt' => $dateCallback,
                'updatedAt' => $dateCallback,
            ],
        ];

        $normalizer = new ObjectNormalizer($classMetadataFactory, defaultContext: $defaultContext);
        $encoder = new JsonEncoder();
        $serializer = new Serializer([$normalizer], [$encoder]);

        return $serializer->serialize($data, 'json', ['groups' => $group]);
    }

    /**
     * Serialize constraint violation list into array of error message
     *
     * @param ConstraintViolationListInterface $errors
     * @return string
     */
    protected static function serializeErrors(ConstraintViolationListInterface $errors): string
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return json_encode($errorMessages);
    }
}
```

Then in controllers you should extend this controller & return serialized data to the Notification handler.

E.g. : Showing a product

```php
<?php
# src/Controller
namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Security\Exception\WebSocketInvalidRequestException;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\TimeBundle\DateTimeFormatter;

class ProductController extends WebSocketCoreController
{
    public static function show(int $id, EntityManagerInterface $entityManager, DateTimeFormatter $dateTimeFormatter): string
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $entityManager->getRepository(Product::class);

        $product = $productRepository->find($id);

        // Some tests
        if (null === $product) {
            throw new WebSocketInvalidRequestException('Vous ne pouvez pas afficher la discussion d\'un utilisateur bloqué.');
        }

        return self::serialize($conversation, 'api_product_light', $dateTimeFormatter);
    }
}
```

In Notification.php, in action dispatcher :

```php
case 'show':
    $data = ProductController::show($messageData->data['id'], $this->entityManager, $this->dateTimeFormatter);
    break;
```

## Extra : Unit testing

To test your WebSocket features, you can use this file. <br>
Connection variables are accessible via the `getWSConst()` method. This file also contains a test that asserts errors on invalid connections.

```php
<?php
# tests/
namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use WebSocket\Client;

class WebSocketTest
{
    // !
    // ! THESES TESTS NEED following command to work
    // ! bin/console --env=test app:wsserver:start
    // !
    public function testInvalidConnexion(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        
        $user = new User();
        $user
            ->setPseudo('test')
            ->setEmail(uniqid().'mail@mail.com')
            ->setRoles(['ROLE_USER'])
            ->setPassword('1234')
        ;
        $userRepository->save($user, true);

        [$baseUrl, $JWTToken] = $this->getWSConst($client, $user);

        //? Invalid JWT token
        $WSClientInvalidJWT = new Client("ws://$baseUrl:8080?token=invalid_jwt_token");

        $WSClientInvalidJWT->send('test');
        $response = $WSClientInvalidJWT->receive();

        $this->assertJson($response);
        $action = json_decode($response)->action;
        
        $this->assertSame('fatalError', $action);

        //? Missing action
        $WSClient = new Client("ws://$baseUrl:8080?token=$JWTToken");

        $WSClient->send(json_encode(
            [
                // 'action' => 'test',
                'data' => 'test',
            ]
        ));

        $response = $WSClient->receive();

        $this->assertJson($response);
        $action = json_decode($response)->action;
        
        $this->assertSame('fatalError', $action);

        //? Missing data
        $WSClient = new Client("ws://$baseUrl:8080?token=$JWTToken");

        $WSClient->send(json_encode(
            [
                'action' => 'test',
                // 'data' => 'test',
            ]
        ));

        $response = $WSClient->receive();

        $this->assertJson($response);
        $action = json_decode($response)->action;
        
        $this->assertSame('fatalError', $action);


        //? User is null (User got deleted since last message)
        $WSClient = new Client("ws://$baseUrl:8080?token=$JWTToken");

        $userRepository->remove($user, true);

        $WSClient->send(json_encode(
            [
                'action' => 'test',
                'data' => 'test',
            ]
        ));

        $response = $WSClient->receive();

        $this->assertJson($response);
        $action = json_decode($response)->action;
        
        $this->assertSame('fatalError', $action);
    }
   
    /**
     * Return Web socket constants for given user
     *
     * @param User $user
     * @return array[string $baseUrl, string $JWTToken]
     */
    private function getWSConst(KernelBrowser $client, User $user): array
    {        
        $client->loginUser($user);
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $javaScriptConst = $crawler->filter('script#JS-CONST')->extract(['_text'])[0];
        $javaScriptConstArray = explode("\n", trim($javaScriptConst));

        preg_match('/const BASE_URL = "(.*)";/', $javaScriptConstArray[0], $baseUrlResults);
        preg_match('/const JWTToken = "(.*)";/', $javaScriptConstArray[1], $JWTTokenResults);

        $baseUrl = $baseUrlResults[1];
        $JWTToken = $JWTTokenResults[1];

        return [$baseUrl, $JWTToken];
    }

}
```

## Extra : Docker

If you are using Docker and want a container for your WebSocket, add this to your `compose.yaml`.

```yaml
websocket-server:
    build: .
    command: php bin/console app:wsserver:start
```
