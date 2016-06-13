#!/bin/bash sh


composer update

vendor/codeception/codeception/codecept run
phpunit

#For run test uncomment
while true; do sleep 1000; done

#alias composer="php -n /usr/bin/composer"
#
#mkdir -p /var/app/NotificationBundle/Tests/app/cache
#mkdir -p /var/app/NotificationBundle/Tests/app/logs
#chmod -R 0777 /var/app/NotificationBundle/Tests/app/cache
#chmod -R 0777 /var/app/NotificationBundle/Tests/app/logs
#
#exec apache2-foreground

#chmod -R 777 Tests/Functional/var/*
#
#php Tests/Functional/bin/console.php doctrine:database:create
#php Tests/Functional/bin/console.php doctrine:schema:update --force

#phpunit