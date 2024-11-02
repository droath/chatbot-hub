import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            hotFile: 'public/vendor/chatbot-hub/chatbot-hub.hot',
            buildDirectory: 'vendor/chatbot-hub',
            input: ['resources/css/index.css', 'resources/js/index.js'],
            refresh: true,
        }),
    ],
});
