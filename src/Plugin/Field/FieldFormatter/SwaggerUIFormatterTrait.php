<?php

namespace Drupal\swagger_ui_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * Provides common methods for Swagger UI field formatters.
 *
 * @internal
 */
trait SwaggerUIFormatterTrait {

  use MessengerTrait;

  /**
   * Adds Swagger UI field formatter settings to formatter settings.
   *
   * @param array $settings
   *   Settings inherited from the parent class.
   */
  protected static function addDefaultSettings(array &$settings) {
    $settings = [
      'validator' => 'default',
      'validator_url' => '',
      'doc_expansion' => 'list',
      'show_top_bar' => FALSE,
      'sort_tags_by_name' => FALSE,
      'supported_submit_methods' => [
        'get' => 'get',
        'put' => 'put',
        'post' => 'post',
        'delete' => 'delete',
        'options' => 'options',
        'head' => 'head',
        'patch' => 'patch',
      ],
    ] + $settings;
  }

  /**
   * Adds Swagger UI specific configurations to the settings form.
   *
   * @param array $form
   *   The form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Drupal\Core\Field\FormatterInterface $formatter
   *   The current field formatter.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition in the current field formatter.
   */
  protected function alterSettingsForm(array &$form, FormStateInterface $form_state, FormatterInterface $formatter, FieldDefinitionInterface $field_definition) {
    $form['validator'] = [
      '#type' => 'select',
      '#title' => $this->t('Validator'),
      '#description' => $this->t("Default=Swagger.io's online validator, None= No validation, Custom=Provide a custom validator url"),
      '#default_value' => $formatter->getSetting('validator'),
      '#options' => [
        'none' => $this->t('None'),
        'default' => $this->t('Default'),
        'custom' => $this->t('Custom'),
      ],
    ];
    $form['validator_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Validator URL'),
      '#description' => $this->t('The custom validator url to be used to validated the swagger files.'),
      '#default_value' => $formatter->getSetting('validator_url'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $field_definition->getName() . '][settings_edit_form][settings][validator]"]' => ['value' => 'custom'],
        ],
      ],
    ];
    $form['doc_expansion'] = [
      '#type' => 'select',
      '#title' => $this->t('Doc Expansion'),
      '#description' => $this->t('Controls how the API listing is displayed.'),
      '#default_value' => $formatter->getSetting('doc_expansion'),
      '#options' => [
        'none' => $this->t('None - Expands nothing'),
        'list' => $this->t('List - Expands only tags'),
        'full' => $this->t('Full - Expands tags and operations'),
      ],
    ];
    $form['show_top_bar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Top Bar'),
      '#description' => $this->t('Controls whether the Swagger UI top bar should be displayed or not.'),
      '#default_value' => $formatter->getSetting('show_top_bar'),
    ];
    $form['sort_tags_by_name'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Sort tags by name'),
      '#description' => $this->t('Controls whether the tag groups should be ordered alphabetically or not.'),
      '#default_value' => $formatter->getSetting('sort_tags_by_name'),
    ];
    $form['supported_submit_methods'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Try it out support for HTTP Methods'),
      '#description' => $this->t('List of HTTP methods that have the Try it out feature enabled. Selecting none disables Try it out for all operations. This does not filter the operations from the display.'),
      '#default_value' => $formatter->getSetting('supported_submit_methods'),
      '#options' => [
        'get' => $this->t('GET'),
        'put' => $this->t('PUT'),
        'post' => $this->t('POST'),
        'delete' => $this->t('DELETE'),
        'options' => $this->t('OPTIONS'),
        'head' => $this->t('HEAD'),
        'patch' => $this->t('PATCH'),
      ],
    ];
  }

  /**
   * Adds Swagger UI specific settings summary.
   *
   * @param array $summary
   *   Settings summary.
   * @param \Drupal\Core\Field\FormatterInterface $formatter
   *   The current field formatter instance.
   */
  protected function addSettingsSummary(array &$summary, FormatterInterface $formatter) {
    $supported_submit_methods = array_filter($formatter->getSetting('supported_submit_methods'));
    $summary[] = $this->t('Uses %validator validator, Doc Expansion of %doc_expansion, Shows top bar: %show_top_bar, Tags sorted by name: %sort_tags_by_name, Try it out support for HTTP Methods: %supported_submit_methods.', [
      '%validator' => $formatter->getSetting('validator'),
      '%doc_expansion' => $formatter->getSetting('doc_expansion'),
      '%show_top_bar' => $formatter->getSetting('show_top_bar') ? $this->t('Yes') : $this->t('No'),
      '%sort_tags_by_name' => $formatter->getSetting('sort_tags_by_name') ? $this->t('Yes') : $this->t('No'),
      '%supported_submit_methods' => !empty($supported_submit_methods) ? implode(', ', array_map('strtoupper', $supported_submit_methods)) : $this->t('None'),
    ]);
  }

  /**
   * Helper function to attach library definitions and pass JavaScript settings.
   *
   * @param array $element
   *   A renderable array of the field element.
   * @param array $swagger_files
   *   An array of Swagger file paths to pass to Swagger UI.
   * @param \Drupal\Core\Field\FormatterInterface $formatter
   *   The current field formatter.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition in the current field formatter.
   *
   * @return array
   *   A renderable array of the field element with attached libraries.
   */
  protected function attachLibraries(array $element, array $swagger_files, FormatterInterface $formatter, FieldDefinitionInterface $field_definition) {
    if (!empty($swagger_files) && _swagger_ui_formatter_get_library_path()) {
      $element['#attached'] = [
        'library' => [
          'swagger_ui_formatter/swagger_ui_formatter.swagger_ui',
          'swagger_ui_formatter/swagger_ui_formatter.swagger_ui_integration',
        ],
        'drupalSettings' => [
          'swaggerUIFormatter' => [
            $field_definition->getName() => [
              'svgDefinition' => _swagger_ui_formatter_get_svg_definition(),
              'swaggerFiles' => $swagger_files,
              'validator' => $formatter->getSetting('validator'),
              'validatorUrl' => $formatter->getSetting('validator_url'),
              'docExpansion' => $formatter->getSetting('doc_expansion'),
              'showTopBar' => $formatter->getSetting('show_top_bar'),
              'sortTagsByName' => $formatter->getSetting('sort_tags_by_name'),
              'supportedSubmitMethods' => array_keys(array_filter($formatter->getSetting('supported_submit_methods'))),
            ],
          ],
        ],
      ];
    }
    return $element;
  }

}
