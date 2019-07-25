const path = require('path');
const webpack = require('webpack');
const tools = require('./webpack');

module.exports = function(){
    const config = {
        entry: {
            './assets/js/admin/admin': './assets/src/js/admin/admin.js',
            './assets/js/admin/learnpress': './assets/src/js/admin/learnpress.js',
            './assets/js/admin/utils': './assets/src/js/admin/utils/index.js',
            './assets/js/admin/editor/course': './assets/src/js/admin/editor/course.js',
            './assets/js/admin/editor/quiz': './assets/src/js/admin/editor/quiz.js',
            './assets/js/admin/editor/question': './assets/src/js/admin/editor/question.js',
            './assets/js/admin/conditional-logic': './assets/src/js/admin/utils/conditional-logic.js',
            './assets/js/admin/partial/meta-box-order': './assets/src/js/admin/partial/meta-box-order.js',
            './assets/js/admin/pages/statistic': './assets/src/js/admin/pages/statistic.js',
            './assets/js/admin/pages/setup': './assets/src/js/admin/pages/setup.js',
            './assets/js/frontend/learnpress': './assets/src/js/frontend/learnpress.js',
            './assets/js/frontend/utils': './assets/src/js/frontend/utils/index.js',
            './assets/js/global': './assets/src/js/global.js',
            './assets/js/utils': './assets/src/js/utils/index.js',
        },
        output: {
            path: path.resolve(__dirname),
            filename: 'production' === process.env.NODE_ENV ? '[name].min.js' : '[name].js',
        },
        plugins: [
            tools.mergeAndCompressJs
        ]
    }

    return config;
}