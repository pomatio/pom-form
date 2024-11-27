const path = require('path');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = [
  {
    mode: 'production',
    optimization: {
      splitChunks: false,
      minimize: true,
      minimizer: [
        new TerserPlugin({
          terserOptions: {
            compress: {
              drop_console: true,
              drop_debugger: true,
              passes: 3
            },
            format: {
              comments: false
            },
            mangle: {
              toplevel: true
            }
          },
          extractComments: false,
          parallel: true
        })
      ]
    },
    devtool: false,
    entry: {
      'background_image': './src/dist/js/background_image.js',
      'code': './src/dist/js/code.js',
      'color': './src/dist/js/color.js',
      'color_palette': './src/dist/js/color_palette.js',
      'font_picker': './src/dist/js/font_picker.js',
      'gallery': './src/dist/js/gallery.js',
      'icon_picker': './src/dist/js/icon_picker.js',
      'image_picker': './src/dist/js/image_picker.js',
      'quantity': './src/dist/js/quantity.js',
      'radio_icons': './src/dist/js/radio_icons.js',
      'range': './src/dist/js/range.js',
      'repeater': './src/dist/js/repeater.js',
      'select': './src/dist/js/select.js',
      'signature': './src/dist/js/signature.js',
    },
    output: {
      filename: '[name].min.js',
      path: path.resolve(__dirname, 'src/dist/js')
    }
  }
];
