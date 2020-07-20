<?php

declare(strict_types = 1);

namespace Drupal\Tests\swagger_ui_formatter\FunctionalJavascript;

use Drupal\Core\Cache\Cache;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\Url;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests file- and link formatters.
 *
 * Validates that multiple OpenaAPI/Swagger 2.0 and 3.0 files can be rendered
 * on the same (node) entity and these files could be rendered from the private
 * filesystem - which is the most problematic one based on our previous
 * experiences.
 */
final class FieldFormatterTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['swagger_ui_formatter_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * File system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->fileSystem = $this->container->get('file_system');
    $module_path = drupal_get_path('module', 'swagger_ui_formatter');
    // Copy fixtures to the public filesystem so they could be access from the
    // browser.
    $this->fileSystem->copy(DRUPAL_ROOT . '/' . $module_path . '/tests/fixtures/openapi20/petstore-expanded.yaml', PublicStream::basePath());
    $this->fileSystem->copy(DRUPAL_ROOT . '/' . $module_path . '/tests/fixtures/openapi30/uspto.yaml', PublicStream::basePath());
  }

  /**
   * Tests the file field formatter.
   */
  public function testFileFormatter(): void {
    $page = $this->getSession()->getPage();
    $assert = $this->assertSession();

    $this->drupalLogin($this->rootUser);
    $this->drupalGet(Url::fromRoute('node.add', ['node_type' => 'api_doc']));
    $petstore_path = $this->fileSystem->realpath('public://petstore-expanded.yaml');
    $uspto_path = $this->fileSystem->realpath('public://uspto.yaml');
    $page->attachFileToField('files[field_api_spec_0][]', $petstore_path);
    $assert->waitForField('field_api_spec_0_remove_button');
    $page->attachFileToField('files[field_api_spec_1][]', $uspto_path);
    $assert->waitForField('field_api_spec_1_remove_button');
    $this->createScreenshot(__FUNCTION__ . '-after-file-upload');
    $this->drupalPostForm(NULL, [
      'title[0][value]' => 'Testing the file field formatter',
    ], 'Save');
    $this->createScreenshot(__FUNCTION__ . '-after-save');
    $assert->waitForField('swagger-ui-field_api_spec-0');
    $assert->pageTextContains('Swagger Petstore');
    $assert->waitForField('swagger-ui-field_api_spec-1');
    $assert->pageTextContains('USPTO Data Set API');
    $this->validateSwaggerUiIsMissingMessage(__FUNCTION__);
  }

  /**
   * Tests the link field formatter.
   */
  public function testLinkFormatter(): void {
    $page = $this->getSession()->getPage();
    $assert = $this->assertSession();

    $this->drupalLogin($this->rootUser);
    $this->drupalGet(Url::fromRoute('node.add', ['node_type' => 'remote_api_doc']));
    $page->fillField('field_remote_api_spec[0][uri]', file_create_url('public://petstore-expanded.yaml'));
    $page->pressButton('field_remote_api_spec_add_more');
    $assert->waitForField('field_remote_api_spec[1][uri]');
    $page->fillField('field_remote_api_spec[1][uri]', file_create_url('public://uspto.yaml'));
    $this->drupalPostForm(NULL, [
      'title[0][value]' => 'Testing the link field formatter',
    ], 'Save');
    $this->createScreenshot(__FUNCTION__ . '-after-save');
    $assert->waitForField('swagger-ui-field_api_spec-0');
    $assert->pageTextContains('Swagger Petstore');
    $assert->waitForField('swagger-ui-field_api_spec-1');
    $assert->pageTextContains('USPTO Data Set API');
    $this->validateSwaggerUiIsMissingMessage(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  protected function createScreenshot($filename, $set_background_color = TRUE): void {
    parent::createScreenshot(DRUPAL_ROOT . '/sites/simpletest/' . $filename . '.png', $set_background_color);
  }

  /**
   * Tests field error message when Swagger UI library is not installed.
   *
   * @param string $filename_prefix
   *   Prefix for created screenshot file, ex.: name of the caller method.
   */
  private function validateSwaggerUiIsMissingMessage(string $filename_prefix): void {
    $assert = $this->assertSession();
    /** @var \Drupal\swagger_ui_formatter_test\Service\SwaggerUiLibraryDiscovery $swagger_ui_library_discovery */
    $swagger_ui_library_discovery = \Drupal::service('swagger_ui_formatter.swagger_ui_library_discovery');
    $swagger_ui_library_discovery->fakeMissingLibrary(TRUE);
    $this->getSession()->reload();
    $this->createScreenshot($filename_prefix . '-' . __FUNCTION__ . '-after-faking-enabled');
    $swagger_ui_library_is_missing = 'The Swagger UI library is missing or incorrectly defined.';
    $assert->pageTextContainsOnce($swagger_ui_library_is_missing);
    $swagger_ui_library_discovery->fakeMissingLibrary(FALSE);
    // DrupalWTF, render cache should be invalidated automatically - just like
    // it worked when the faking was enabled.
    Cache::invalidateTags(['rendered']);
    $this->getSession()->reload();
    $this->createScreenshot($filename_prefix . '-' . __FUNCTION__ . '-after-faking-disabled');
    $assert->pageTextNotContains($swagger_ui_library_is_missing);
  }

}
