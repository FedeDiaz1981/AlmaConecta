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
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                // Blues
                blueDeep: "var(--ac-blue-deep)",
                blueInk: "var(--ac-blue-ink)",
                blueNight: "var(--ac-blue-night)",
                blueMid: "var(--ac-blue-mid)",
                blueBright: "var(--ac-blue-bright)",

                // Golds
                gold: "var(--ac-gold)",
                goldLight: "var(--ac-gold-light)",
                goldStrong: "var(--ac-gold-strong)",
                goldDark: "var(--ac-gold-dark)",
                amberDeep: "var(--ac-amber-deep)",

                // Neutrals
                silver: "var(--ac-silver)",
                silverLight: "var(--ac-silver-light)",
                grayMid: "var(--ac-gray-mid)",
                grayDark: "var(--ac-gray-dark)",
                carbon: "var(--ac-carbon)",

                // Accent
                accentMint: "var(--ac-accent-mint)",
            },

            borderRadius: {
                sm: "var(--radius-sm)",
                md: "var(--radius-md)",
                lg: "var(--radius-lg)",
            },

            boxShadow: {
                soft: "var(--shadow-soft)",
                strong: "var(--shadow-strong)",
            },

            spacing: {
                1: "var(--space-1)",
                2: "var(--space-2)",
                3: "var(--space-3)",
                4: "var(--space-4)",
                6: "var(--space-6)",
                8: "var(--space-8)",
            },
        },
    },

    plugins: [forms],
};
