#!/bin/bash

git reset --hard master && git pull
composer install --no-dev --no-progress --no-suggest --ignore-platform-reqs
php bin/console cache:cl
php bin/magento app:poison:renew