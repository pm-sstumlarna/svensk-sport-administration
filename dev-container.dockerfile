## Containerfile compatible with Docker and Podman
## Build with: podman build --pull --rm -f dev-container.dockerfile -t svensksportadministration:dev .
## Run with: podman run --rm -it --network host -v "$(pwd):/app" svensksportadministration:dev

FROM php:8-cli

# Install system deps and PHP extensions (pdo, pdo_mysql, pcntl, zip) and xdebug + composer
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git curl unzip libzip-dev default-mysql-client libonig-dev zlib1g-dev libpng-dev libjpeg62-turbo-dev \
 && docker-php-ext-install pdo pdo_mysql pcntl zip \
 && pecl install xdebug \
 && docker-php-ext-enable xdebug \
 && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Expose Xdebug port (9003) for debugging if not using host networking
EXPOSE 9003

CMD ["bash"]