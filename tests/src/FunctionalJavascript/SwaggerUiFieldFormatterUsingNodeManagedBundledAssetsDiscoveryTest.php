<?php

declare(strict_types = 1);

namespace Drupal\Tests\swagger_ui_formatter\FunctionalJavascript;

use Drupal\swagger_ui_formatter\SwaggerUiLibraryDiscovery\SwaggerUiLibraryDiscoveryFromNodeManagedBundledAssets;

/**
 * Tests Swagger UI FF by using node managed bundled assets discovery.
 *
 * @internal This class is not part of the module's public programming API.
 */
final class SwaggerUiFieldFormatterUsingNodeManagedBundledAssetsDiscoveryTest extends SwaggerUiFieldFormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['swagger_ui_formatter_test_bundled_assets'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    self::assertInstanceOf(SwaggerUiLibraryDiscoveryFromNodeManagedBundledAssets::class, $this->container->get('swagger_ui_formatter.swagger_ui_library_discovery'));
  }

}
