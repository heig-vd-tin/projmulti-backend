# syntax=docker/dockerfile:1
# Image unique : backend Laravel + front Vue compilé, servis par Apache/PHP.
# Le front est fourni comme contexte nommé « frontend » (voir compose.yaml).

FROM node:22-alpine AS front-build
WORKDIR /src
COPY --from=frontend package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY --from=frontend . .
# Le front définit « npm run build » qui produit dist/ (Vue CLI ou Vite).
RUN npm run build && mv dist /dist

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

FROM php:8.5-apache
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql ldap gd opcache \
 && apt-get update && apt-get install -y --no-install-recommends coinor-cbc && rm -rf /var/lib/apt/lists/* \
 && a2enmod rewrite headers \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php.ini $PHP_INI_DIR/conf.d/zz-projmulti.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/projmulti-entrypoint

WORKDIR /var/www/html
COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=front-build /dist/ ./public/
RUN php artisan package:discover --ansi \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod +x /usr/local/bin/projmulti-entrypoint

ENTRYPOINT ["projmulti-entrypoint"]
CMD ["apache2-foreground"]
