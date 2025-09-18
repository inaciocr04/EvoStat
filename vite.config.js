import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
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
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // Séparer les bibliothèques lourdes
                    'vendor': ['vue', '@inertiajs/vue3'],
                    'charts': ['apexcharts', 'chart.js', 'vue3-apexcharts'],
                    'utils': ['gsap', 'dayjs', 'vuedraggable']
                }
            }
        },
        // Optimisations de performance
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true,
            }
        },
        // Augmenter la limite de taille des chunks
        chunkSizeWarningLimit: 1000,
    },
    // Optimisations de développement
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: '0.0.0.0',
            port: 5173,
            overlay: false
        },
        cors: true
    }
});
