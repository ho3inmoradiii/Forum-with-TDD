const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss');
const path = require('path');

mix.js('resources/js/app.js', 'public/js')
    .vue() // Add Vue support
    .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
        require('tailwindcss'),
    ])
    .webpackConfig({
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
            },
        },
    });
