import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                // System Arabic-friendly stack: no web-font downloads.
                sans: ['system-ui', '-apple-system', 'Segoe UI', 'Tahoma', 'Arial', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Warm design tokens. Primary = burnt orange, neutral = warm stone.
                primary: colors.orange,
                neutral: colors.stone,
            },
        },
    },

    plugins: [forms],
};
