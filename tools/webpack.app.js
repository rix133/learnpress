const path = require('path');
const webpack = require('webpack');

module.exports = function(){
    const config = {
        entry: {
            './assets/js/frontend/app': './assets/src/js/frontend/app/app.js'
        },
        output: {
            path: path.dirname(path.resolve(__dirname)),
            filename: 'production' === process.env.NODE_ENV ? '[name].min.js' : '[name].js',
        },
    };

    return config;
}