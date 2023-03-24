<?php

declare(strict_types = 1);

namespace Drupal\swagger_ui_formatter\Exception;

/**
 * Base exception for Swagger UI library discovery.
 */
final class SwaggerUiLibraryDiscoveryException extends \RuntimeException implements SwaggerUiLibraryDiscoveryExceptionInterface {

  public const CODE_INVALID_DIR = 1;

  public const CODE_REQUIRED_FILE_IS_NOT_FOUND = 2;

  public const CODE_CANNOT_READ_PACKAGE_JSON_CONTENT = 3;

  public const CODE_CANNOT_DECODE_PACKAGE_JSON = 4;

  public const CODE_UNABLE_TO_IDENTIFY_LIBRARY_VERSION = 5;

  public const CODE_LIBRARY_VERSION_IS_NOT_SUPPORTED = 6;

  /**
   * {@inheritdoc}
   *
   * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
   */
  private function __construct(string $message = '', int $code = 0, \Throwable $previous = NULL) {
    parent::__construct($message, $code, $previous);
  }

  /**
   * Thrown when the Swagger UI library directory path is invalid.
   *
   * @param string $library_dir
   *   The path of the Swagger UI library directory.
   *
   * @return self
   *   The exception.
   */
  public static function becauseLibraryDirectoryIsInvalid(string $library_dir): self {
    return new self(sprintf('The provided "%s" Swagger UI library directory is invalid.', $library_dir), self::CODE_INVALID_DIR);
  }

  /**
   * Thrown when a required Swagger UI library file is not found.
   *
   * @param string $file_path
   *   The path of the required Swagger UI library file.
   *
   * @return self
   *   The exception.
   */
  public static function becauseRequiredLibraryFileIsNotFound(string $file_path): self {
    return new self(sprintf('The Swagger UI library directory is invalid because the required "%s" file is not found.', $file_path), self::CODE_REQUIRED_FILE_IS_NOT_FOUND);
  }

  /**
   * Thrown when cannot read the Swagger UI library's package.json file.
   *
   * @param string $package_json_path
   *   The Swagger UI library's package.json file path.
   *
   * @return self
   *   The exception.
   */
  public static function becauseCannotReadPackageJsonContent(string $package_json_path): self {
    return new self(sprintf('Cannot read the content of the Swagger UI library\'s package.json file in "%s".', $package_json_path), self::CODE_CANNOT_READ_PACKAGE_JSON_CONTENT);
  }

  /**
   * Thrown when the Swagger UI library's package.json file cannot be decoded.
   *
   * @param string $package_json_path
   *   The Swagger UI library's package.json file path.
   * @param string $json_last_error_msg
   *   The error message of the last json_decode() call.
   *
   * @return self
   *   The exception.
   */
  public static function becausePackageJsonCannotBeDecoded(string $package_json_path, string $json_last_error_msg): self {
    return new self(sprintf('Cannot decode the Swagger UI library\'s package.json file in "%s": "%s".', $package_json_path, $json_last_error_msg), self::CODE_CANNOT_DECODE_PACKAGE_JSON);
  }

  /**
   * Thrown when the library version is not found in the package.json file.
   *
   * @param string $package_json_path
   *   The Swagger UI library's package.json file path.
   *
   * @return self
   *   The exception.
   */
  public static function becauseUnableToIdentifyLibraryVersion(string $package_json_path): self {
    return new self(sprintf('The Swagger UI library version is not found in "%s".', $package_json_path), self::CODE_UNABLE_TO_IDENTIFY_LIBRARY_VERSION);
  }

  /**
   * Thrown when the library version is lower than the minimum supported one.
   *
   * @param string $library_version
   *   The actual version of the Swagger UI library.
   * @param string $library_version_min
   *   The minimum supported version of the Swagger UI library.
   *
   * @return self
   *   The exception.
   */
  public static function becauseLibraryVersionIsNotSupported(string $library_version, string $library_version_min): self {
    return new self(sprintf('The Swagger UI library version v%s is lower than the minimally supported v%s. Please download <a href="https://github.com/swagger-api/swagger-ui/releases" target="_blank">a newer version</a>.', $library_version, $library_version_min), self::CODE_LIBRARY_VERSION_IS_NOT_SUPPORTED);
  }

}
