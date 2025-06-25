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

**Testing**
- `ddev phpunit` - Run PHPUnit tests

For detailed configuration options and advanced usage, see the [ddev-drupal-contrib documentation](https://github.com/ddev/ddev-drupal-contrib).
