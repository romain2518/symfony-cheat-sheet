# Useful commands

| What | Command |
|-|-|
| Clear cache | `php bin/console cache:clear` |
| List routes | `php bin/console debug:router` |
| List services | `php bin/console debug:autowiring` |
| Validate schema | `php bin/console doctrine:schema:validate` |
| Update your database according to your schema 🎉 | `php bin/console doctrine:schema:update --force` |
| Regenerate entity & repository | `php bin/console make:entity --regenerate App` |
| Regenerate initial migration | `php bin/console doctrine:migrations:dump-schema` |
| Update the list of tracked migrations in the database based on files | `php bin/console doctrine:migrations:rollup` |
