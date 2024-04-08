#!/usr/bin/env bash

set -e

if [[ ${CHECK_CS} == true ]]; then
    docker compose exec -T php composer normalize -d .. --no-update-lock --dry-run
    # @TODO There is a weird file access issue on GHA.
    # Drupal QA: Unable to set 511 visibility for file at /mnt/files/local_mount.
    docker compose exec -T php ./vendor/bin/phpcs -s ./vendor/pronovix/drupal-qa/config/phpcs.xml.dist --ignore="contrib/*,*/build/*" web/modules
    docker compose exec -T php ./vendor/bin/phpstan --no-progress
fi
