FROM php:8.4-cli

# Install system libraries required for PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        unzip \
        git \
    && docker-php-ext-install \
        intl \
        pdo_pgsql \
        zip \
        bcmath \
    && rm -rf /var/lib/apt/lists/*

# Install Xdebug for local debugging
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copy Composer from its official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app