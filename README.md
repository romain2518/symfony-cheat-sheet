# Symfony cheat sheet

## [Useful commands](useful-commands.md)

## Useful files

- [Using the WebSocket Ratchet with Symfony](ratchet.md)
- [Using docker with Symfony](docker.md)
- [Auto update entity date properties with lifecycle events](./lifecycleAutoUpdateDate.md)

## Common packages

| What | How to create/use | Extra |
| --- | --- | --- |
| VichUploader | `composer require vich/uploader-bundle`<br> | [Manual](https://github.com/dustin10/VichUploaderBundle/tree/master)<br>[Basic usage](./vichBasicUsage.md) |
| PHPUnit | `composer require --dev symfony/test-pack`<br>`php bin/console make:test`<br>Start tests with `php bin/phpunit` | [Doc assertions](https://symfony.com/doc/current/testing.html#testing-the-response-assertions) |
| Deployer | `composer require --dev symfony/test-pack`<br>Create your deploy.php file (or use [this one](deploy.php) and follow the TODO comments)<br>`dep first_deploy prod -f deploy.php` ✨ | Don't forget to gitignore deploy.php file !!! 💣 |

## Non-API

| What | How to create/use | Extra |
| --- | --- | --- |
| Project skeleton | Symfony only : `composer create-project symfony/skeleton:"8.0.*" my_project_directory`<br>Symfony with Docker : [See this guide](docker.md)<br>If webapp `composer require webapp` | |
| Controller | `php bin/console make:controller` | [Parameters validation](https://symfony.com/doc/current/routing.html#parameters-validation) |
| Entity | `php bin/console make:entity` | When default repository methods are not enough : DQL and Query builder |
| Fixture | `php bin/console make:fixtures`<br>`php bin/console doctrine:fixtures:load` | |
| CRUD | `php bin/console make:crud` | [Activate bootstrap style](https://symfony.com/doc/current/form/bootstrap5.html)<br>Check forms before using. |
| Form | `php bin/console make:form` | [Validation constraints reference](https://symfony.com/doc/current/reference/constraints.html)<br>[Form types reference](https://symfony.com/doc/current/reference/forms/types.html)<br>[Customize form rendering](https://symfony.com/doc/current/form/form_customization.html) |
| User | `php bin/console make:user` | |
| Registration | `php bin/console make:registration-form` | |
| Login | `make:security:form-login` | |
| Access control | Write your url patterns in [security.yaml](https://symfony.com/doc/current/security.html#securing-url-patterns-access-control), [controllers](https://symfony.com/doc/current/security.html#securing-controllers-and-other-code), or [using twig](https://symfony.com/doc/current/security.html#access-control-in-templates) | |
| Reset password | `php bin/console make:reset-password` | Notice : This uses symfony/mailer. |
| Voter | `php bin/console make:voter` | |
| Service | Create it manually | [Doc](https://symfony.com/doc/current/quick_tour/the_architecture.html#creating-services) |
| Command | `php bin/console make:command` | [Doc](https://symfony.com/doc/current/console.html#creating-a-command) |
| Event subscriber<br>Event listener | `php bin/console make:subscriber`<br>`php bin/console make:listener` | [Doc](https://symfony.com/doc/current/event_dispatcher.html), Types of events :<br> - [Kernel](https://symfony.com/doc/current/reference/events.html),<br> - [Form](https://symfony.com/doc/current/form/events.html),<br> - [Doctrine](https://symfony.com/doc/current/doctrine/events.html#doctrine-lifecycle-subscribers) |
| Customize error pages | `composer require symfony/serializer-pack`<br>Add your template in `template/bundles/TwigBundle/Exception/error404.html.twig` | [Doc](https://symfony.com/doc/current/controller/error_pages.html) |

## Rest API

[REST API best practices](https://restfulapi.net/resource-naming/)

| What | How to create/use | Extra |
| --- | --- | --- |
| Controllers | `php bin/console make:controller --no-template` | |
| Serializer | `composer require symfony/serializer-pack` | [Doc](https://symfony.com/doc/current/serializer.html) |
| Lexik JWT | `composer require lexik/jwt-authentication-bundle`<br>`php bin/console lexik:jwt:generate-keypair`<br>[Configure](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst#symfony-53-and-higher) | Careful default token time to live is [1 hour](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst#configuration) |
| CORS | `composer require nelmio/cors-bundle`<br>Configure allowed origins in your .env | [Doc](https://github.com/nelmio/NelmioCorsBundle) |

## Security

| What | How to create/use | Extra |
| --- | --- | --- |
| Anti Bruteforce | composer require symfony/rate-limiter<br>Configure login throttling in `config/packages/security.yaml` | [Doc](https://symfony.com/doc/current/security.html#limiting-login-attempts) |
| Access Control List | Configure roles and url access in `config/packages/security.yaml` | [Doc](https://symfony.com/doc/current/security.html#access-control-authorization) |
| App secret | Generate a new secret with `php -r "echo bin2hex(random_bytes(32));"` | Secret should be regenerated when deploying |

## Useful commands

| What | Command |
| - | - |
| Clear cache | `php bin/console cache:clear` |
| List routes | `php bin/console debug:router` |
| List services | `php bin/console debug:autowiring` |
| Validate schema | `php bin/console doctrine:schema:validate` |
| Regenerate initial migration | `php bin/console doctrine:migrations:dump-schema` |
| Regenerate entity & repository after adding a property manually | `php bin/console make:entity --regenerate App` |