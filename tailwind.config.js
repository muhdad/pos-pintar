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
                    // Deep maroon for sidebar, top bar, dark surfaces, and high-emphasis headers
                    maroon: {
                        950: '#38040A',
                        900: '#4A0710',
                        800: '#5B0A14', // Primary brand maroon
                        700: '#6E0E1B',
                        600: '#7A0F1C', // Hover / lighter dark surface
                        500: '#941525',
                        100: '#FBE8EA',
                        50:  '#FDF2F4',
                    },
                    // Vibrant orange for primary CTAs, active indicators, and checkout
                    orange: {
                        900: '#7C2D12',
                        800: '#9A3412',
                        700: '#C2410C', // Accessible text on white (AA compliant: >4.5:1)
                        600: '#EA580C', // Primary CTA button fill
                        500: '#F97316',
                        400: '#FB923C',
                        100: '#FFEDD5',
                        50:  '#FFF7ED',
                    },
                    // Golden yellow reserved strictly for small emphasis, totals, and badges
                    gold: {
                        800: '#92400E',
                        700: '#B45309', // Accessible text on light backgrounds
                        600: '#D97706',
                        500: '#FFB800', // Highlight total on dark surfaces
                        100: '#FEF3C7',
                        50:  '#FFFBEB',
                    },
                    // Neutral architectural palette
                    canvas:  '#FAF6F0', // Warm off-white page background
                    surface: '#FFFFFF', // Crisp card and table background
                    border:  '#E7E0D8', // 1px low-contrast structural border
                    'border-subtle': '#F0EBE4',
                    text:    '#292524', // stone-800 primary copy
                    muted:   '#78716C', // stone-500 secondary labels
                },
                // Semantic status colors (subtle, non-neon)
                status: {
                    success: { bg: '#F0FDF4', text: '#15803D', border: '#BBF7D0' },
                    danger:  { bg: '#FEF2F2', text: '#B91C1C', border: '#FECACA' },
                    warning: { bg: '#FFFBEB', text: '#B45309', border: '#FDE68A' },
                    info:    { bg: '#EFF6FF', text: '#1D4ED8', border: '#BFDBFE' },
                }
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', '"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
            },
            // Restrained radius scale: 6px controls, 10px cards/panels, 14px modals
            borderRadius: {
                DEFAULT: '6px',
                sm: '4px',
                md: '6px',
                lg: '10px',
                xl: '14px',
            },
            // Subtle, functional elevation only for modals and popups. Surfaces use 1px borders.
            boxShadow: {
                subtle: '0 1px 2px 0 rgba(41, 37, 36, 0.04)',
                dropdown: '0 4px 12px -2px rgba(41, 37, 36, 0.08)',
                modal: '0 12px 32px -4px rgba(41, 37, 36, 0.16)',
            },
        },
    },

    plugins: [forms],
};
