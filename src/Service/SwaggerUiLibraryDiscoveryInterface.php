<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter\Service;

// @phpcs:disable Drupal.Semantics.FunctionTriggerError.TriggerErrorTextLayoutStrict
@trigger_error('The ' . __NAMESPACE__ . '\SwaggerUiLibraryDiscoveryInterface is deprecated in swagger_ui_formatter:4.4.0 and is removed from swagger_ui_formatter:5.0.0. \Drupal\swagger_ui_formatter\SwaggerUiLibraryDiscovery\SwaggerUiLibraryDiscoveryInterface is the replacement', E_USER_DEPRECATED);
// @phpcs:enable

use Drupal\swagger_ui_formatter\SwaggerUiLibraryDiscovery\SwaggerUiLibraryDiscoveryInterface as OriginalSwaggerUiLibraryDiscoveryInterface;

/**
 * Generic definition of a Swagger UI library discovery service.
 *
 * @phpcs:disable Drupal.Commenting.Deprecated.DeprecatedMissingSeeTag
 * @deprecated in swagger_ui_formatter:4.4.0 and is removed from swagger_ui_formatter:5.0.0.
 * \Drupal\swagger_ui_formatter\SwaggerUiLibraryDiscovery\SwaggerUiLibraryDiscoveryInterface
 * is the replacement.
 * @phpcs:enable
 */
interface SwaggerUiLibraryDiscoveryInterface extends OriginalSwaggerUiLibraryDiscoveryInterface {

}
