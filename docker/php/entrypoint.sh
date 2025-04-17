#!/bin/bash

# Create storage link if it doesn't exist
if [ ! -d /var/www/public/storage ]; then
  cd /var/www && php artisan storage:link
fi

# Ensure log directory exists
mkdir -p /var/www/storage/logs

# Ensure proper permissions
chown -R www-data:www-data /var/www/storage

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf

# Start PHP-FPM
php-fpm 