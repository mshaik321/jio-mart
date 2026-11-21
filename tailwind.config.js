import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                // Brand blue: header, links, focus rings.
                brand: {
                    50: '#eef4ff', 100: '#dce7ff', 200: '#b9cfff', 300: '#8cafff', 400: '#5b86f2',
                    500: '#3160d8', 600: '#2149b5', 700: '#183b94', 800: '#122d74', 900: '#0c1f52',
                },
                // Fresh green: buy buttons, prices, success states.
                fresh: {
                    50: '#f1faec', 100: '#ddf2d2', 200: '#bde5a9', 300: '#94d175', 400: '#69b948',
                    500: '#47a22b', 600: '#2a7a1b', 700: '#216017', 800: '#1a4d13', 900: '#174210',
                },
                // Sunny yellow: highlights, active pills, promo accents.
                sun: {
                    50: '#fffbe3', 100: '#fff4b3', 200: '#ffec86', 300: '#ffe45c', 400: '#ffd62e',
                    500: '#f5c400', 600: '#d9a800', 700: '#a98100', 800: '#7d5f00', 900: '#5a4400',
                },
            },
            fontFamily: {
                sans: ['Figtree', 'Noto Sans Telugu', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
