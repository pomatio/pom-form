const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

module.exports = {
  entry: {
    'admin': './src/dist/css/admin.scss',
    'background-image': './src/dist/css/background-image.scss',
    'color-palette': './src/dist/css/color-palette.scss',
    'gallery': './src/dist/css/gallery.scss',
    'icon-picker': './src/dist/css/icon-picker.scss',
    'image-picker': './src/dist/css/image-picker.scss',
    'quantity': './src/dist/css/quantity.scss',
    'radio-icons': './src/dist/css/radio-icons.scss',
    'range': './src/dist/css/range.scss',
    'repeater': './src/dist/css/repeater.scss',
    'signature': './src/dist/css/signature.scss',
    'toggle': './src/dist/css/toggle.scss',
  },
  output: {
    filename: '[name].min.js',
    path: path.resolve(__dirname, 'src/dist/css')
  },
  module: {
    rules: [
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              importLoaders: 2,
              sourceMap: false
            }
          },
          'sass-loader'
        ]
      }
    ]
  },
  plugins: [
    new RemoveEmptyScriptsPlugin(),
    new MiniCssExtractPlugin({
      filename: '[name].min.css'
    })
  ],
  mode: 'production',
  optimization: {
    minimize: true,
    minimizer: [
      new CssMinimizerPlugin()
    ]
  },
  devtool: false
};
