# Using docker with Symfony

This sheet describes a modern and sustained usecase of Docker on a new Symfony project, using official docker-based installer [Symfony Docker](https://github.com/dunglas/symfony-docker).

## Quick setup

Use [this script](./create-symfony-project.sh) to create and run a Docker-based Symfony project with GitHub integration. You can also add it to your environment variables to make it easier to use from anywhere.<br>
Usage: `create-symfony-project <project-name> <git-url>`

## Implementation steps

1. Clone Symfony Docker

    ```bash
    git clone git@github.com:dunglas/symfony-docker.git [new-project]
    cd new-project
    ```

2. Clean up

    ```bash
    rm -rf .git* docs/ README.md
    ```

3. Git init

    ```bash
    git init
    git add .
    git commit -m "Initial commit"
    git remote add origin https://github.com/your-username/project.git
    git push -u origin main
    ```

4. Build containers and Symfony skeleton & start

    Dev :

    ```bash
    docker compose build --pull --no-cache
    docker compose up --wait
    ```

    Prod :

    ```bash
    docker compose -f compose.yaml -f compose.prod.yaml build --pull --no-cache

    SERVER_NAME=your-domain-name.example.com \
    APP_SECRET=ChangeMe \
    CADDY_MERCURE_JWT_SECRET=ChangeThisMercureHubJWTSecretKey \
    docker compose -f compose.yaml -f compose.prod.yaml up --wait
    ```

    Ready to go ! ✨

5. Stop containers

    ```bash
    docker compose down --remove-orphans
    ```

## Other commands

| Command | Description | Options |
| - | - | - |
| docker compose logs | Output logs from containers | |
| docker exec [container] [command] | Execute a command in a running container | Interactive : `-i` <br> Terminal : `-t` <br> Run an interactive terminal in a php container : `docker exec -it php bash` |

## Common containers

Adminer :

```yaml
adminer:
  image: adminer
  ports:
    - "8080:8080"
  depends_on:
    - database
```

Mail consumer :

```yaml
mail-observer:
  build: .
  command: php bin/console messenger:consume async
```
