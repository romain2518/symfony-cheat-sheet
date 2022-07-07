# Symfony cheat sheet

## [Useful commands](useful-commands.md)

## Non-API

| What | How to create/use | Extra |
| --- | --- | --- |
| Database | `bin/console doctrine:database:create` | --- |
| Controller | `bin/console make:controller` | [Doc](https://symfony.com/doc/5.4/routing.html)<br> `requirements={"param"="Regex"}` |
| Entitie | `bin/console make:migration`<br>`bin/console doctrine:migrations:migrate` | When default repository methods are not enough : DQL and Query builder |
| Fixture | `bin/console make:fixtures`<br>`bin/console doctrine:fixtures:load` | --- |
| CRUD | `bin/console make:crud` | [Activate bootstrap style](https://symfony.com/doc/current/form/bootstrap5.html)<br>Check forms before using.
| Form | `bin/console make:form` | [Validate using annotations or php](https://symfony.com/doc/current/forms.html)<br>[Types reference](https://symfony.com/doc/current/reference/forms/types.html)<br>[Disable HTML5 Validation](https://symfony.com/doc/current/forms.html#client-side-html-validation)<br>[Customize your forms](https://symfony.com/doc/current/form/form_customization.html) |
| User | `bin/console make:user` | --- |
| Authenticator | `bin/console make:auth` | Error : login route doesn't work using Apache => [Fix](error-login-apache.md))  |
| Access control | Write your url patterns in [security.yaml](https://symfony.com/doc/current/security.html#securing-url-patterns-access-control), [controllers](https://symfony.com/doc/current/security.html#securing-controllers-and-other-code), or [using twig](https://symfony.com/doc/current/security.html#access-control-in-templates) | --- |
| Registration | `bin/console make:registration-form` | --- |
| Reset password | `bin/console make:reset-password` | Notice : This uses symfony/mailer.  |
| Voter | `bin/console make:voter` | --- |
| Service | Create it manually | [Doc](https://symfony.com/doc/current/quick_tour/the_architecture.html#creating-services) |
| Command | `bin/conle make:command` | [Doc](https://symfony.com/doc/current/console.html#creating-a-command)<br>Don't forget the `parent::__construct();` 💥
| Event subscriber/listener | Subs : `bin/console make:subscriber`<br>List : manually | [Doc](https://symfony.com/doc/current/event_dispatcher.html), Types of events :<br> - [Kernel](https://symfony.com/doc/current/reference/events.html),<br> - [Form](https://symfony.com/doc/current/form/events.html),<br> - [Doctrine](https://symfony.com/doc/current/doctrine/events.html#doctrine-lifecycle-subscribers) |
| PHPUnit | `composer require phpunit --dev`<br>`bin/console make:test`<br>Start tests with `bin/phpunit` | [Doc assertions](https://phpunit.readthedocs.io/fr/latest/assertions.html)<br>`bin/phpunit --coverage-html ./tests/coverage/2022-07-04` |
| Deploy | [Install](https://deployer.org/docs/6.x/installation)<br>Create your deploy.php file (or use [this one](deploy.php) and follow the TODO comments)<br>`dep first_deploy prod -f deploy.php` ✨ | Don't forget to gitignore deploy.php file !!! 💣 |

## Rest API

<small>[Conventions de code](https://restfulapi.net/resource-naming/)</small>

| What | How to create/use | Extra |
| --- | --- | --- |
| Controllers | `bin/console make:controller --no-template` | --- |
| Serializer | `composer require symfony/serializer`<br>Use [attributes groups](https://symfony.com/doc/5.4/components/serializer.html#attributes-groups) | When deserializing data don't forget to use `try` blocs as we don't know what it looks like... |
| DoctrineDenormalizer | Copy [this file](doctrineDenormalizer.php) to your project | Nothing else to do, it uses chain of responsability and so, will be executed automatically. |
| Lexik JWT | `composer require lexik/jwt-authentication-bundle`<br>`bin/console lexik:jwt:generate-keypair`<br>[Configure](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst#symfony-53-and-higher) | Careful default token time to live is [1 hour](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst#configuration) |
