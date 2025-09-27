import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/music-manager.js'
            ],
            refresh: true,
        }),
    ],
    optimizeDeps: {
        include: ['alpinejs', 'axios']
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});