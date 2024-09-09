#!/bin/bash

git reset --hard master && git pull pull master
composer install --no-dev --no-progress --no-suggest --ignore-platform-reqs
php bin/console cache:cl
php bin/console cache:opcache:clear
php bin/magento app:poison:renew