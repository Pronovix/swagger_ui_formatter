# Swagger UI Field Formatter

This Drupal module provides a field formatter for File and Link type fields
which renders the referenced file using
[Swagger UI](https://swagger.io/tools/swagger-ui/) if the file is a valid
Swagger file. Supported file types are JSON (`.json`) and/or YAML (`.yml` or
`.yaml`).

This module uses the
[Swagger UI](https://github.com/swagger-api/swagger-ui) JavaScript library.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/swagger_ui_formatter) on
[drupal.org](https://www.drupal.org/).

To submit bug reports and feature suggestions, or to track changes visit the
module's [GitHub repository](https://github.com/Pronovix/swagger_ui_formatter).

## Requirements

Please note that the minimum supported Swagger UI library version is currently
`3.32.2` as at the time of this release that's the latest version which includes
security fixes.

## Module installation

Install the Swagger UI Field Formatter module as you would normally install
a contributed Drupal module. Visit the official
[Installing Modules](https://www.drupal.org/node/1897420) documentation for
further information.

## Swagger UI library installation

### Manual installation

Download the
[appropriate Swagger UI library version](https://github.com/swagger-api/swagger-ui/releases),
extract the archive and rename the folder to "swagger-ui". Place the renamed folder into
the `[DRUPAL ROOT]/libraries` directory so its path will be
`[DRUPAL ROOT]/libraries/swagger-ui`.

### Installation via Composer

If you would like to install the Swagger UI library with
[Composer](https://getcomposer.org/), you probably used the
[Project template for Drupal 8 projects](https://github.com/drupal/recommended-project)
to set up your project. To install JavaScript libraries, it's recommended to use
the [asset-packagist](https://asset-packagist.org/) repository. So you will need
to add the following to your `composer.json` file in the "repositories" section:

```json
{
    "type": "composer",
    "url": "https://asset-packagist.org"
}
```

It's also needed to extend the "extra/installer-paths" section with:

```json
"web/libraries/{$name}": [
    "type:drupal-library",
    "type:bower-asset",
    "type:npm-asset"
],
"web/libraries/swagger-ui": ["bower-asset/swagger-ui"],
```

And add a new "installer-types" section next to "extra/installer-paths":

```json
"installer-types": ["bower-asset", "npm-asset"],
```

After this you can install the library with the following command:

```shell
composer require oomphinc/composer-installers-extender bower-asset/swagger-ui
```

The library will be downloaded into the `[DRUPAL ROOT]/libraries` directory.

## Configuration

### File fields

1. In the Drupal administrative UI navigate to "Structure" > "Content types" >
**TYPE** > "Manage fields" where **TYPE** is the content type to which you want
to add the new field, such as a Basic page.
2. Click on the "Add field" button to add a new field.
3. Set the field type to "File" and enter a label name.
4. Click "Save and continue".
5. On the "Edit" tab, in the "Allowed file extensions" field enter the
   following: `yaml,yml,json`
6. Click "Save settings".
7. Click on the "Manage display" tab.
8. Select "Swagger UI" in the "Format" drop-down for the new field and
optionally configure the formatter settings.
9. Click "Save".
10. Add a new **TYPE** type content and upload a valid Swagger file.

### Link fields

1. In the Drupal administrative UI navigate to "Structure" > "Content types" >
**TYPE** > "Manage fields" where **TYPE** is the content type to which you want
to add the new field, such as a Basic page.
2. Click on the "Add field" button to add a new field.
3. Set the field type to "Link" and enter a label name.
4. Click "Save and continue".
5. On the "Edit" tab manage your field settings as you wish.
6. Click "Save settings".
7. Click on the "Manage display" tab.
8. Select "Swagger UI" in the "Format" drop-down for the new field and
optionally configure the formatter settings.
9. Click "Save".
10. Add a new **TYPE** type content and provide a valid Swagger file path.

When viewing the content page the uploaded or the referenced Swagger file will
be rendered by Swagger UI.

## Troubleshooting

If the content of the Swagger file does not render correctly try
clearing the cache by navigating to "Configuration" > "Development" >
"Performance" on the Drupal administrative UI and clicking on the "Clear all
caches" button.

If clearing the cache doesn't help, it's also worth to check the Swagger UI
library related entry on the "Reports" > "Status report" page to see whether
the library got recognised correctly or there is any problem with it.
