<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter_test\Service;

use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\State\StateInterface;
use Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryException;
use Drupal\swagger_ui_formatter\Service\SwaggerUiLibraryDiscovery as OriginalSwaggerUiLibraryDiscovery;
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
   * A state key for a boolean value to indicate an unsupported library version.
   */
  private const STATE_FAKE_UNSUPPORTED_LIBRARY = 'swagger_ui_formatter_test_fake_unsupported_library';

  /**
   * The decorated service.
   *
   * @var \Drupal\swagger_ui_formatter\Service\SwaggerUiLibraryDiscovery
   */
  private OriginalSwaggerUiLibraryDiscovery $decorated;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private StateInterface $state;

  /**
   * The cache tags invalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  private CacheTagsInvalidatorInterface $cacheTagsInvalidator;

  /**
   * The library discovery.
   *
   * @var \Drupal\Core\Asset\LibraryDiscoveryInterface
   */
  private LibraryDiscoveryInterface $libraryDiscovery;

  /**
   * Constructs a new object.
   *
   * @param \Drupal\swagger_ui_formatter\Service\SwaggerUiLibraryDiscovery $decorated
   *   The decorated service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator service.
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery
   *   The library discovery.
   */
  public function __construct(OriginalSwaggerUiLibraryDiscovery $decorated, StateInterface $state, CacheTagsInvalidatorInterface $cache_tags_invalidator, LibraryDiscoveryInterface $library_discovery) {
    $this->decorated = $decorated;
    $this->state = $state;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->libraryDiscovery = $library_discovery;
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
    if ($this->state->get(self::STATE_FAKE_UNSUPPORTED_LIBRARY)) {
      throw SwaggerUiLibraryDiscoveryException::becauseLibraryVersionIsNotSupported('3.32.1', $this->decorated::MIN_SUPPORTED_LIBRARY_VERSION);
    }
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
    $this->flushCaches();
  }

  /**
   * Helper function to fake an unsupported library version.
   *
   * @param bool $state
   *   Indicates whether the unsupported library version is faked or not.
   *
   * @see libraryVersion()
   */
  public function fakeUnsupportedLibrary(bool $state): void {
    $this->state->set(self::STATE_FAKE_UNSUPPORTED_LIBRARY, $state);
    $this->flushCaches();
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

  /**
   * Flushes caches for tests.
   */
  private function flushCaches(): void {
    // Invalidate the cache tags so the library path needs to be re-calculated.
    $this->cacheTagsInvalidator->invalidateTags($this->getCacheTags());
    // DrupalWTF, render cache should be invalidated automatically but it seems
    // there is a hidden static state somewhere...
    // @see \Drupal\Tests\swagger_ui_formatter\FunctionalJavascript\SwaggerUiFieldFormatterTest::validateSwaggerUiErrorMessage()
    // @see \Drupal\Core\Test\RefreshVariablesTrait::refreshVariables()
    if ($this->cacheTagsInvalidator instanceof CacheTagsInvalidator) {
      $this->cacheTagsInvalidator->resetChecksums();
    }
    // Flush the static cache of the used service by the
    // SwaggerUIFormatterTrait, this warrants that valid library
    // gets registered when it is needed.
    $this->libraryDiscovery->clearCachedDefinitions();
  }

}
