<?php

declare(strict_types = 1);

namespace {

  // Global variables to be able to use them in different (test) namespaces.
  const SWAGGER_UI_FORMATTER_TEST_INVALID_LIBRARY_DIR = 'libraries/invalid';
  const SWAGGER_UI_FORMATTER_TEST_VALID_LIBRARY_DIR_WITH_MISSING_FILES = 'libraries/valid-with-missing-files';
  const SWAGGER_UI_FORMATTER_TEST_VALID_LIBRARY_VERSION = '3.28.0';
  const SWAGGER_UI_FORMATTER_TEST_MISSING_PACKAGE_JSON_DIR = 'libraries/missing-package-json';
  const SWAGGER_UI_FORMATTER_TEST_INVALID_PACKAGE_JSON_DIR = 'libraries/invalid-package-json';
  const SWAGGER_UI_FORMATTER_TEST_VERSION_NOT_FOUND_IN_PACKAGE_JSON_DIR = 'libraries/version-not-found-in-package-json';
  const SWAGGER_UI_FORMATTER_TEST_VERSION_IS_NOT_SUPPORTED_IN_PACKAGE_JSON_DIR = 'libraries/version-is-not-supported-in-package-json';

}

/**
 * Swagger UI library discovery service related unit tests.
 *
 * phpcs:disable SlevomatCodingStandard.Namespaces.RequireOneNamespaceInFile.MoreNamespacesInFile
 */
namespace Drupal\Tests\swagger_ui_formatter\Unit {

  use Drupal\Core\Cache\CacheBackendInterface;
  use Drupal\Core\Extension\ThemeHandlerInterface;
  use Drupal\Core\Theme\ActiveTheme;
  use Drupal\Core\Theme\ThemeInitializationInterface;
  use Drupal\Core\Theme\ThemeManagerInterface;
  use Drupal\Tests\UnitTestCase;
  use Drupal\swagger_ui_formatter\Exception\SwaggerUiLibraryDiscoveryException;
  use Drupal\swagger_ui_formatter\Service\SwaggerUiLibraryDiscovery;

  /**
   * Tests the Swagger UI library discovery service.
   *
   * @covers \Drupal\swagger_ui_formatter\Service\SwaggerUiLibraryDiscovery
   */
  final class SwaggerUiLibraryDiscoveryTest extends UnitTestCase {

    /**
     * The default Swagger UI library directory path.
     */
    private const DEFAULT_LIBRARY_DIR = 'libraries/swagger-ui';

    /**
     * The mocked default cache bin.
     *
     * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cache;

    /**
     * The mocked theme handler service.
     *
     * @var \Drupal\Core\Extension\ThemeHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeHandler;

    /**
     * The mocked theme manager service.
     *
     * @var \Drupal\Core\Theme\ThemeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeManager;

    /**
     * The mocked theme initialization service.
     *
     * @var \Drupal\Core\Theme\ThemeInitializationInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeInitialization;

    /**
     * The Swagger UI library discovery service.
     *
     * @var \Drupal\swagger_ui_formatter\Service\SwaggerUiLibraryDiscovery
     */
    private $swaggerUiLibraryDiscovery;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
      parent::setUp();
      $this->cache = $this->createMock(CacheBackendInterface::class);
      $this->themeHandler = $this->createMock(ThemeHandlerInterface::class);
      $this->themeManager = $this->createMock(ThemeManagerInterface::class);
      $this->themeInitialization = $this->createMock(ThemeInitializationInterface::class);
      $this->swaggerUiLibraryDiscovery = new SwaggerUiLibraryDiscovery($this->cache, $this->themeHandler, $this->themeManager, $this->themeInitialization);
    }

    /**
     * Tests valid library directory with cold cache.
     */
    public function testWithValidLibraryDirectoryColdCache(): void {
      $this->setUpLibraryDirectoryTest();
      $default_theme = new ActiveTheme(['name' => 'bartik']);
      $this->themeManager
        ->expects($this->once())
        ->method('alterForTheme')
        ->with($default_theme, 'swagger_ui_library_directory', self::DEFAULT_LIBRARY_DIR)
        ->willReturn(NULL);
      // Here, we don't care about whether the data has been cached or not.
      $this->cache
        ->expects($this->once())
        ->method('set')
        ->willReturn(NULL);

      self::assertEquals(self::DEFAULT_LIBRARY_DIR, $this->swaggerUiLibraryDiscovery->libraryDirectory());
    }

