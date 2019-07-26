const path = require('path');
const webpack = require('webpack');
const {get, escapeRegExp, compact} = require('lodash');
const {basename, sep} = require('path');
const {camelCaseDash} = require('@wordpress/scripts/utils');
const CustomTemplatedPathPlugin = require('@wordpress/custom-templated-path-webpack-plugin');

const packages = {
    'course': 'course',
    'lesson': 'lesson',
    'app': 'app.js'
}

module.exports = function () {
    const config = {
        entry: Object.keys(packages).reduce((memo, packageName) => {
            const name = camelCaseDash(packageName);
            memo[name] = `./assets/src/js/frontend/app/${ packageName }`;
            return memo;
        }, {}),
        output: {
            path: path.dirname(path.resolve(__dirname)),
            filename: 'assets/js/frontend/app/[basename]/[name]' + ('production' === process.env.NODE_ENV ? '.min' : '') + '.js',
            library: ['LP_LEARN', '[name]'],
            libraryTarget: 'this',
        },
        plugins: [
            new CustomTemplatedPathPlugin({
                basename(path, data) {

                    if(get(data, ['chunk', 'id']) === 'app'){
                        return '';
                    }

                    let rawRequest;
                    const entryModule = get(data, ['chunk', 'entryModule'], {});
                    switch (entryModule.type) {
                        case 'javascript/auto':
                            rawRequest = entryModule.rawRequest;
                            break;

                        case 'javascript/esm':
                            rawRequest = entryModule.rootModule.rawRequest;
                            break;
                    }

                    if (rawRequest) {
                        return basename(rawRequest);
                    }

                    return path;
                },
            }),
        ]
    };

    return config;
}