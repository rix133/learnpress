const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const args = require('yargs').argv;

// Set different CSS extraction for editor only and common block styles
const blocksCSSPlugin = new ExtractTextPlugin({
    filename: './assets/css/main.css',
});


// Configuration for the ExtractTextPlugin.
const extractConfig = {
    use: [
        {loader: 'raw-loader'},
        {
            loader: 'postcss-loader',
            options: {
                plugins: [require('autoprefixer')],
            },
        },
        {
            loader: 'sass-loader',
            query: {
                outputStyle: 'production' === process.env.NODE_ENV ? 'compressed' : 'nested'
            },
        },
    ],
};

const getCustomOption = function getCustomOption(name) {
    var def = args.define;
    if (!def) {
        return undefined;
    }


};

const moduleConfig = {
    admin: require('./tools/webpack.admin')(),
    app: require('./tools/webpack.app')()
};

module.exports = function () {
    var config = {
        watch: 'production' !== process.env.NODE_ENV,
        devtool: process.env.NODE_ENV === 'production' ? '' : 'source-map',
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /(node_modules|bower_components)/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['babel-preset-env', 'es2015']
                        }
                    },
                },
                {
                    test: /([a-zA-Z0-9\s_\\.\-\(\):])+(.s?css)$/,
                    use: blocksCSSPlugin.extract(extractConfig),
                },
            ],
        },
        plugins: [
            blocksCSSPlugin
        ]
    }

    var where = args.define || 'app';

    config = {
        ...config,
        ...moduleConfig[where]
    }

    return config;
};