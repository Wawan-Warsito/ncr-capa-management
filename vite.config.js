import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    if (id.includes('node_modules')) {
                        const match = id.match(/node_modules\/(@[^/]+\/[^/]+|[^/]+)\//);
                        const pkg = match?.[1];
                        if (!pkg) return;

                        const reactPkgs = new Set([
                            'react',
                            'react-dom',
                            'react-router',
                            'react-router-dom',
                            'scheduler',
                            'lucide-react',
                        ]);

                        const chartsPkgs = new Set([
                            'recharts',
                            'd3-array',
                            'd3-color',
                            'd3-ease',
                            'd3-format',
                            'd3-interpolate',
                            'd3-path',
                            'd3-scale',
                            'd3-shape',
                            'd3-time',
                            'd3-time-format',
                        ]);

                        const utilsPkgs = new Set(['axios', 'qrcode']);

                        if (reactPkgs.has(pkg)) return 'vendor-react';
                        if (chartsPkgs.has(pkg)) return 'vendor-charts';
                        if (utilsPkgs.has(pkg)) return 'vendor-utils';
                    }
                },
            },
        },
        chunkSizeWarningLimit: 900,
    },
    test: {
        globals: true,
        environment: 'jsdom',
        setupFiles: './resources/js/tests/setup.js',
    },
    server: {
        proxy: {
            '/api': {
                target: 'http://localhost:8000',
                changeOrigin: true,
                secure: false,
            },
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
