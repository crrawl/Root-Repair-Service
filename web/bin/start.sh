#!/bin/sh
set -eu
mkdir -p /var/log/rootrepair
touch /var/log/rootrepair/access.log
chown root:www-data /var/log/rootrepair/access.log
chmod 640 /var/log/rootrepair/access.log
mkdir -p /var/www/html/storage/uploads
chown www-data:www-data /var/www/html/storage/uploads
chmod 750 /var/www/html/storage/uploads
php /var/www/html/bin/setup.php
exec apache2-foreground

