const { VueLoaderPlugin } = require('vue-loader');
// eslint-disable-next-line import/no-extraneous-dependencies
const path = require('path');
const buildPaths = require('./webpackPaths');

const mode = process.env.NODE_ENV || 'development';

const config = {
  mode,
  target: 'web',
  entry: {
    formShortcode: path.resolve(__dirname, buildPaths.input.formShortcode),
  },
  output: {
    filename: '[name].bundle.js',
    path: path.resolve(__dirname, buildPaths.output),
  },
  module: {
    rules: [
      {
        test: /.+\.(vue)$/,
        loader: 'vue-loader',
      },
      {
        test: /.+\.(js)$/,
        loader: 'babel-loader',
      },
      {
        test: /.+\.(css)$/,
        loader: ['vue-style-loader', 'css-loader'],
      },
    ],
  },
  plugins: [new VueLoaderPlugin()],
  resolve: {
    modules: [path.dirname(buildPaths.input.formShortcode), 'node_modules'],
    alias: {
      vue$: 'vue/dist/vue.esm.js',
    },
    extensions: ['.js', '.vue', '.css'],
  },
  devtool: mode === 'development' ? 'eval' : 'none',
};

module.exports = config;
