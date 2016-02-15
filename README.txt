Swagger UI Field Formatter
==========================

Introduction
============

This module provides a field formatter for file fields with allowed file types
as json or yml, which renders the uploaded json or yml file using the Swagger UI
if the uploaded json or yml file is a valid swagger file. This module uses the
Swagger UI Library available at https://github.com/swagger-api/swagger-ui

Requirements
============
This module does not have any special requirements. However in order to be able
to allow json or yml file extensions in Drupal, you might need to install the
file entity module.

Installation
============
Download the module from https://drupal.org and extract it into your
sites/all/modules/contrib. Login as adminstrator and got admin/modules and select
Swagger UI Field Formatter and Save the configuration.

Configuration
=============

Goto admin/structure/types/manage/<type>/fields and add a new field of type file
and select the widget as file.
In the allowed extensions field in the field settings add yml,json.
After saving the new field settings Goto admin/structure/types/manage/<type>/display
select Swagger UI Formatter from the Format drop down for the newly created file
field.

Create a new content of the type <type> to which we added the new file field above
and upload a valid swagger .yml or .json file.
You can see that the uploaded swagger file is rendered using the Swagger UI.
