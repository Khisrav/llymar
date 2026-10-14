import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => ({
    esbuild: mode === 'production'
        ? { pure: ['console.log', 'console.info', 'console.debug'], drop: ['debugger'] }
        : {},
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/filament-custom.css'
            ],
            ssr: 'resources/js/ssr.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    base: '/build/',
}));
