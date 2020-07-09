#!/usr/bin/env bash

set -e

if [[ ${RUN_PHPUNIT_TESTS} == true ]]; then
    docker-compose run --rm php ./vendor/bin/phpunit -c web/core -v --debug --printer '\Drupal\Tests\Listeners\HtmlOutputPrinter' web/modules/drupal_module/tests/
fi
