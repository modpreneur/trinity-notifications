#!/bin/bash sh

chmod -R 777 Tests/Functional/var/*

php Tests/Functional/bin/console.php doctrine:database:create
php Tests/Functional/bin/console.php doctrine:schema:update --force

phpunit