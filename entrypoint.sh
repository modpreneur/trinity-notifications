#!/bin/sh sh

composer update

phpunit

vendor/codeception/codeception/codecept run

phpstan analyse Annotations/ appTests/ Controller/ DataFixtures/ DataTransformer/ DependencyInjection/ Drivers/ Entity/ Event/ EventListener/ Exception/ Facades/ Interfaces/ Notification/ Services/ --level=4

tail -f /dev/null

