<?php

/**
 * @file
 * Custom autoloader for Drupal PHPUnit testing.
 *
 * Forked version of a removed solution from Pronovix/drupal-qa.
 * @see https://github.com/Pronovix/drupal-qa/commit/5f1bf60dfe2b78de7a5a69baee3f7db61edf1b51.
 */

use Composer\Autoload\ClassLoader;
use Drupal\Tests\swagger_ui_formatter\DrupalExtensionFilterIterator;

/**
 * Finds all valid extension directories recursively within a given directory.
 *
 * @param string $scan_directory
 *   The directory that should be recursively scanned.
 * @return array
 *   An associative array of extension directories found within the scanned
 *   directory, keyed by extension name.
 */
function drupal_phpunit_find_extension_directories($scan_directory) {
  $extensions = [];
  $filter = new DrupalExtensionFilterIterator(new \RecursiveDirectoryIterator($scan_directory, \RecursiveDirectoryIterator::FOLLOW_SYMLINKS | \FilesystemIterator::CURRENT_AS_SELF));
  $filter->acceptTests(TRUE);
  $dirs = new \RecursiveIteratorIterator($filter);
  foreach ($dirs as $dir) {
    if (strpos($dir->getPathname(), '.info.yml') !== FALSE) {
      // Cut off ".info.yml" from the filename for use as the extension name. We
      // use getRealPath() so that we can scan extensions represented by
      // directory aliases.
      $extensions[substr($dir->getFilename(), 0, -9)] = $dir->getPathInfo()
        ->getRealPath();
    }
  }
  return $extensions;
}

/**
 * Returns directories under which contributed extensions may exist.
 *
 * @param string $root
 *   (optional) Path to the root of the Drupal installation.
 *
 * @return array
 *   An array of directories under which contributed extensions may exist.
 */
function drupal_phpunit_contrib_extension_directory_roots($root = NULL) {
  if ($root === NULL) {
    $root = dirname(__DIR__, 2);
  }
  $paths = [
    $root . '/core',
    $root . '/',
  ];
  $sites_path = $root . '/sites';
  // Note this also checks sites/../modules and sites/../profiles.
  foreach (scandir($sites_path) ?: [] as $site) {
    if ($site[0] === '.' || $site === 'simpletest') {
      continue;
    }
    $path = "$sites_path/$site";
    $paths[] = is_dir("$path/modules") ? realpath("$path/modules") : '';
    $paths[] = is_dir("$path/profiles") ? realpath("$path/profiles") : '';
    $paths[] = is_dir("$path/themes") ? realpath("$path/themes") : '';
  }
  return array_filter($paths, 'file_exists');
}

/**
 * Registers the namespace for each extension directory with the autoloader.
 *
 * @param array $dirs
 *   An associative array of extension directories, keyed by extension name.
 *
 * @return array
 *   An associative array of extension directories, keyed by their namespace.
 */
function drupal_phpunit_get_extension_namespaces($dirs) {
  $suite_names = ['Unit', 'Kernel', 'Functional', 'Build', 'FunctionalJavascript'];
  $namespaces = [];
  foreach ($dirs as $extension => $dir) {
    if (is_dir($dir . '/src')) {
      // Register the PSR-4 directory for module-provided classes.
      $namespaces['Drupal\\' . $extension . '\\'][] = $dir . '/src';
    }
    $test_dir = $dir . '/tests/src';
    if (is_dir($test_dir)) {
      foreach ($suite_names as $suite_name) {
        $suite_dir = $test_dir . '/' . $suite_name;
        if (is_dir($suite_dir)) {
          // Register the PSR-4 directory for PHPUnit-based suites.
          $namespaces['Drupal\\Tests\\' . $extension . '\\' . $suite_name . '\\'][] = $suite_dir;
        }
      }
      // Extensions can have a \Drupal\Tests\extension\Traits namespace for
      // cross-suite trait code.
      $trait_dir = $test_dir . '/Traits';
      if (is_dir($trait_dir)) {
        $namespaces['Drupal\\Tests\\' . $extension . '\\Traits\\'][] = $trait_dir;
      }
    }
  }
  return $namespaces;
}

// We define the COMPOSER_INSTALL constant, so that PHPUnit knows where to
// autoload from. This is needed for tests run in isolation mode, because
// phpunit.xml.dist is located in a non-default directory relative to the
// PHPUnit executable.
if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
  define('PHPUNIT_COMPOSER_INSTALL', __DIR__ . '/../../autoload.php');
}

/**
 * Populate class loader with additional namespaces for tests.
 *
 * We run this in a function to avoid setting the class loader to a global
 * that can change. This change can cause unpredictable false positives for
 * phpunit's global state change watcher. The class loader can be retrieved from
 * composer at any time by requiring autoload.php.
 */
function drupal_phpunit_populate_class_loader(): ClassLoader {
  $webroot = dirname(__DIR__, 2) . '/build/web';
  /** @var \Composer\Autoload\ClassLoader $loader */
  $loader = require "{$webroot}/autoload.php";
  $core_tests_dir = "{$webroot}/core/tests";

  // Start with classes in known locations.
  $loader->add('Drupal\\Tests', $core_tests_dir);
  $loader->add('Drupal\\TestSite', $core_tests_dir);
  $loader->add('Drupal\\KernelTests', $core_tests_dir);
  $loader->add('Drupal\\FunctionalTests', $core_tests_dir);
  $loader->add('Drupal\\FunctionalJavascriptTests', $core_tests_dir);
  $loader->add('Drupal\\TestTools', $core_tests_dir);

  if (!isset($GLOBALS['namespaces'])) {
    // Scan for arbitrary extension namespaces from core and contrib.
    $extension_roots = drupal_phpunit_contrib_extension_directory_roots($webroot);

    $dirs = array_map('drupal_phpunit_find_extension_directories', $extension_roots);
    $dirs = array_reduce($dirs, 'array_merge', []);
    $GLOBALS['namespaces'] = drupal_phpunit_get_extension_namespaces($dirs);
  }
  foreach ($GLOBALS['namespaces'] as $prefix => $paths) {
    $loader->addPsr4($prefix, $paths);
  }

  return $loader;
}

// Do class loader population.
$loader = drupal_phpunit_populate_class_loader();

// Set sane locale settings, to ensure consistent string, dates, times and
// numbers handling.
// @see \Drupal\Core\DrupalKernel::bootEnvironment()
setlocale(LC_ALL, 'C');

// Set appropriate configuration for multi-byte strings.
mb_internal_encoding('utf-8');
mb_language('uni');

// Set the default timezone. While this doesn't cause any tests to fail, PHP
// complains if 'date.timezone' is not set in php.ini. The Australia/Sydney
// timezone is chosen so all tests are run using an edge case scenario (UTC+10
// and DST). This choice is made to prevent timezone related regressions and
// reduce the fragility of the testing system in general.
date_default_timezone_set('Australia/Sydney');

// Ensure ignored deprecation patterns listed in .deprecation-ignore.txt are
// considered in testing.
if (getenv('SYMFONY_DEPRECATIONS_HELPER') === FALSE) {
  $deprecation_ignore_filename = realpath(__DIR__ . "/../.deprecation-ignore.txt");
  putenv("SYMFONY_DEPRECATIONS_HELPER=ignoreFile=$deprecation_ignore_filename");
}

// Drupal expects to be run from its root directory. This ensures all test types
// are consistent.
chdir(dirname(__DIR__, 2) . '/build/web');
