# Development

## Initial setup

Suggested development environment is [Drupal Dev][https://github.com/Pronovix/docker-drupal-dev]

```sh
$ git clone https://github.com/Pronovix/docker-drupal-dev.git drupal-dev
$ mkdir build;
$ ln -s drupal-dev/docker-compose.yml
$ ln -s drupal-dev/Dockerfile
$ printf "COMPOSE_PROJECT_NAME=swagger_ui_formatter\nPHP_IMAGE=wodby/drupal-php:7.4-dev\n#You can find examples for available customization in the drupal-dev/examples/.env file.\n" > .env && source .env
$ docker-compose up -d --build
$ docker-compose exec php composer install
$ ln -s ../../../../drupal-dev/drupal/settings.php build/web/sites/default/settings.php
$ ln -s ../../../../drupal-dev/drupal/settings.shared.php build/web/sites/default/settings.shared.php
$ ln -s ../../../../drupal-dev/drupal/settings.testing.php build/web/sites/default/settings.testing.php
$ ln -s ../../../drupal-dev/drupal/development.services.yml.dist build/web/sites/development.services.yml.dist
$ docker-compose exec php drush si -y
$ docker-compose exec php drush en swagger_ui_formatter -y
```

## QA

### Code-style checks

```sh
$ docker-compose exec php composer normalize --indent-size=4 --indent-style=space --no-update-lock
$ docker-compose exec php ./vendor/bin/phpcbf -s ../phpcs.xml.dist --ignore="contrib/*" web/modules
$ docker-compose exec php ./vendor/bin/phpcs -s ../phpcs.xml.dist --ignore="contrib/*" web/modules
$ docker-compose exec php ./vendor/bin/drupal-check --drupal-root=. -e "*/build/*" ..
```

### Running tests

```sh
$ docker-compose run --rm php ./vendor/bin/phpunit -c web/core -v --debug --printer '\Drupal\Tests\Listeners\HtmlOutputPrinter' --bootstrap=vendor/pronovix/drupal-qa/src/Drupal/PhpUnit/bootstrap.php web/modules/drupal_module/tests/
```