    /**
     * Tests library directory with warm cache.
     */
    public function testLibraryDirectoryWithWarmCache(): void {
      $this->cache
        ->method('get')
        ->willReturn((object) ['data' => self::DEFAULT_LIBRARY_DIR]);
      $this->themeHandler
        ->expects($this->never())
        ->method('getDefault');

      self::assertEquals(self::DEFAULT_LIBRARY_DIR, $this->swaggerUiLibraryDiscovery->libraryDirectory());
    }

    /**
     * Tests with invalid library directory.
     */
    public function testWithInvalidLibraryDirectory(): void {
      $this->setUpLibraryDirectoryTest();
      $default_theme = new ActiveTheme(['name' => 'bartik']);
      $this->themeManager
        ->expects($this->once())
        ->method('alterForTheme')
        ->with($default_theme, 'swagger_ui_library_directory', self::DEFAULT_LIBRARY_DIR)
        ->willReturnCallback(static function ($default_theme, $hook, &$library_dir) {
          $library_dir = SWAGGER_UI_FORMATTER_TEST_INVALID_LIBRARY_DIR;
          return NULL;
        });

      $this->expectException(SwaggerUiLibraryDiscoveryException::class);
      $this->expectExceptionCode(SwaggerUiLibraryDiscoveryException::CODE_INVALID_DIR);

      $this->swaggerUiLibraryDiscovery->libraryDirectory();
    }

    /**
     * Tests with missing required library file.
     */
    public function testWithMissingRequiredLibraryFile(): void {
      $this->setUpLibraryDirectoryTest();
      $default_theme = new ActiveTheme(['name' => 'bartik']);
      $this->themeManager
        ->expects($this->once())
        ->method('alterForTheme')
        ->with($default_theme, 'swagger_ui_library_directory', self::DEFAULT_LIBRARY_DIR)
        ->willReturnCallback(static function ($default_theme, $hook, &$library_dir) {
          $library_dir = SWAGGER_UI_FORMATTER_TEST_VALID_LIBRARY_DIR_WITH_MISSING_FILES;
          return NULL;
        });

      $this->expectException(SwaggerUiLibraryDiscoveryException::class);
      $this->expectExceptionCode(SwaggerUiLibraryDiscoveryException::CODE_REQUIRED_FILE_IS_NOT_FOUND);

      $this->swaggerUiLibraryDiscovery->libraryDirectory();
    }

    /**
     * Tests with valid library version.
     */
    public function testWithValidLibraryVersion(): void {
      $this->setUpLibraryVersionTest();
      // Imitate that SwaggerUiLibraryDiscovery::libraryDirectory() responds
      // from cache to avoid mocking or different services.
      $this->cache
        ->method('get')
        ->willReturn((object) ['data' => self::DEFAULT_LIBRARY_DIR]);

      self::assertEquals(SWAGGER_UI_FORMATTER_TEST_VALID_LIBRARY_VERSION, $this->swaggerUiLibraryDiscovery->libraryVersion());
    }

    /**
     * Tests with missing package.json.
     */
    public function testWithMissingPackageJson(): void {
      $this->setUpLibraryVersionTest();
      // Imitate a wrong package.json path with a specific library directory.
      $this->cache
        ->method('get')
        ->willReturn((object) ['data' => SWAGGER_UI_FORMATTER_TEST_MISSING_PACKAGE_JSON_DIR]);

      $this->expectException(SwaggerUiLibraryDiscoveryException::class);
      $this->expectExceptionCode(SwaggerUiLibraryDiscoveryException::CODE_CANNOT_READ_PACKAGE_JSON_CONTENT);

      $this->swaggerUiLibraryDiscovery->libraryVersion();
    }

    /**
     * Tests with malformed package.json.
     */
    public function testWithMalformedPackageJson(): void {
      $this->setUpLibraryVersionTest();
      // Imitate an invalid package.json with a specific library directory.
      $this->cache
        ->method('get')
        ->willReturn((object) ['data' => SWAGGER_UI_FORMATTER_TEST_INVALID_PACKAGE_JSON_DIR]);

      $this->expectException(SwaggerUiLibraryDiscoveryException::class);
      $this->expectExceptionCode(SwaggerUiLibraryDiscoveryException::CODE_CANNOT_DECODE_PACKAGE_JSON);

      $this->swaggerUiLibraryDiscovery->libraryVersion();
    }

