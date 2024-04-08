#!/usr/bin/env bash

set -e

if [[ ${RUN_PHPUNIT_TESTS} == true ]]; then
    # Custom bootstrap file is required to prevent infinite loop caused by symlinking that Drupal's original
    # bootstrap file cannot handle.
    docker-compose run --rm php ./vendor/bin/phpunit -c web/core -v --debug --printer '\Drupal\Tests\Listeners\HtmlOutputPrinter' --bootstrap=/mnt/files/local_mount/tests/src/bootstrap.php web/modules/drupal_module/tests/
fi
