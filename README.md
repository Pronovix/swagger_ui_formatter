CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

This module provides a field formatter for File and Link fields which renders
the referenced file using Swagger UI if the file is a valid Swagger file.
Supported file types are JSON (.json) and/or YAML (.yml or .yaml). This module
uses the Swagger UI library available at
https://github.com/swagger-api/swagger-ui

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/swagger_ui_formatter

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/swagger_ui_formatter


REQUIREMENTS
------------

This module requires the following outside of Drupal core.

For version 8.x-2.x the Swagger UI library needs to be installed separately.
Download the appropriate Swagger UI version, extract the archive and rename the
folder to "swagger-ui" or to "swagger_ui". Place the renamed folder in the
[DRUPAL ROOT]/libraries directory so its path will be
[DRUPAL ROOT]/libraries/swagger-ui for example.

 * Swagger UI - https://swagger.io/tools/swagger-ui/
 * Swagger UI library - https://github.com/swagger-api/swagger-ui/releases


INSTALLATION
------------

Install the Swagger UI Field Formatter module as you would normally install
a contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
further information.


INSTALLATION VIA COMPOSER (RECOMMENDED)
---------------------------------------

If you would like to install the Swagger UI library with composer, you
probably used the [Drupal Composer template](https://github.com/drupal-composer/drupal-project)
to setup your project. It's recommended to use [asset-packagist](https://asset-packagist.org/)
to install JavaScript libraries. So you will need to add the following to your
composer.json file into the repositories section:

```json
    {
        "type": "composer",
        "url": "https://asset-packagist.org"
    }
```

It's also needed to extend the 'installer-path' section:

```json
    "web/libraries/{$name}": [
        "type:drupal-library",
        "type:bower-asset",
        "type:npm-asset"
    ],
    "web/libraries/swagger_ui": ["bower-asset/swagger-ui"],
```
And add a new 'installer-types' section next to the 'installer-path' in the 'extra' section:

```json
    "installer-types": ["bower-asset", "npm-asset"],
```

After this you can install the library with:

```shell
composer require oomphinc/composer-installers-extender bower-asset/swagger-ui
```
The library will be downloaded into the libraries folder.

MANUAL INSTALLATION
-------------------

    1. Download the Swagger UI library, extract the file and rename the folder
       to "swagger-ui" or "swagger_ui". Now, place the renamed folder in the
       [DRUPAL ROOT]/libraries directory, so its path will be
       [DRUPAL ROOT]/libraries/swagger-ui for example.
    2. Download the module from https://drupal.org and extract it into your
       [DRUPAL ROOT]/modules/contrib directory. Login as administrator, visit
       the admin/modules page with your web browser and install the
       Swagger UI Field Formatter module.


CONFIGURATION
-------------

File fields:

    1. Navigate to Structure > Content types > TYPE > Manage fields where
       TYPE is the content type to which you want to add the new field, such as
       a Basic page.
    2. Click on the "Add field" button to add a new field.
    3. Set the field type to "File" and enter a label name.
    4. Click "Save and continue".
    5. On the "Edit" tab, in the "Allowed file extensions" field enter the
       following: yaml,yml,json
    6. Click "Save settings".
    7. Click on the "Manage display" tab.
    8. Select "Swagger UI" in the Format drop-down for the new field and
       optionally configure formatter settings.
    9. Click "Save".
    10. Add a new TYPE type content and upload a valid Swagger file.

Link fields:

    1. Navigate to Structure > Content types > TYPE > Manage fields where
       TYPE is the content type to which you want to add the new field, such as
       a Basic page.
    2. Click on the "Add field" button to add a new field.
    3. Set the field type to "Link" and enter a label name.
    4. Click "Save and continue".
    5. On the "Edit" tab manage your field settings as you wish.
    6. Click "Save settings".
    7. Click on the "Manage display" tab.
    8. Select "Swagger UI" in the Format drop-down for the new field and
       optionally configure formatter settings.
    9. Click "Save".
    10. Add a new TYPE type content and provide a valid Swagger file path.

When viewing the content page the uploaded or the referenced Swagger file will
be rendered by Swagger UI.

Note: If the content of the Swagger file does not render correctly try clearing
the cache by navigating to Configuration > Development > Performance and
clicking on the "Clear all caches" button.


MAINTAINERS
-----------

The 8.x, 7.x-2.x branches:

 * Balazs Wittmann (balazswmann) - https://www.drupal.org/u/balazswmann
 * Dezső BICZÓ (mxr576) - https://www.drupal.org/u/mxr576
 
Supporting organizations:

 * Pronovix - https://www.drupal.org/pronovix

---

The 7.x-1.x branch:

 * Dezső BICZÓ (mxr576) - https://www.drupal.org/u/mxr576
 * dsudheesh - https://www.drupal.org/u/dsudheesh

Supporting organizations:

 * DigitalAPICraft - https://www.drupal.org/digitalapicraft
 * Pronovix - https://www.drupal.org/pronovix