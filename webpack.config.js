const Encore = require('@symfony/webpack-encore');
const path = require('path'); // 👈 you forgot this

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // Rendre jQuery disponible globalement
    .addPlugin(new (require('webpack')).ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery'
    }))
    .addExternals({
        jquery: 'jQuery',
        $: 'jQuery'
    })

    // main entry for your JS
    .addEntry('vitrine', './assets/vitrine/app.js')

    .addEntry('backoffice', './assets/backoffice/app.js')

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
