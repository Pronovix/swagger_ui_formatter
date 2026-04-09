<?php

/**
 * @file
 * Dynamic include file for PHPStan.
 */

declare(strict_types = 1);

if (PHP_SAPI !== 'cli') {
  return;
}

use Composer\InstalledVersions;
use Composer\Semver\Semver;

$config = [];
$includes = [];

$phpstan_version = InstalledVersions::getVersion('phpstan/phpstan-src');
assert(is_string($phpstan_version));
if (Semver::satisfies($phpstan_version, '^1')) {
  $includes[] = './phpstan-baseline-v1.neon';
}
elseif (Semver::satisfies($phpstan_version, '^2')) {
  $includes[] = './phpstan-baseline.neon';
}

$config['includes'] = $includes;

return $config;
