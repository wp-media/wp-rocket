const path = require('path');

module.exports = (env, argv) => {
  let production = argv.mode === 'production'

  return {

    entry: {
      'rucss-progress-bar': path.resolve(__dirname, 'src/js/react/rucss-progress-bar.js'),
    },

    output: {
      filename: '[name].js',
      path: path.resolve(__dirname, 'assets/js/react/'),
    },

    devtool: production ? '' : 'source-map',

    resolve: {
      extensions: [".js", ".jsx", ".json"],
    },

    module: {
      rules: [
        {
          test: /\.jsx?$/,
          exclude: /node_modules/,
          loader: 'babel-loader',
        },
      ],
    },
  };
}
