<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter\Service;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default Swagger UI library discovery service implementation.
 *
 * The main purpose of this service is to allow themes and modules to modify
 * the Swagger UI library directory path. The default theme can do this by
 * implementing the hook_swagger_ui_library_directory_alter() hook. Modules can
 * override the directory path by decorating the service.
 *
 * @see https://www.drupal.org/docs/drupal-apis/services-and-dependency-injection/altering-existing-services-providing-dynamic
 */
final class SwaggerUiLibraryDiscovery implements SwaggerUiLibraryDiscoveryInterface, ContainerInjectionInterface, CacheableDependencyInterface {

  /**
   * Swagger UI library path related cache ID.
   */
  private const LIBRARY_PATH_CID = 'swagger_ui_formatter:library_path';

  /**
   * The minimum supported Swagger UI library version.
   */
  public const MIN_SUPPORTED_LIBRARY_VERSION = '4.15.0';

  /**
   * The default cache bin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private CacheBackendInterface $cache;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  private ThemeHandlerInterface $themeHandler;

  /**
   * The theme manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  private ThemeManagerInterface $themeManager;

  /**
   * The theme initialization service.
   *
   * @var \Drupal\Core\Theme\ThemeInitializationInterface
   */
  private ThemeInitializationInterface $themeInitialization;

  /**
   * Constructs a SwaggerUiLibraryDiscovery instance.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The default cache bin.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler service.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager service.
   * @param \Drupal\Core\Theme\ThemeInitializationInterface $theme_initialization
   *   The theme initialization service.
   */
  public function __construct(CacheBackendInterface $cache, ThemeHandlerInterface $theme_handler, ThemeManagerInterface $theme_manager, ThemeInitializationInterface $theme_initialization) {
    $this->cache = $cache;
    $this->themeHandler = $theme_handler;
    $this->themeManager = $theme_manager;
    $this->themeInitialization = $theme_initialization;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    // Make it work in hook_requirements() from the "install" phase.
    return new self(
      $container->get('cache.default'),
      $container->get('theme_handler'),
      $container->get('theme.manager'),
      $container->get('theme.initialization')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function libraryDirectory(): string {
    $cache = $this->cache->get(self::LIBRARY_PATH_CID);
    if ($cache) {
      assert(property_exists($cache, 'data'));
      return $cache->data;
    }
    // The default library directory (relative to DRUPAL_ROOT).
    $library_dir = 'libraries/swagger-ui';
    // Allow the default theme to alter the default library directory.
    $default_theme = $this->themeInitialization->getActiveThemeByName($this->themeHandler->getDefault());
    $this->themeInitialization->loadActiveTheme($default_theme);
    // The hook is only invoked for the default theme (and its base themes).
    $this->themeManager->alterForTheme($default_theme, 'swagger_ui_library_directory', $library_dir);
    // Make sure that the directory path is relative (to DRUPAL ROOT).
    $library_dir = ltrim($library_dir, '/');
    $this->validateLibraryDirectory(DRUPAL_ROOT . '/' . $library_dir);
    // Save the library directory to cache so we can save some computation time.
    $this->cache->set(self::LIBRARY_PATH_CID, $library_dir, $this->getCacheMaxAge(), $this->getCacheTags());
    return $library_dir;
  }

  /**
   * {@inheritdoc}
   */
  public function libraryVersion(): string {
    $library_dir = $this->libraryDirectory();
    $package_json_path = DRUPAL_ROOT . '/' . $library_dir . '/package.json';
    $package_json_content = file_get_contents($package_json_path);
    if (!$package_json_content) {
      throw SwaggerUiLibraryDiscoveryException::becauseCannotReadPackageJsonContent($package_json_path);
    }
    $data = Json::decode($package_json_content);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw SwaggerUiLibraryDiscoveryException::becausePackageJsonCannotBeDecoded($package_json_path, json_last_error_msg());
    }
    if (!isset($data['version'])) {
      throw SwaggerUiLibraryDiscoveryException::becauseUnableToIdentifyLibraryVersion($package_json_path);
    }
    if (version_compare($data['version'], self::MIN_SUPPORTED_LIBRARY_VERSION, '<')) {
      throw SwaggerUiLibraryDiscoveryException::becauseLibraryVersionIsNotSupported($data['version'], self::MIN_SUPPORTED_LIBRARY_VERSION);
    }
    return $data['version'];
  }

  /**
   * Validates a given Swagger UI library directory.
   *
   * @param string $library_dir
   *   The directory path which contains the Swagger UI library.
   *
   * @throws \Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryException
   */
  private function validateLibraryDirectory(string $library_dir): void {
    if (!file_exists($library_dir)) {
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
