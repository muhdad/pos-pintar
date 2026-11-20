import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    maroon: {
                        950: '#38040A',
                        900: '#4D0811',
                        800: '#5B0A14', // Primary deep maroon for sidebar & dark surfaces
                        700: '#6B0D19',
                        600: '#7A0F1C',
                        500: '#941525',
                        400: '#B82234',
                        100: '#FBE8EA',
                        50:  '#FDF2F4',
                    },
                    orange: {
                        700: '#C2410C',
                        600: '#EA580C',
                        500: '#F97316', // Primary Accent / CTA button
                        400: '#FB923C',
                        300: '#FDBA74',
                        100: '#FFEDD5',
                        50:  '#FFF7ED',
                    },
                    gold: {
                        700: '#B45309',
                        600: '#D97706',
                        500: '#FFB800', // Highlight / Badges / Totals
                        400: '#FBBF24',
                        200: '#FDE68A',
                        100: '#FEF3C7',
                        50:  '#FFFBEB',
                    },
                    canvas: '#FFF8F0', // Neutral warm off-white
                    card: '#FFFFFF',
                    border: '#EFE7DE',
                    text: '#2D1E1B',
                    muted: '#786B66',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', '"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
            },
            boxShadow: {
                'brand-sm': '0 2px 8px -1px rgba(91, 10, 20, 0.05), 0 1px 4px -1px rgba(91, 10, 20, 0.03)',
                'brand-md': '0 8px 24px -3px rgba(91, 10, 20, 0.07), 0 4px 12px -2px rgba(91, 10, 20, 0.03)',
                'brand-lg': '0 16px 36px -4px rgba(91, 10, 20, 0.12), 0 8px 16px -4px rgba(91, 10, 20, 0.05)',
                'brand-orange': '0 8px 24px -4px rgba(249, 115, 22, 0.35)',
                'brand-maroon': '0 8px 24px -4px rgba(91, 10, 20, 0.35)',
            },
            borderRadius: {
                '2.5xl': '1.25rem',
                '3.5xl': '1.75rem',
            },
        },
    },

    plugins: [forms],
};
