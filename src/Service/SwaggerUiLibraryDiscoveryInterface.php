<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter\Service;

/**
 * Generic definition of a Swagger UI library discovery service.
 */
interface SwaggerUiLibraryDiscoveryInterface {

  /**
   * Gets the Swagger UI library directory.
   *
   * This is a relative path from the DRUPAL_ROOT. No leading slash should be
   * included in the returned path.
   *
   * @return string
   *   The path of the Swagger UI library directory relative to DRUPAL_ROOT.
   *
   * @throws \Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryExceptionInterface
   */
  public function libraryDirectory(): string;

  /**
   * Gets the Swagger UI library version.
   *
   * @return string
   *   The Swagger UI library version.
   *
   * @throws \Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryExceptionInterface
   */
  public function libraryVersion(): string;

}
