<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
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
   * phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingTraversablePropertyTypeHintSpecification
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
   * File URL generator.
   *
   * Due to Drupal <9.3.0 support we have to allow NULL, that is the simplest
   * option.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface|null
   */
  private ?FileUrlGeneratorInterface $fileUrlGenerator;

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
   * @param \Drupal\Core\File\FileUrlGeneratorInterface|null $file_url_generator
   *   File URL generator.
   *   Due to Drupal <9.3.0 support we have to allow NULL, that is the simplest
   *   option.
   */
  public function __construct(string $plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, string $label, string $view_mode, array $third_party_settings, TranslationInterface $string_translation, LoggerInterface $logger, ?FileUrlGeneratorInterface $file_url_generator = NULL) {
    // phpcs:ignore DrupalPractice.Objects.GlobalDrupal.GlobalDrupal
    if (!$file_url_generator) {
      // The nicest thing that can be said about the required format by this
      // sniff is "insufficient".
      // phpcs:ignore Drupal.Semantics.FunctionTriggerError
      @trigger_error('Calling SwaggerUIFileFormatter::__construct() without the $file_url_generator argument is deprecated in swagger_ui_formatter:3.4 and will be required before swagger_ui_formatter:4.0.', E_USER_DEPRECATED);
      // phpcs:ignore DrupalPractice.Objects.GlobalDrupal.GlobalDrupal
      $file_url_generator = \Drupal::service('file_url_generator');
    }
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->stringTranslation = $string_translation;
    $this->logger = $logger;
    $this->fileUrlGenerator = $file_url_generator;
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
      $container->get('logger.channel.swagger_ui_formatter'),
      $container->has('file_url_generator') ? $container->get('file_url_generator') : NULL
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
  protected function getSwaggerFileUrlFromField(FieldItemInterface $field_item, array $context = []): ?string {
    if (!isset($this->fileEntityCache[$context['field_items']->getEntity()->id()])) {
      // Store file entities keyed by their id.
      $this->fileEntityCache[$context['field_items']->getEntity()->id()] = array_reduce($this->getEntitiesToView($context['field_items'], $context['lang_code']), static function (array $carry, File $entity) {
        $carry[$entity->id()] = $entity;
        return $carry;
      }, []);
    }

    // This is only set if the file entity exists and the current user has
    // access to the entity.
    if (isset($this->fileEntityCache[$context['field_items']->getEntity()->id()][$field_item->getValue()['target_id']])) {
      /** @var \Drupal\file\Entity\File $file */
      $file = $this->fileEntityCache[$context['field_items']->getEntity()->id()][$field_item->getValue()['target_id']];
      $url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      if ($url === FALSE) {
        $this->logger->error('URL could not be created for %file file.', [
          '%file' => $file->label(),
          'link' => $context['field_items']->getEntity()->toLink($this->t('view'))->toString(),
        ]);
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
