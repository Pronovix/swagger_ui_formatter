#!/usr/bin/env bash

set -e

# Lock Drupal core to the expected major version.
if [[ -n "${DRUPAL_CORE}" ]]; then
  docker compose exec php composer require -d .. --no-update drupal/core:${DRUPAL_CORE}
fi
# We need to run both "install" and "update" commands because:
# * `--prefer-lowest` is not supported by "install".
# * it seems there is an issue with the merge plugin and because of that if we would only run
# `composer update --prefer-lowest` then incorrect lower versions could be installed, ex.: drupal/core:8.5.0 where
# there is a drupal/core: ^8.7 constraint.
docker compose exec php composer install -d .. ${COMPOSER_GLOBAL_OPTIONS}
if [[ -n "${DEPENDENCIES}" ]]; then
    # Avoid failing builds caused by "Source directory /mnt/files/local_mount/build/vendor/drupal/coder has uncommitted changes.".
    docker compose exec php composer config -d .. --global discard-changes true
    docker compose exec php composer update -d .. ${COMPOSER_GLOBAL_OPTIONS} ${DEPENDENCIES} -n --with-dependencies
else
    # Ensure Drupal coding standard is registered.
    # TODO Check why it gets immediately unregistered after it has been registered
    # Error:
    # PHP CodeSniffer Config installed_paths set to ../../drupal/coder/coder_sniffer
    # PHP CodeSniffer Config installed_paths delete
    docker compose exec php composer update -d .. none
fi
# Log the installed versions.
docker compose exec php composer --version
docker compose exec php composer show -d .. -f json

sudo chown -R travis:travis .
ln -s ../../../../drupal-dev/drupal/settings.php build/web/sites/default/settings.php
ln -s ../../../../drupal-dev/drupal/settings.shared.php build/web/sites/default/settings.shared.php
ln -s ../../../../drupal-dev/drupal/settings.testing.php build/web/sites/default/settings.testing.php
ln -s ../../../drupal-dev/drupal/development.services.yml.dist build/web/sites/development.services.yml.dist
sudo chown -R 1000:travis .
