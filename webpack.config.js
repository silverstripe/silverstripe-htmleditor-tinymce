const Path = require('path');
const { JavascriptWebpackConfig, CssWebpackConfig } = require('@silverstripe/webpack-config');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const PATHS = {
  ROOT: Path.resolve(),
  SRC: Path.resolve('client/src'),
  DIST: Path.resolve('client/dist'),
  MODULES: 'node_modules',
};

const config = [
  // JavaScript
  new JavascriptWebpackConfig('js', PATHS, 'silverstripe/htmleditor-tinymce')
    .setEntry({
      bundle: `${PATHS.SRC}/bundle.js`,
      // default plugins
      'TinyMCE_sslink': `${PATHS.SRC}/plugins/TinyMCE_sslink.js`,
      'TinyMCE_sslink-external': `${PATHS.SRC}/plugins/TinyMCE_sslink-external.js`,
      'TinyMCE_sslink-email': `${PATHS.SRC}/plugins/TinyMCE_sslink-email.js`,
      // asset-admin plugins
      'TinyMCE_ssmedia': `${PATHS.SRC}/plugins/TinyMCE_ssmedia.js`,
      'TinyMCE_ssembed': `${PATHS.SRC}/plugins/TinyMCE_ssembed.js`,
      'TinyMCE_sslink-file': `${PATHS.SRC}/plugins/TinyMCE_sslink-file.js`,
      // cms plugins
      'TinyMCE_sslink-internal': `${PATHS.SRC}/plugins/TinyMCE_sslink-internal.js`,
      'TinyMCE_sslink-anchor': `${PATHS.SRC}/plugins/TinyMCE_sslink-anchor.js`,
    })
    .mergeConfig({
      plugins: [
        new CopyWebpackPlugin({
          patterns: [
            // Copy npm and custom tinymce content into the same dist directory
            {
              from: `${PATHS.MODULES}/tinymce`,
              to: `${PATHS.DIST}/tinymce`
            },
            {
              from: `${PATHS.SRC}/tinymce`,
              to: `${PATHS.DIST}/tinymce`
            },
          ]
        }),
      ],
    })
    .getConfig(),
  // sass to css
  new CssWebpackConfig('css', PATHS)
    .setEntry({
      editor: `${PATHS.SRC}/styles/editor.scss`,
    })
    .getConfig(),
];

// Use WEBPACK_CHILD=js or WEBPACK_CHILD=css env var to run a single config
module.exports = (process.env.WEBPACK_CHILD)
  ? config.find((entry) => entry.name === process.env.WEBPACK_CHILD)
  : config;
