#!/usr/bin/env bash

set -e

if [[ ${CHECK_CS} == true ]]; then
    docker-compose exec -T php composer normalize -d .. --indent-size=4 --indent-style=space --no-update-lock --dry-run
    docker-compose exec -T php ./vendor/bin/phpcs -s ../phpcs.xml.dist --ignore="contrib/*" web/modules
    docker-compose exec -T php ./vendor/bin/drupal-check --drupal-root=. -e "*/build/*" ..
fi
