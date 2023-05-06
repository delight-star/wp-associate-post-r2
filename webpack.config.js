const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );

module.exports = {
    ...defaultConfig,
    plugins: [
        ...defaultConfig.plugins.filter(
            plugin => plugin.constructor.name !== 'CleanWebpackPlugin',
        ),
        new CleanWebpackPlugin( {
            cleanOnceBeforeBuildPatterns: [ '**/*', '!src/**' ],
            cleanAfterEveryBuildPatterns: [ '!fonts/**', '!images/**' ],
            cleanStaleWebpackAssets: false,
        } ),
    ],
};