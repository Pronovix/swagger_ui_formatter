#!/usr/bin/env bash

set -e

if [[ ${CHECK_CS} == true ]]; then
    docker-compose exec php composer normalize -d .. --indent-size=4 --indent-style=space --no-update-lock --dry-run
    docker-compose exec php ./vendor/bin/phpcs -s ../phpcs.xml.dist web/modules
    docker-compose exec php ./vendor/bin/drupal-check --drupal-root=. -e "*/build/*" ..
fi
