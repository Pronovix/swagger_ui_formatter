/**
 * @file A script to find the installed version of the swagger-ui package
 * and write it to an output file.
 * @internal
 */

const fs = require('fs');
const path = require('path');

/**
 * The name of the npm package to find the version for.
 * @const {string}
 */
const packageName = 'swagger-ui-dist';

/**
 * The absolute path to the output file where the version will be stored.
 * @const {string}
 */
const outputFilePath = path.join(__dirname, 'swagger_ui_version.json');

try {
  // Get the directory containing the package from its main file path.
  const packageMain = require.resolve(packageName);
  const packageJsonPath = path.join(path.dirname(packageMain), './package.json');

  // Read the package.json file and parse it.
  const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));
  const packageVersion = packageJson.version;

  // Write the version to the specified output file.
  const versionData = {
    'swagger-ui-version': packageVersion,
  };
  fs.writeFileSync(outputFilePath,  JSON.stringify(versionData), 'utf8');
} catch (error) {
  console.error(`Error: Could not determine or write version for ${packageName}.`, error);
  process.exit(1);
}
