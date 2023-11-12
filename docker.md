# Using docker with Symfony

Symfony comes with a compose.yaml & compose.override.yaml that contains database (PostgreSQL) & mail dev interface containers.

## Implementation steps

1. Duplicate .ignore in a .dockerignore
2. Add app container to compose.yaml (other container exemples at the end of the file)

    ```yaml
    # compose.yaml

    web-app:
        build: . 
        ports:
        - "8000:80"
        depends_on:
        - database
        volumes:
        # Bind-mount web-app source code
        - ./:/var/www/app:rw
    ```

3. Override database environment variables if needed

    ```properties
    # .env.local
    
    ###> Docker POSTGRES Container
    POSTGRES_VERSION=15
    POSTGRES_DB=app_db
    POSTGRES_USER=app_user
    POSTGRES_PASSWORD=app_password
    ###< Docker POSTGRES Container
    ```

4. Add a Dockerfile

    ```dockerfile
    # Dockerfile

    # Use PHP Apache base image
    FROM php:8.2-apache

    # Give permissions to the /var directory for apache user/group (www-data)
    RUN chown -R www-data:www-data /var

    # Install Composer globally
    RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

    # Install additional dependencies, enable the zip extension, and clean up
    RUN apt-get update \
        && apt-get install -y \
            libzip-dev \
            unzip \
            p7zip-full \
            git \
        && docker-php-ext-install zip \
        # Clean up
        && rm -rf /var/lib/apt/lists/*

    WORKDIR /var/www/app

    COPY . .

    # Set Composer to allow superuser permissions
    ENV COMPOSER_ALLOW_SUPERUSER 1

    # Run Composer to install project dependencies
    RUN composer install

    # Set the ServerName in Apache configuration
    RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

    # Update the Apache virtual host configuration to set the DocumentRoot of the app
    RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/app/public|' /etc/apache2/sites-available/000-default.conf
    ```

5. Create & start the containers

    ```bash
    # Using .env.local to override database environment variables
    docker compose --env-file .env.local up

    # Or without overriding database environment variables
    docker compose up
    ```

Ready to go ! ✨

## Basic commands

| Command | Description | Options |
|-|-|-|
| docker build [Dockerfile] | Build an image from a Dockerfile | Tag (set a name) : `-t username/appname:1.0` |
| docker run [image] | Run a container | Publish (Port forward) : `-p [host_port]:[container_port]` <br>Volume (Mount volume) : `-v [host_path]:[container_path]` |
| docker compose up | Create & starts containers | Environment file : `--env-file [envFilePath]`<br> Build (Rebuild images before starting) : `--build` <br> Detach (Run container in background) `-d` |
| docker compose down | Stop & remove containers | |
| docker push [image] | Push an image to DockerHub | |
| docker pull [image] | Pull an image from DockerHub | |

## Common containers

Adminer :

```yaml
adminer:
    image: adminer:latest
    ports:
      - "8080:8080"
    depends_on:
      - database
```

WebSocket Server (Replace the command by your own command) :

```yaml
websocket-server:
    build: .
    command: php bin/console app:wsserver:start
```

Mail consumer :

```yaml
mail-observer:
    build: .
    command: php bin/console messenger:consume async
```
