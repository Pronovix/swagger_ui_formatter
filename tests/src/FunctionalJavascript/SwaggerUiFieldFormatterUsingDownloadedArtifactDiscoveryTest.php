<?php

declare(strict_types = 1);

namespace Drupal\Tests\swagger_ui_formatter\FunctionalJavascript;

use Drupal\swagger_ui_formatter\SwaggerUiLibraryDiscovery\SwaggerUiLibraryDiscoveryFromDownloadedArtifact;

/**
 * Tests Swagger UI FF by using the downloaded artifact discovery.
 *
 * @internal This class is not part of the module's public programming API.
 */
final class SwaggerUiFieldFormatterUsingDownloadedArtifactDiscoveryTest extends SwaggerUiFieldFormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    self::assertInstanceOf(SwaggerUiLibraryDiscoveryFromDownloadedArtifact::class, $this->container->get('swagger_ui_formatter.swagger_ui_library_discovery'));
  }

}
