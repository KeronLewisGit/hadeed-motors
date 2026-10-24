import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

// Fonts are downloaded at build time and served from /build, so visitors never hit a font CDN.
// Two families only: Unbounded, a wide geometric display face that echoes the chunky HM
// lettering on the showroom, used sparingly for headings and gauge numerals; Hanken Grotesk
// for everything people read.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Unbounded', {
                    alias: 'display',
                    variable: '--ff-display',
                    weights: [500, 700],
                    subsets: ['latin'],
                    preload: [{ weight: 700 }],
                    fallbacks: ['ui-sans-serif', 'system-ui', 'sans-serif'],
                }),
                bunny('Hanken Grotesk', {
                    alias: 'sans',
                    variable: '--ff-sans',
                    weights: [400, 500, 600, 700],
                    styles: ['normal'],
                    subsets: ['latin'],
                    preload: [{ weight: 400 }, { weight: 600 }],
                    fallbacks: ['ui-sans-serif', 'system-ui', 'sans-serif'],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: { watch: { ignored: ['**/storage/framework/views/**'] } },
});
