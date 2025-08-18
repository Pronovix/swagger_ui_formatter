<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter\SwaggerUiLibraryDiscovery;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Swagger UI library discoveries.
 *
 * @internal This class is not part of the module's public programming API.
 *
 * @phpstan-consistent-constructor
 */
abstract class SwaggerUiLibraryDiscoveryBase implements SwaggerUiLibraryDiscoveryInterface, ContainerInjectionInterface, CacheableDependencyInterface {

  /**
   * The minimum supported Swagger UI library version.
   */
  public const MIN_SUPPORTED_LIBRARY_VERSION = '4.15.0';

  /**
   * Swagger UI library path related cache ID.
   */
  private const LIBRARY_PATH_CID = 'swagger_ui_formatter:library_path';

  /**
   * The default cache bin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private CacheBackendInterface $cache;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The default cache bin.
   */
  public function __construct(CacheBackendInterface $cache) {
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    // Make it work in hook_requirements() from the "install" phase.
    return new static(
      $container->get('cache.default'),
    );
  }

  /**
   * {@inheritdoc}
   */
  final public function libraryDirectory(): string {
    $cache = $this->cache->get(self::LIBRARY_PATH_CID);
    if ($cache) {
      assert(property_exists($cache, 'data'));
      return $cache->data;
    }

    $library_dir = $this->identifyLibraryLocation();
    $this->validateLibraryDirectory(DRUPAL_ROOT . '/' . $library_dir);
    // Save the library directory to cache so we can save some computation time.
    $this->cache->set(self::LIBRARY_PATH_CID, $library_dir, $this->getCacheMaxAge(), $this->getCacheTags());
    return $library_dir;
  }

  /**
   * {@inheritdoc}
   */
  final public function libraryVersion(): string {
    $version = $this->identifyLibraryVersion();
    if (version_compare($version, self::MIN_SUPPORTED_LIBRARY_VERSION, '<')) {
      throw SwaggerUiLibraryDiscoveryException::becauseLibraryVersionIsNotSupported($version, self::MIN_SUPPORTED_LIBRARY_VERSION);
    }
    return $version;
  }

  /**
   * Validates a given Swagger UI library directory.
   *
   * @param string $library_dir
   *   The directory path, which contains the Swagger UI library.
   *
   * @throws \Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryExceptionInterface
   */
  final protected function validateLibraryDirectory(string $library_dir): void {
    if (!file_exists($library_dir) || !is_dir($library_dir)) {
      throw SwaggerUiLibraryDiscoveryException::becauseLibraryDirectoryIsInvalid($library_dir);
    }
    $files_to_check = [
      'package.json',
      'dist/swagger-ui.css',
      'dist/swagger-ui-bundle.js',
      'dist/swagger-ui-standalone-preset.js',
      'dist/oauth2-redirect.html',
    ];
    foreach ($files_to_check as $file) {
      $file_path = $library_dir . '/' . $file;
      if (!file_exists($file_path)) {
        throw SwaggerUiLibraryDiscoveryException::becauseRequiredLibraryFileIsNotFound($file_path);
      }
    }
  }

  /**
   * Defers library version identification to child classes.
   *
   * @return string
   *   The Swagger UI library version.
   *
   * @throws \Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryExceptionInterface
   */
  abstract protected function identifyLibraryVersion(): string;

  /**
   * Defers library install location identification to child classes.
   *
   * This is a relative path from the DRUPAL_ROOT. No leading slash should be
   * included in the returned path.
   *
   * @return string
   *   The path of the Swagger UI library directory relative to DRUPAL_ROOT.
   *
   * @throws \Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryExceptionInterface
   */
  abstract protected function identifyLibraryLocation(): string;

  /**
   * Clears internal cache.
   */
  public function reset(): void {
    $this->cache->delete(self::LIBRARY_PATH_CID);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    return [
      self::LIBRARY_PATH_CID,
      'config:system.theme',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge(): int {
    return CacheBackendInterface::CACHE_PERMANENT;
  }

}
