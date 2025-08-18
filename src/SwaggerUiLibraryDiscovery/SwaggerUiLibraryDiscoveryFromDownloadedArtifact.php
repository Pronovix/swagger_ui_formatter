<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter\SwaggerUiLibraryDiscovery;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Swagger UI library discovery from downloaded artifacts.
 *
 * Discovers Swagger UI library from a manually or
 * Composer (via https://asset-packagist.org) downloaded and extracted
 * library folder. This discovery method is intended for sites that do not use
 * Node.js package managers (Yarn/npm) and instead manually download the
 * Swagger UI distribution files.
 *
 * The default discovery path is 'libraries/swagger-ui', but themes can
 * override this path by implementing hook_swagger_ui_library_directory_alter().
 *
 * @internal This class is not part of the module's public programming API.
 */
final class SwaggerUiLibraryDiscoveryFromDownloadedArtifact extends SwaggerUiLibraryDiscoveryBase {

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
   * Constructs a new object.
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
    parent::__construct($cache);
    $this->themeHandler = $theme_handler;
    $this->themeManager = $theme_manager;
    $this->themeInitialization = $theme_initialization;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
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
  protected function identifyLibraryLocation(): string {
    // The default library directory (relative to DRUPAL_ROOT).
    $library_dir = 'libraries/swagger-ui';
    // Allow the default theme to alter the default library directory.
    $default_theme = $this->themeInitialization->getActiveThemeByName($this->themeHandler->getDefault());
    $this->themeInitialization->loadActiveTheme($default_theme);
    // The hook is only invoked for the default theme (and its base themes).
    // @todo Deprecate hook and replace with one that has more specific name.
    //   (What is the alternative of https://www.drupal.org/node/2881531 for
    //   theme related alter hooks?)
    $this->themeManager->alterForTheme($default_theme, 'swagger_ui_library_directory', $library_dir);
    return ltrim($library_dir, '/');
  }

  /**
   * {@inheritdoc}
   */
  protected function identifyLibraryVersion(): string {
    $library_dir = $this->libraryDirectory();
    $package_json_path = DRUPAL_ROOT . '/' . $library_dir . '/package.json';
    $package_json_content = file_get_contents($package_json_path);
    if (!$package_json_content) {
      throw SwaggerUiLibraryDiscoveryException::becauseCannotReadPackageJsonContent($package_json_path);
    }
    try {
      $data = json_decode($package_json_content, TRUE, flags: JSON_THROW_ON_ERROR);
    }
    catch (\JsonException $e) {
      throw SwaggerUiLibraryDiscoveryException::becausePackageJsonCannotBeDecoded($package_json_path, $e->getMessage());
    }
    if (!array_key_exists('version', $data)) {
      throw SwaggerUiLibraryDiscoveryException::becauseUnableToIdentifyLibraryVersion($package_json_path);
    }
    return $data['version'];
  }

}
