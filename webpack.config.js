const Encore = require('@symfony/webpack-encore');
const path = require('path');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')

    .addExternals({
        jquery: 'jQuery',
        $: 'jQuery'
    })

    // main entry for your JS
    .addEntry('vitrine', './assets/vitrine/app.js')

    .addEntry('admin_base', './assets/backoffice/base/app.js')
    .addEntry('admin_dashboard', './assets/backoffice/dashboard/dashboard.js')
    .addEntry('admin_plangen', './assets/backoffice/plangen/plangen.js')
    .addEntry('admin_planjour', './assets/backoffice/planjour/planjour.js')
    .addEntry('admin_planning_modern', './assets/backoffice/planning_modern/planning_modern.js')

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
    .copyFiles({
        from: './assets/vitrine/images',
        to: 'images/[name].[hash:8].[ext]'
    })
    .addAliases({
        '@fonts': path.resolve(__dirname, 'assets/fonts')
    })
    ;

module.exports = Encore.getWebpackConfig();
