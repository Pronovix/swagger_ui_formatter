const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');

module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production';
  return {
    entry: {
      'swagger-ui-bundle': './src/SwaggerUIMainLoader.js',
      'swagger-ui-standalone-preset': './src/SwaggerUIStandalonePresetLoader.js'
    },
    module: {
      rules: [
        {
          test: /\.css$/i,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader'
          ]
        }
      ]
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: 'swagger-ui.css'
      }),
      new CopyWebpackPlugin({
        patterns: [
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
      filename: '[name].js',
      path: path.resolve(__dirname, 'dist'),
      clean: true
    },
    optimization: {
      minimize: isProduction,
      minimizer: [
        new TerserPlugin({
          parallel: true,
          terserOptions: { ecma: 2020 }
        })
      ],
    },
    resolve: {
      fallback: {
        "path": require.resolve("path-browserify")
      }
    },
    devtool: isProduction ? false : 'source-map'
  }
};
