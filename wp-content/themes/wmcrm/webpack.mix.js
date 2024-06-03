let mix = require('laravel-mix');
mix.js('resources/js/app.js', 'assets/js')
    .autoload({
        jquery: ['$', 'window.jQuery']
    })
    .copy('node_modules/selectric/public/jquery.selectric.min.js', 'assets/js');
mix.sass('resources/sass/app.scss', 'assets/css')
    .copy('node_modules/selectric/public/selectric.css', 'assets/css');
mix.webpackConfig({
    resolve: {
        alias: {
            'jquery-ui': 'jquery-ui/ui/widgets',
        }
    }
});