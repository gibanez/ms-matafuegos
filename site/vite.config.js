import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [vue(), tailwindcss()],
    base: '/',
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
        },
    },
    build: {
        outDir: '../public',
        emptyOutDir: false,
        assetsDir: 'site-assets',
    },
    server: {
        port: 5174,
        proxy: {
            '/api': {
                target: 'http://localhost:9981',
                changeOrigin: true,
            },
            '/media': {
                target: 'http://localhost:9981',
                changeOrigin: true,
            },
        },
    },
});
