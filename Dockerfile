# Etapa 1: Construir assets de Vue con Node
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci --legacy-peer-deps
COPY resources/ resources/
COPY public/ public/
COPY vite.config.js ./
RUN npm run build

# Etapa 2: Imagen final con PHP y Apache
FROM php:8.2-apache

# Instalar extensiones necesarias para Laravel y MySQL
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

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache para servir desde /var/www/html/public
COPY . /var/www/html
COPY .env.example /var/www/html/.env
RUN sed -i 's/DB_HOST=127.0.0.1/DB_HOST=${DB_HOST}/g' /var/www/html/.env \
    && sed -i 's/DB_DATABASE=laravel/DB_DATABASE=${DB_DATABASE}/g' /var/www/html/.env \
    && sed -i 's/DB_USERNAME=root/DB_USERNAME=${DB_USERNAME}/g' /var/www/html/.env \
    && sed -i 's/DB_PASSWORD=/DB_PASSWORD=${DB_PASSWORD}/g' /var/www/html/.env \
    && sed -i 's/APP_KEY=/APP_KEY=${APP_KEY}/g' /var/www/html/.env \
    && sed -i 's|APP_URL=http://localhost|APP_URL=${APP_URL}|g' /var/www/html/.env

# Copiar los assets compilados de Vue desde la etapa anterior
COPY --from=node-builder /app/public/build /var/www/html/public/build

# Configurar Apache: DocumentRoot a la carpeta public de Laravel
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && echo '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>' >> /etc/apache2/apache2.conf

# Dar permisos a las carpetas de Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Instalar dependencias de PHP (producción)
WORKDIR /var/www/html
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Generar la clave de aplicación y el enlace simbólico
RUN php artisan key:generate --force \
    && php artisan storage:link --force \
    && php artisan optimize

EXPOSE 80
CMD ["apache2-foreground"]
