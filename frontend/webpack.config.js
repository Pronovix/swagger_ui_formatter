const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = (env, argv) => {
  return {
    entry: './src/index.js',
    plugins: [
      new CopyWebpackPlugin({
        patterns: [
          {
            from: require.resolve('swagger-ui-dist/swagger-ui-bundle.js'),
            to: path.resolve(__dirname, 'dist/swagger-ui-bundle.js')
          },
          {
            from: require.resolve('swagger-ui-dist/swagger-ui-standalone-preset.js'),
            to: path.resolve(__dirname, 'dist/swagger-ui-standalone-preset.js')
          },
          {
            from: require.resolve('swagger-ui-dist/swagger-ui.css'),
            to: path.resolve(__dirname, 'dist/swagger-ui.css')
          },
          {
            from: require.resolve('swagger-ui-dist/oauth2-redirect.html'),
            to: path.resolve(__dirname, 'dist/oauth2-redirect.html')
          },
          {
            from: require.resolve('swagger-ui-dist/oauth2-redirect.js'),
            to: path.resolve(__dirname, 'dist/oauth2-redirect.js')
          }
        ]
      })
    ],
    output: {
      path: path.resolve(__dirname, 'dist'),
      clean: true
    },
    optimization: {
      minimizer: [
        new TerserPlugin({
          extractComments: false,
        }),
      ],
    },
  }
};
