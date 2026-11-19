#!/bin/sh
set -e
cd /var/www/html
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    # En local, les sources sont montées : pas de cache, et l'opcache relit les fichiers.
    php artisan optimize:clear >/dev/null
    echo "opcache.validate_timestamps=1" > "$PHP_INI_DIR/conf.d/zzz-dev.ini"
fi
chown -R www-data:www-data storage bootstrap/cache
exec "$@"
