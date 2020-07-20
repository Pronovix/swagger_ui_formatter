<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter_test\Service;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\State\StateInterface;
use Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryException;
use Drupal\swagger_ui_formatter\Service\SwaggerUiLibraryDiscoveryInterface;

/**
 * Decorator service for testing.
 */
final class SwaggerUiLibraryDiscovery implements SwaggerUiLibraryDiscoveryInterface, CacheableDependencyInterface {

  /**
   * A state key for a boolean value to indicate a missing library path.
   */
  private const STATE_FAKE_MISSING_LIBRARY = 'swagger_ui_formatter_test_fake_missing_library';

  /**
   * The decorated service.
   *
   * @var \Drupal\swagger_ui_formatter\Service\SwaggerUiLibraryDiscovery
   */
  private $decorated;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private $state;

  /**
   * The cache tags invalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  private $cacheTagsInvalidator;

  /**
   * Constructs a new object.
   *
   * @param \Drupal\swagger_ui_formatter\Service\SwaggerUiLibraryDiscoveryInterface $decorated
   *   The decorated service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator service.
   */
  public function __construct(SwaggerUiLibraryDiscoveryInterface $decorated, StateInterface $state, CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->decorated = $decorated;
    $this->state = $state;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * {@inheritdoc}
   */
  public function libraryDirectory(): string {
    if ($this->state->get(self::STATE_FAKE_MISSING_LIBRARY)) {
      throw SwaggerUiLibraryDiscoveryException::becauseLibraryDirectoryIsInvalid('fake-missing/swagger-ui');
    }
    return $this->decorated->libraryDirectory();
  }

  /**
   * {@inheritdoc}
   */
  public function libraryVersion(): string {
    return $this->decorated->libraryVersion();
  }

  /**
   * Helper function to fake a missing library path.
   *
   * @param bool $state
   *   Indicates whether the missing library path is faked or not.
   *
   * @see libraryDirectory()
   */
  public function fakeMissingLibrary(bool $state): void {
    $this->state->set(self::STATE_FAKE_MISSING_LIBRARY, $state);
    // Invalidate the cache tags so the library path needs to be re-calculated.
    $this->cacheTagsInvalidator->invalidateTags($this->decorated->getCacheTags());
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
