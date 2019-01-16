<?php

namespace Drupal\swagger_ui_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Plugin implementation of Swagger UI file field formatter.
 *
 * @FieldFormatter(
 *   id = "swagger_ui_file",
 *   label = @Translation("Swagger UI"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class SwaggerUIFileFormatter extends FileFormatterBase {

  use SwaggerUIFormatterTrait;

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $element = parent::view($items, $langcode);

    $swagger_files = [];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      /** @var \Drupal\file\Entity\File $file */
      // We don't validate file types, syntax and semantics. It's the user's
      // responsibility to set up field settings correctly and provide valid
      // files with valid file extensions.
      $swagger_files[] = file_create_url($file->getFileUri());
    }

    return $this->attachLibraries($element, $swagger_files);
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $element[$delta] = [
        '#theme' => 'swagger_ui_field_item',
        '#field_name' => $this->fieldDefinition->getName(),
        '#delta' => $delta,
      ];
    }
    return $element;
  }

}
