<?php

/**
 * @file
 * Hooks of Swagger UI Field Formatter module.
 */

declare(strict_types = 1);

/**
 * Alters the Swagger UI library directory path.
 *
 * This hook is only invoked for the default theme (and its base themes).
 * Modules can override the directory path by decorating the
 * SwaggerUiLibraryDiscovery service.
 *
 * @param string $library_dir
 *   The Swagger UI library directory path.
 *
 * @see Drupal\swagger_ui_formatter\Service\SwaggerUiLibraryDiscovery
 */
function hook_swagger_ui_library_directory_alter(string &$library_dir): void {
  $library_dir = '/my/custom/path/to/swagger-ui/';
}
