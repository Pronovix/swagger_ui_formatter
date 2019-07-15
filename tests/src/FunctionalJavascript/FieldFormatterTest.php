<?php

namespace Drupal\Tests\swagger_ui_formatter\src\FunctionalJavascript;

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
    parent::createScreenshot((getenv('BROWSERTEST_OUTPUT_DIRECTORY') ?? 'public://') . '/' . $filename . '.png', $set_background_color);
  }

  /**
   * Tests field error message when Swagger UI library is not installed.
   *
   * @param string $filename_prefix
   *   Prefix for created screenshot file, ex.: name of the caller method.
   */
  private function validateSwaggerUiIsMissingMessage(string $filename_prefix) : void {
    $assert = $this->assertSession();
    // Clear caches of Drupal that runs in the test browser.
    $cache = \Drupal::cache();
    $cache_tag_invalidator = \Drupal::service('cache_tags.invalidator');
    // Because cached render arrays that contains a field rendered by
    // a formatter provided by this module are also tagged with this CID,
    // this call should invalidate _only those render arrays_ that contains a
    // field rendered by this module. (This is a much lightweight call then
    // drupal_flush_all_caches()).
    $cache_tag_invalidator->invalidateTags([SWAGGER_UI_FORMATTER_LIBRARY_PATH_CID]);
    // Simple way of faking that Swagger UI library is missing.
    // (The implementation in _swagger_ui_formatter_get_library_path()
    // makes this possible.)
    $cache->set(SWAGGER_UI_FORMATTER_LIBRARY_PATH_CID, FALSE);
    $this->getSession()->reload();
    $this->createScreenshot($filename_prefix . '-' . __FUNCTION__ . '-after-faking-enabled');
    $assert->pageTextContainsOnce('Swagger UI library is missing.');

    $cache_tag_invalidator->invalidateTags([SWAGGER_UI_FORMATTER_LIBRARY_PATH_CID]);
    $cache->delete(SWAGGER_UI_FORMATTER_LIBRARY_PATH_CID);
    $this->getSession()->reload();
    $this->createScreenshot($filename_prefix . '-' . __FUNCTION__ . '-after-faking-disabled');
  }

}
