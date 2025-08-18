<?php

declare(strict_types = 1);

namespace Drupal\Tests\swagger_ui_formatter\FunctionalJavascript;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\Url;
use Drupal\FunctionalJavascriptTests\JSWebAssert;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests file and link field formatters.
 *
 * Validates that multiple OpenaAPI/Swagger 2.0 and 3.0 files can be rendered
 * on the same (node) entity and these files could be rendered from the private
 * filesystem - which is the most problematic one based on our previous
 * experiences.
 *
 * @internal This class is not part of the module's public programming API.
 */
abstract class SwaggerUiFieldFormatterTestBase extends WebDriverTestBase {

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
  private FileSystemInterface $fileSystem;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->fileSystem = $this->container->get('file_system');
    $module_path = $this->container->get('extension.path.resolver')->getPath('module', 'swagger_ui_formatter');
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
    assert($assert instanceof JSWebAssert);

    $this->drupalLogin($this->rootUser);
    $this->drupalGet(Url::fromRoute('node.add', ['node_type' => 'api_doc']));
    $petstore_path = $this->fileSystem->realpath('public://petstore-expanded.yaml');
    assert(is_string($petstore_path));
    $uspto_path = $this->fileSystem->realpath('public://uspto.yaml');
    assert(is_string($uspto_path));
    $page->attachFileToField('files[field_api_spec_0][]', $petstore_path);
    $assert->waitForField('field_api_spec_0_remove_button');
    $page->attachFileToField('files[field_api_spec_1][]', $uspto_path);
    $assert->waitForField('field_api_spec_1_remove_button');
    $this->createScreenshot(__FUNCTION__ . '-after-file-upload');
    $this->submitForm([
      'title[0][value]' => 'Testing the file field formatter',
    ], 'Save');
    $this->createScreenshot(__FUNCTION__ . '-after-save');
    $assert->waitForField('swagger-ui-field_api_spec-0');
    $assert->pageTextContains('Swagger Petstore');
    $assert->waitForField('swagger-ui-field_api_spec-1');
    $assert->pageTextContains('USPTO Data Set API');
  }

  /**
   * Tests the link field formatter.
   */
  public function testLinkFormatter(): void {
    $page = $this->getSession()->getPage();
    $assert = $this->assertSession();
    assert($assert instanceof JSWebAssert);

    $this->drupalLogin($this->rootUser);
    $this->drupalGet(Url::fromRoute('node.add', ['node_type' => 'remote_api_doc']));

    $page->fillField('field_remote_api_spec[0][uri]', $this->container->get('file_url_generator')->generateAbsoluteString('public://petstore-expanded.yaml'));
    $page->pressButton('field_remote_api_spec_add_more');
    $assert->waitForField('field_remote_api_spec[1][uri]');
    $page->fillField('field_remote_api_spec[1][uri]', $this->container->get('file_url_generator')->generateAbsoluteString('public://uspto.yaml'));
    $this->submitForm([
      'title[0][value]' => 'Testing the link field formatter',
    ], 'Save');
    $this->createScreenshot(__FUNCTION__ . '-after-save');
    $assert->waitForField('swagger-ui-field_api_spec-0');
    $assert->pageTextContains('Swagger Petstore');
    $assert->waitForField('swagger-ui-field_api_spec-1');
    $assert->pageTextContains('USPTO Data Set API');
  }

  /**
   * {@inheritdoc}
   */
  protected function createScreenshot($filename, $set_background_color = TRUE): void {
    parent::createScreenshot(DRUPAL_ROOT . '/sites/simpletest/' . $filename . '.png', $set_background_color);
  }

}
