# Symfony cheat sheet

## [Useful commands](useful-commands.md)

## Useful files

- [Using the WebSocket Ratchet with Symfony](ratchet.md)
- [Using docker with Symfony](docker.md)
- [Auto update entity date properties with lifecycle events](./lifecycleAutoUpdateDate.md)
- [Download MailHog server (windows amd64)](./MailHog_windows_amd64.exe)

## Common packages

| What | How to create/use | Extra |
| --- | --- | --- |
| VichUploader | `composer require vich/uploader-bundle`<br> | [Manual](https://github.com/dustin10/VichUploaderBundle/tree/master)<br>[Basic usage](./vichBasicUsage.md) |
| PHPUnit | `composer require phpunit --dev`<br>`php bin/console make:test`<br>Start tests with `bin/phpunit` | [Doc assertions](https://phpunit.readthedocs.io/fr/latest/assertions.html)<br>`bin/phpunit --coverage-html ./tests/coverage/2022-07-04` |
| Deploy | [Install](https://deployer.org/docs/6.x/installation)<br>Create your deploy.php file (or use [this one](deploy.php) and follow the TODO comments)<br>`dep first_deploy prod -f deploy.php` ✨ | Don't forget to gitignore deploy.php file !!! 💣 |

## Non-API

| What | How to create/use | Extra |
| --- | --- | --- |
| Project | `composer create-project symfony/skeleton my_project`<br>`composer create-project symfony/website-skeleton my_project` | Skeleton => lighter app like microservice, console app or API<br>Website skeleton => traditional web app |
| Database | `php bin/console doctrine:database:create` | --- |
| Controller | `php bin/console make:controller` | [Doc](https://symfony.com/doc/5.4/routing.html)<br> `requirements={"param"="Regex"}` |
| Entity | `php bin/console make:migration`<br>`php bin/console doctrine:migrations:migrate` | When default repository methods are not enough : DQL and Query builder |
| Fixture | `php bin/console make:fixtures`<br>`php bin/console doctrine:fixtures:load` | --- |
| CRUD | `php bin/console make:crud` | [Activate bootstrap style](https://symfony.com/doc/current/form/bootstrap5.html)<br>Check forms before using.
| Form | `php bin/console make:form` | [Validate using annotations or php](https://symfony.com/doc/current/forms.html)<br>[Types reference](https://symfony.com/doc/current/reference/forms/types.html)<br>[Disable HTML5 Validation](https://symfony.com/doc/current/forms.html#client-side-html-validation)<br>[Customize your forms](https://symfony.com/doc/current/form/form_customization.html) |
| User | `php bin/console make:user` | --- |
| Authenticator | `php bin/console make:auth` | |
| Access control | Write your url patterns in [security.yaml](https://symfony.com/doc/current/security.html#securing-url-patterns-access-control), [controllers](https://symfony.com/doc/current/security.html#securing-controllers-and-other-code), or [using twig](https://symfony.com/doc/current/security.html#access-control-in-templates) | --- |
| Registration | `php bin/console make:registration-form` | --- |
| Reset password | `php bin/console make:reset-password` | Notice : This uses symfony/mailer.  |
| Voter | `php bin/console make:voter` | --- |
| Service | Create it manually | [Doc](https://symfony.com/doc/current/quick_tour/the_architecture.html#creating-services) |
| Command | `bin/conle make:command` | [Doc](https://symfony.com/doc/current/console.html#creating-a-command)<br>Don't forget the `parent::__construct();` 💥
| Event subscriber/listener | Subs : `php bin/console make:subscriber`<br>List : manually | [Doc](https://symfony.com/doc/current/event_dispatcher.html), Types of events :<br> - [Kernel](https://symfony.com/doc/current/reference/events.html),<br> - [Form](https://symfony.com/doc/current/form/events.html),<br> - [Doctrine](https://symfony.com/doc/current/doctrine/events.html#doctrine-lifecycle-subscribers) |

## Rest API

<small>[Conventions de code](https://restfulapi.net/resource-naming/)</small>

| What | How to create/use | Extra |
| --- | --- | --- |
| Controllers | `php bin/console make:controller --no-template` | --- |
| Serializer | `composer require symfony/serializer`<br>Use [attributes groups](https://symfony.com/doc/5.4/components/serializer.html#attributes-groups) | When deserializing data don't forget to use `try` blocs as we don't know what it looks like... |
| Lexik JWT | `composer require lexik/jwt-authentication-bundle`<br>`php bin/console lexik:jwt:generate-keypair`<br>[Configure](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst#symfony-53-and-higher) | Careful default token time to live is [1 hour](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst#configuration) |
| CORS | `composer require cors`<br>Configure allowed origins in your .env | [Doc](https://github.com/nelmio/NelmioCorsBundle) |
