FROM php:8.4-fpm

# Arguments
ARG USER_ID=1000
ARG GROUP_ID=1000

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    pkg-config \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Extensions PHP
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    intl \
    opcache

# Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# MongoDB
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Création d'un utilisateur correspondant à l'utilisateur local
RUN groupadd -g ${GROUP_ID} appgroup \
    && useradd -u ${USER_ID} -g appgroup -m appuser

WORKDIR /var/www/html

# Configuration PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/opstrack.ini

# Permissions
RUN chown -R appuser:appgroup /var/www/html

USER appuser

EXPOSE 9000

CMD ["php-fpm"]