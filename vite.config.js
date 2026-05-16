import {
    defineConfig,
    loadEnv,
} from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devServerUrl = env.VITE_DEV_SERVER_URL?.replace(/\/$/, '');
    const devServerOrigin = devServerUrl ? new URL(devServerUrl) : null;

    return {
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/storefront.css',
                'resources/js/app.js',
                'resources/js/storefront.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        ...(devServerOrigin && {
            origin: devServerUrl,
            hmr: {
                host: devServerOrigin.hostname,
                protocol: devServerOrigin.protocol === 'https:' ? 'wss' : 'ws',
                clientPort: devServerOrigin.port
                    ? Number(devServerOrigin.port)
                    : devServerOrigin.protocol === 'https:'
                      ? 443
                      : 80,
            },
        }),
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    };
});