    /**
     * Tests with missing version in package.json.
     */
    public function testWithMissingVersionInPackageJson(): void {
      $this->setUpLibraryVersionTest();
      // Imitate that the "version" attribute is not found in package.json with
      // a specific library directory.
      $this->cache
        ->method('get')
        ->willReturn((object) ['data' => SWAGGER_UI_FORMATTER_TEST_VERSION_NOT_FOUND_IN_PACKAGE_JSON_DIR]);

      $this->expectException(SwaggerUiLibraryDiscoveryException::class);
      $this->expectExceptionCode(SwaggerUiLibraryDiscoveryException::CODE_UNABLE_TO_IDENTIFY_LIBRARY_VERSION);

      $this->swaggerUiLibraryDiscovery->libraryVersion();
    }

    /**
     * Tests with unsupported package.json.
     */
    public function testWithUnsupportedPackageJson(): void {
      $this->setUpLibraryVersionTest();
      // Imitate that the Swagger UI library "version" from package.json is not
      // supported.
      $this->cache
        ->method('get')
        ->willReturn((object) ['data' => SWAGGER_UI_FORMATTER_TEST_VERSION_IS_NOT_SUPPORTED_IN_PACKAGE_JSON_DIR]);

      $this->expectException(SwaggerUiLibraryDiscoveryException::class);
      $this->expectExceptionCode(SwaggerUiLibraryDiscoveryException::CODE_LIBRARY_VERSION_IS_NOT_SUPPORTED);

      $this->swaggerUiLibraryDiscovery->libraryVersion();
    }

    /**
     * Setup method for SwaggerUiLibraryDiscovery::libraryDirectory() tests.
     */
    private function setUpLibraryDirectoryTest(): void {
      $this->cache
        ->method('get')
        ->willReturn(NULL);
      $this->themeHandler
        ->method('getDefault')
        ->willReturn('bartik');
      $default_theme = new ActiveTheme(['name' => 'bartik']);
      $this->themeInitialization
        ->method('getActiveThemeByName')
        ->with($this->themeHandler->getDefault())
        ->willReturn($default_theme);
      $this->themeInitialization
        ->method('loadActiveTheme')
        ->with($default_theme)
        ->willReturn(NULL);
    }

    /**
     * Setup method for SwaggerUiLibraryDiscovery::libraryVersion() tests.
     */
    private function setUpLibraryVersionTest(): void {
      $this->themeHandler
        ->expects($this->never())
        ->method('getDefault');
    }

  }

}

/**
 * Function overrides for the Swagger UI library discovery service namespace.
 *
 * phpcs:disable SlevomatCodingStandard.Namespaces.RequireOneNamespaceInFile.MoreNamespacesInFile
 */
namespace Drupal\swagger_ui_formatter\Service {

  const DRUPAL_ROOT = '';

  /**
   * {@inheritdoc}
   */
  function file_exists(string $filename): bool {
    if ($filename === '/' . SWAGGER_UI_FORMATTER_TEST_INVALID_LIBRARY_DIR) {
      return FALSE;
    }
    if ($filename === '/' . SWAGGER_UI_FORMATTER_TEST_VALID_LIBRARY_DIR_WITH_MISSING_FILES) {
      return TRUE;
    }
    return !preg_match('#^/' . SWAGGER_UI_FORMATTER_TEST_VALID_LIBRARY_DIR_WITH_MISSING_FILES . '#', $filename);
  }

  /**
   * {@inheritdoc}
   */
  function file_get_contents($filename) {
    if ($filename === '/' . SWAGGER_UI_FORMATTER_TEST_MISSING_PACKAGE_JSON_DIR . '/package.json') {
      return FALSE;
    }
    if ($filename === '/' . SWAGGER_UI_FORMATTER_TEST_INVALID_PACKAGE_JSON_DIR . '/package.json') {
      return '{ invalid json }';
    }
    if ($filename === '/' . SWAGGER_UI_FORMATTER_TEST_VERSION_NOT_FOUND_IN_PACKAGE_JSON_DIR . '/package.json') {
      return '{ "foo": "bar" }';
    }
    if ($filename === '/' . SWAGGER_UI_FORMATTER_TEST_VERSION_IS_NOT_SUPPORTED_IN_PACKAGE_JSON_DIR . '/package.json') {
      return '{ "version": "3.13.3" }';
    }
    return '{ "version": "' . SWAGGER_UI_FORMATTER_TEST_VALID_LIBRARY_VERSION . '" }';
  }

}
