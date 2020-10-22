<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides common methods for Swagger UI field formatters.
 *
 * @internal
 */
trait SwaggerUIFormatterTrait {

  use StringTranslationTrait;

  /**
   * Adds Swagger UI field formatter settings to formatter settings.
   *
   * @param array $settings
   *   Settings inherited from the parent class.
   */
  final protected static function addDefaultSettings(array &$settings): void {
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
  final protected function alterSettingsForm(array &$form, FormStateInterface $form_state, FormatterInterface $formatter, FieldDefinitionInterface $field_definition): void {
    $form['validator'] = [
      '#type' => 'select',
      '#title' => $this->t('Validator'),
      '#description' => $this->t("Default=Swagger.io's online validator, None=No validation, Custom=Provide a custom validator url"),
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
  final protected function addSettingsSummary(array &$summary, FormatterInterface $formatter): void {
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
   * Builds a render array from a field.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Field items.
   * @param \Drupal\Core\Field\FormatterInterface $formatter
   *   The current field formatter.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition in the current field formatter.
   * @param array $context
   *   Additional context for field rendering.
   *
   * @return array
   *   Field value as a render array.
   */
  final protected function buildRenderArray(FieldItemListInterface $items, FormatterInterface $formatter, FieldDefinitionInterface $field_definition, array $context = []): array {
    $element = [];
    $library_name = 'swagger_ui_formatter.swagger_ui_integration';
    /** @var \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery */
    $library_discovery = \Drupal::service('library.discovery');
    /** @var \Drupal\swagger_ui_formatter\Service\SwaggerUiLibraryDiscoveryInterface $swagger_ui_library_discovery */
    $swagger_ui_library_discovery = \Drupal::service('swagger_ui_formatter.swagger_ui_library_discovery');

    // The Swagger UI library integration is only registered if the Swagger UI
    // library directory and version is correct.
    if ($library_discovery->getLibraryByName('swagger_ui_formatter', $library_name) === FALSE) {
      $element = [
        '#theme' => 'status_messages',
        '#message_list' => [
          'error' => [$this->t('The Swagger UI library is missing, incorrectly defined or not supported.')],
        ],
      ];
    }
    else {
      $library_dir = $swagger_ui_library_discovery->libraryDirectory();
      // Set the oauth2-redirect.html file path for OAuth2 authentication.
      $oauth2_redirect_url = \Drupal::request()->getSchemeAndHttpHost() . '/' . $library_dir . '/dist/oauth2-redirect.html';

      foreach ($items as $delta => $item) {
        $element[$delta] = [
          '#delta' => $delta,
          '#field_name' => $field_definition->getName(),
        ];
        // It's the user's responsibility to set up field settings correctly
        // and use this field formatter with valid Swagger files. Although, it
        // could happen that a URL could not be generated from a field value.
        $swagger_file_url = $this->getSwaggerFileUrlFromField($item, $context + ['field_items' => $items]);
        if ($swagger_file_url === NULL) {
          $element[$delta] += [
            '#theme' => 'status_messages',
            '#message_list' => [
              'error' => [$this->t('Could not create URL to file.')],
            ],
          ];
        }
        else {
          $element[$delta] += [
            '#theme' => 'swagger_ui_field_item',
            '#attached' => [
              'library' => [
                'swagger_ui_formatter/' . $library_name,
              ],
              'drupalSettings' => [
                'swaggerUIFormatter' => [
                  "{$field_definition->getName()}-{$delta}" => [
                    'oauth2RedirectUrl' => $oauth2_redirect_url,
                    'swaggerFile' => $swagger_file_url,
                    'validator' => $formatter->getSetting('validator'),
                    'validatorUrl' => $formatter->getSetting('validator_url'),
                    'docExpansion' => $formatter->getSetting('doc_expansion'),
                    'showTopBar' => $formatter->getSetting('show_top_bar'),
                    'sortTagsByName' => $formatter->getSetting('sort_tags_by_name'),
                    'supportedSubmitMethods' => array_keys(array_filter($formatter->getSetting('supported_submit_methods'))),
                  ],
                ],
              ],
            ],
          ];
        }
      }
    }

    if ($swagger_ui_library_discovery instanceof CacheableDependencyInterface) {
      $cacheable_metadata = CacheableMetadata::createFromRenderArray($element)->merge(CacheableMetadata::createFromObject($swagger_ui_library_discovery));
      $cacheable_metadata->applyTo($element);
    }

    return $element;
  }

  /**
   * Creates a web-accessible URL to a Swagger file from the field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $field_item
   *   The field item.
   * @param array $context
   *   Additional context for creating the URL to the Swagger file.
   *
   * @return string|null
   *   URL to the Swagger file or null if the URL could not be created.
   */
  abstract protected function getSwaggerFileUrlFromField(FieldItemInterface $field_item, array $context = []): ?string;

}
