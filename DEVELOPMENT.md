# Development

Install DDEV on your machine, then start the development environment:

```shell
$ ddev start
```

## Linting, static code analyses and testing

The following commands are available for code quality and testing:

**Code Quality**
- `ddev phpcs` - Run PHP_CodeSniffer to check coding standards
- `ddev phpcbf` - Automatically fix coding standard violations
- `ddev phpstan` - Run static analysis on the web/modules/custom directory
  - To maintain and update the PHPStan v1 baseline for Drupal 10, run the following command: `ddev core-version "^10" && ddev phpstan -b phpstan-baseline-v1.neon`

**Testing**
- `ddev phpunit` - Run PHPUnit tests

For detailed configuration options and advanced usage, see the [ddev-drupal-contrib documentation](https://github.com/ddev/ddev-drupal-contrib).
