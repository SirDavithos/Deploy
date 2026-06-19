# Stage 1: PHP with Composer to generate the vendor folder
FROM php:8.2-cli AS php-deps
RUN apt-get update && apt-get install -y zip unzip libzip-dev && docker-php-ext-install zip
WORKDIR /app
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist

# Stage 2: Node with vendor (from Stage 1) to build frontend
FROM node:20-alpine AS node-build
WORKDIR /app
COPY --from=php-deps /app/vendor /app/vendor
COPY package.json package-lock.json* ./
RUN npm ci --legacy-peer-deps
COPY resources/ resources/
COPY public/ public/
COPY vite.config.js ./
RUN npm run build

# Stage 3: Final production image with Apache
FROM php:8.2-apache AS production

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && a2enmod rewrite \
    && apt-get clean

# Copy Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Copy vendor from the php-deps stage
COPY --from=php-deps /app/vendor /var/www/html/vendor

# Copy built assets from the node-build stage
COPY --from=node-build /app/public/build /var/www/html/public/build

# Setup .env with placeholder replacement
COPY .env.example /var/www/html/.env
RUN sed -i 's/DB_HOST=127.0.0.1/DB_HOST=${DB_HOST}/g' /var/www/html/.env \
    && sed -i 's/DB_DATABASE=laravel/DB_DATABASE=${DB_DATABASE}/g' /var/www/html/.env \
    && sed -i 's/DB_USERNAME=root/DB_USERNAME=${DB_USERNAME}/g' /var/www/html/.env \
    && sed -i 's/DB_PASSWORD=/DB_PASSWORD=${DB_PASSWORD}/g' /var/www/html/.env \
    && sed -i 's/APP_KEY=/APP_KEY=${APP_KEY}/g' /var/www/html/.env \
    && sed -i 's|APP_URL=http://localhost|APP_URL=${APP_URL}|g' /var/www/html/.env

# Configure Apache to serve from /public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && echo '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>' >> /etc/apache2/apache2.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate app key, storage link, and optimize
RUN php artisan key:generate --force \
    && php artisan storage:link --force \
    && php artisan optimize

EXPOSE 80
CMD ["apache2-foreground"]
