<?php

namespace Drupal\swagger_ui_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of Swagger UI link field formatter.
 *
 * @FieldFormatter(
 *   id = "swagger_ui_link",
 *   label = @Translation("Swagger UI"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class SwaggerUILinkFormatter extends FormatterBase {

  use SwaggerUIFormatterTrait;

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $element = parent::view($items, $langcode);

    $swagger_files = [];
    foreach ($items as $delta => $item) {
      /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $item */
      // We don't validate URLs or the referenced paths. It's the user's
      // responsibility to set up field settings correctly and provide valid
      // URLs with valid file extensions.
      $swagger_files[] = $item->getUrl()->toString();
    }

    return $this->attachLibraries($element, $swagger_files);
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'swagger_ui_field_item',
        '#field_name' => $this->fieldDefinition->getName(),
        '#delta' => $delta,
      ];
    }
    return $element;
  }

}
