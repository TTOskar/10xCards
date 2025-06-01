const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .addEntry('form-validation', './assets/js/form-validation.js')
    .addEntry('progress-indicator', './assets/js/progress-indicator.js')
    .addEntry('flashcard-actions', './assets/js/flashcard-actions.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })
    .enableStimulusBridge('./assets/controllers.json')
    .enablePostCssLoader()
    .enableSassLoader();

module.exports = Encore.getWebpackConfig(); 