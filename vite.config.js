import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/builder/canvas.js',
            ],
            refresh: true,
            fonts: [
                bunny('Akshar', {
                    weights: [300, 400, 500, 600],
                }),
                bunny('Outfit', {
                    weights: [300, 400, 500, 600],
                }),
                bunny('Roboto', {
                    weights: [300, 400, 500, 700],
                }),
                bunny('Inter', {
                    weights: [300, 400, 500, 700],
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
