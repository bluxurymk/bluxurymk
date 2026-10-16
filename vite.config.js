import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/filament/admin/theme.css'],
            refresh: true,
            fonts: [
                bunny('Cormorant Garamond', {
                    variable: '--font-cormorant',
                    weights: [400, 500, 600, 700],
                    subsets: ['latin', 'cyrillic'],
                    preload: [{ weight: 600 }],
                    fallbacks: ['Georgia', 'serif'],
                }),
                bunny('Montserrat', {
                    variable: '--font-montserrat',
                    weights: [300, 400, 500, 600],
                    subsets: ['latin', 'cyrillic'],
                    preload: [{ weight: 400 }],
                    fallbacks: ['system-ui', 'sans-serif'],
                }),
                bunny('Marck Script', {
                    variable: '--font-marck',
                    weights: [400],
                    subsets: ['latin', 'cyrillic'],
                    preload: false,
                    fallbacks: ['cursive'],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
