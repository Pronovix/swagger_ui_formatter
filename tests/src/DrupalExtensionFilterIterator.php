<?php

declare(strict_types=1);

/**
 * Forked version of a removed solution from Pronovix/drupal-qa.
 * @see https://github.com/Pronovix/drupal-qa/commit/5f1bf60dfe2b78de7a5a69baee3f7db61edf1b51.
 */

namespace Drupal\Tests\swagger_ui_formatter;

use Drupal\Core\Extension\Discovery\RecursiveExtensionFilterIterator as DrupalRecursiveExtensionFilterIterator;

/**
 * Improved version of Drupal's RecursiveExtensionFilterIterator for PHPUnit.
 *
 * This class and bootstrap.php was created to be able to run PHPUnit tests in
 * setups where modules, themes or profiles may contain a "build" folder with
 * a Drupal core and a symlink that to themselves which could lead to infinite
 * recursions when the original PHPUnit bootstrap script tries to discovery
 * available tests.
 *
 * Example structure:
 * ├── build
 * │   └── web
 * │       ├── core
 * │       ├── index.php
 * │       ├── modules
 * │       |    ├── drupal_module -> ../../../
 * └──  my_module.info.yml
 *
 * Related issues on Drupal.org:
 * https://www.drupal.org/project/drupal/issues/2943172
 * https://www.drupal.org/project/drupal/issues/3050881
 */
final class DrupalExtensionFilterIterator extends DrupalRecursiveExtensionFilterIterator
{
    /**
     * DrupalExtensionFilterIterator constructor.
     *
     * @param \RecursiveIterator $iterator
     *   The iterator to filter.
     */
    public function __construct(\RecursiveIterator $iterator)
    {
        // We should not initialize Settings here to retrieve
        // `file_scan_ignore_directories` here although that would remove some
        // code duplications.
        parent::__construct($iterator, ['build', 'node_modules', 'bower_components']);
    }
}
