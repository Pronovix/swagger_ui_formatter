Swagger UI Field Formatter
==========================

Introduction
============

This module provides a field formatter for file fields with allowed file types
of JSON (.json) and/or YAML (.yml or .yaml), which renders the uploaded file
using Swagger UI if the file is a valid Swagger file. This module uses the
Swagger UI library available at https://github.com/swagger-api/swagger-ui

Requirements
============
This module does not have any special requirements.

Installation
============
Download the module from https://drupal.org and extract it into your
sites/all/modules/contrib directory. Login as administrator, go the the
admin/modules page and install the Swagger UI Field Formatter module.

Configuration
=============

Go to the admin/structure/types/manage/<type>/fields page, add a new field
of type File and add "json", "yml" or "yaml" to allowed file extensions.
Then go the admin/structure/types/manage/<type>/display page and select
Swagger UI from the Format drop down.

Create a new content of the type <type> to which you added the new file field
and upload a valid Swagger file.