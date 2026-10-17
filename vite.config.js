import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import path from 'node:path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/frontend.css',
                'resources/js/frontend/app.tsx',
                'resources/css/dashboard.css',
                'resources/js/dashboard/app.jsx',
            ],
            // Built separately with `vite build --ssr` into bootstrap/ssr/.
            ssr: 'resources/js/frontend/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@frontend': path.resolve(import.meta.dirname, 'resources/js/frontend'),
            '@dashboard': path.resolve(import.meta.dirname, 'resources/js/dashboard'),
        },
    },
});
