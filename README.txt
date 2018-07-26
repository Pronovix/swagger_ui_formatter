INTRODUCTION
------------

This module provides a field formatter for file fields with allowed file types
of JSON (.json) and/or YAML (.yml or .yaml), which renders the uploaded file
using Swagger UI if the file is a valid Swagger file. This module uses the
Swagger UI library available at https://github.com/swagger-api/swagger-ui

REQUIREMENTS
------------

The Swagger UI library available at
https://github.com/swagger-api/swagger-ui/releases

INSTALLATION
------------

1. Download the Swagger UI library, extract the file and rename the directory to
   "swagger-ui" or ""swagger_ui". Now, place the renamed directory into the
   /libraries directory so it's path will be /libraries/swagger_ui

2. Download the module from https://drupal.org and extract it into your
   /modules/contrib directory. Login as administrator, visit the
   admin/modules page with your web browser and install the
   Swagger UI Field Formatter module.

CONFIGURATION
-------------

Go to the admin/structure/types/manage/<type>/fields page, add a new field
of type File and add "json", "yml" or "yaml" to allowed file extensions.
Then go the admin/structure/types/manage/<type>/display page and select
Swagger UI from the Format drop down.

Create a new content of the type <type> to which you added the new file field
and upload a valid Swagger file.