#!/bin/bash sh

alias composer="php -n /usr/bin/composer"

mkdir -p /var/app/NotificationBundle/Tests/app/cache
mkdir -p /var/app/NotificationBundle/Tests/app/logs
chmod -R 0777 /var/app/NotificationBundle/Tests/app/cache
chmod -R 0777 /var/app/NotificationBundle/Tests/app/logs

exec apache2-foreground