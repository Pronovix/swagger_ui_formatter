<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\Exception\InvalidStreamWrapperException;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\file\FileInterface;
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
final class SwaggerUIFileFormatter extends FileFormatterBase implements ContainerFactoryPluginInterface {

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
   * @phpstan-var array<string|int,array<string|int,\Drupal\file\FileInterface>>
   */
  private array $fileEntityCache = [];

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private LoggerInterface $logger;

  /**
   * File URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  private FileUrlGeneratorInterface $fileUrlGenerator;

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
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   File URL generator.
   */
  public function __construct(string $plugin_id, mixed $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, string $label, string $view_mode, array $third_party_settings, TranslationInterface $string_translation, LoggerInterface $logger, FileUrlGeneratorInterface $file_url_generator) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->stringTranslation = $string_translation;
    $this->logger = $logger;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('string_translation'),
      $container->get('logger.channel.swagger_ui_formatter'),
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    $settings = parent::defaultSettings();
    self::addDefaultSettings($settings);
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = parent::settingsSummary();
    $this->addSettingsSummary($summary, $this);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
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
      /** @var \Drupal\file\FileInterface[] $available_entities */
      $available_entities = $this->getEntitiesToView($context['field_items'], $context['lang_code']);
      $this->fileEntityCache[$context['field_items']->getEntity()->id()] = array_reduce($available_entities, static function (array $carry, FileInterface $entity) {
        $carry[$entity->id()] = $entity;
        return $carry;
      }, []);
    }

    // This is only set if the file entity exists and the current user has
    // access to the entity.
    if (isset($this->fileEntityCache[$context['field_items']->getEntity()->id()][$field_item->getValue()['target_id']])) {
      $file = $this->fileEntityCache[$context['field_items']->getEntity()->id()][$field_item->getValue()['target_id']];
      try {
        return $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
      catch (InvalidStreamWrapperException $e) {
        $this->logger->error('URL could not be created for %file file.', [
          '%file' => $file->label(),
          'link' => $context['field_items']->getEntity()->toLink($this->t('view'))->toString(),
        ]);
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    return $this->buildRenderArray($items, $this, $this->fieldDefinition, ['lang_code' => $langcode]);
  }

}
