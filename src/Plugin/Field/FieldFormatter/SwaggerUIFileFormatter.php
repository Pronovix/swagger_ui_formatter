<?php

namespace Drupal\swagger_ui_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class SwaggerUIFileFormatter extends FileFormatterBase implements ContainerFactoryPluginInterface {

  use SwaggerUIFormatterTrait;

  /**
   * Cached file entities by parent entity.
   *
   * Associative array where a key is an entity id that a field belongs and
   * values are file entities referenced by the field. File entities also
   * keyed by id.
   *
   * @var array
   */
  private $fileEntityCache = [];

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Constructs a SwaggerUIFileFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   String translation.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, TranslationInterface $string_translation, LoggerInterface $logger) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->stringTranslation = $string_translation;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('string_translation'),
      $container->get('logger.channel.swagger_ui_formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    static::addDefaultSettings($settings);
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $this->addSettingsSummary($summary, $this);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $this->alterSettingsForm($form, $form_state, $this, $this->fieldDefinition);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSwaggerFileUrlFromField(FieldItemInterface $field_item, array $context = []) {
    if (!isset($this->fileEntityCache[$context['field_items']->getEntity()->id()])) {
      // Store file entities keyed by their id.
      $this->fileEntityCache[$context['field_items']->getEntity()->id()] = array_reduce($this->getEntitiesToView($context['field_items'], $context['lang_code']), function (array $carry, File $entity) {
        $carry[$entity->id()] = $entity;
        return $carry;
      }, []);
    }

    // This is only set if the file entity exists and the current user has
    // access to the entity.
    if (isset($this->fileEntityCache[$context['field_items']->getEntity()->id()][$field_item->getValue()['target_id']])) {
      /** @var \Drupal\file\Entity\File $file */
      $file = $this->fileEntityCache[$context['field_items']->getEntity()->id()][$field_item->getValue()['target_id']];
      $url = file_create_url($file->getFileUri());
      if ($url === FALSE) {
        $this->logger->error('URL could not be created for %file file.', ['%file' => $file->label(), 'link' => $context['field_items']->getEntity()->toLink($this->t('view'))->toString()]);
        return NULL;
      }

      return $url;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    return $this->buildRenderArray($items, $this, $this->fieldDefinition, ['lang_code' => $langcode]);
  }

}
