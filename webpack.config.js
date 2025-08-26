const Encore = require('@symfony/webpack-encore');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')

    // main entry for your JS
    .addEntry('vitrine', './assets/vitrine/app.js')

    // enables Sass/SCSS support
    .enableSassLoader()

    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables Symfony UX/Stimulus (if you use it)
    // .enableStimulusBridge('./assets/controllers.json')

    // enables PostCSS (autoprefixer)
    .enablePostCssLoader()

    // Recommended for Symfony apps
    .enableSingleRuntimeChunk()
    ;

module.exports = Encore.getWebpackConfig();
