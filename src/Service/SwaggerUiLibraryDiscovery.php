<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\swagger_ui_formatter\SwaggerUiLibraryDiscovery\SwaggerUiLibraryDiscoveryFromDownloadedArtifact;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * BC-bridge for Swagger UI discovery from downloaded artifacts.
 *
 * @internal This class is not part of the module's public programming API.
 *
 * @phpcs:disable Drupal.Commenting.Deprecated.DeprecatedMissingSeeTag
 * @deprecated in swagger_ui_formatter:4.4.0 and is removed from swagger_ui_formatter:5.0.0.
 * \Drupal\swagger_ui_formatter\SwaggerUiLibraryDiscovery\SwaggerUiLibraryDiscoveryFromDownloadedArtifact
 * is the replacement.
 * @phpcs:enable
 */
final class SwaggerUiLibraryDiscovery implements SwaggerUiLibraryDiscoveryInterface, ContainerInjectionInterface, CacheableDependencyInterface {

  /**
   * The decorated service discovery.
   */
  private SwaggerUiLibraryDiscoveryFromDownloadedArtifact $decorated;

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
    // @phpcs:disable Drupal.Semantics.FunctionTriggerError.TriggerErrorTextLayoutRelaxed
    @trigger_error('The ' . self::class . ' is deprecated in swagger_ui_formatter:4.4.0 and is removed from swagger_ui_formatter:5.0.0. \Drupal\swagger_ui_formatter\SwaggerUiLibraryDiscovery\SwaggerUiLibraryDiscoveryFromDownloadedArtifact is the replacement', E_USER_DEPRECATED);
    // @phpcs:enable
    $this->decorated = new SwaggerUiLibraryDiscoveryFromDownloadedArtifact($cache, $theme_handler, $theme_manager, $theme_initialization);
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
  public function libraryDirectory(): string {
    return $this->decorated->libraryDirectory();
  }

  /**
   * {@inheritdoc}
   */
  public function libraryVersion(): string {
    return $this->decorated->libraryVersion();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    return $this->decorated->getCacheContexts();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    return $this->decorated->getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge(): int {
    return $this->decorated->getCacheMaxAge();
  }

}
