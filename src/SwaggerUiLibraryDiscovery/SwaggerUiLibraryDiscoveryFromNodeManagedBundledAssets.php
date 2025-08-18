<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter\SwaggerUiLibraryDiscovery;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Swagger UI library discovery for Node.js package manager installations.
 *
 * Discovers Swagger UI library from Node.js package manager installations
 * (Yarn, Yarn Workspaces, or npm) located in the project's frontend/dist
 * directory.
 *
 * @internal This class is not part of the module's public programming API.
 */
final class SwaggerUiLibraryDiscoveryFromNodeManagedBundledAssets extends SwaggerUiLibraryDiscoveryBase {

  /**
   * The extension path resolver.
   */
  protected ExtensionPathResolver $extensionPathResolver;

  /**
   * Constructs a new object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The default cache bin.
   * @param \Drupal\Core\Extension\ExtensionPathResolver $extension_path_resolver
   *   The extension path resolver service.
   */
  public function __construct(CacheBackendInterface $cache, ExtensionPathResolver $extension_path_resolver) {
    parent::__construct($cache);
    $this->extensionPathResolver = $extension_path_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('cache.default'),
      $container->get(ExtensionPathResolver::class),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function identifyLibraryLocation(): string {
    return $this->extensionPathResolver->getPath('module', 'swagger_ui_formatter') . '/frontend';
  }

  /**
   * {@inheritdoc}
   */
  protected function identifyLibraryVersion(): string {
    $library_dir = $this->libraryDirectory();
    $version_info_path = DRUPAL_ROOT . '/' . $library_dir . '/swagger_ui_version.json';
    $version_info_content = file_get_contents($version_info_path);
    if (!$version_info_content) {
      throw SwaggerUiLibraryDiscoveryException::becauseCannotReadVersionInfoContent($version_info_path);
    }
    try {
      $data = json_decode($version_info_content, TRUE, flags: JSON_THROW_ON_ERROR);
    }
    catch (\JsonException $e) {
      throw SwaggerUiLibraryDiscoveryException::becauseVersionInfoCannotBeDecoded($version_info_path, $e->getMessage());
    }
    if (!array_key_exists('swagger-ui-version', $data)) {
      throw SwaggerUiLibraryDiscoveryException::becauseUnableToIdentifyLibraryVersion($version_info_path);
    }
    return (string) $data['swagger-ui-version'];
  }

}
