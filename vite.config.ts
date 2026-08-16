import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                // Split the heaviest, least-often-changed vendor libraries
                // into their own chunks so a change to app code doesn't
                // bust the browser cache for all of them at once, and so
                // pages that don't use charts/calendar/dnd don't pay for
                // them upfront. Vite 8's Rolldown bundler needs this as a
                // function rather than the classic Rollup object form.
                manualChunks(id: string) {
                    if (!id.includes('node_modules')) {
                        return;
                    }

                    if (
                        id.includes('/react/') ||
                        id.includes('/react-dom/')
                    ) {
                        return 'vendor-react';
                    }

                    if (id.includes('/recharts/')) {
                        return 'vendor-charts';
                    }

                    if (id.includes('/react-big-calendar/')) {
                        return 'vendor-calendar';
                    }

                    if (id.includes('/react-dnd')) {
                        return 'vendor-dnd';
                    }

                    if (id.includes('/@tanstack/')) {
                        return 'vendor-query';
                    }
                },
            },
        },
    },
});
