import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig(({ command }) => {
    const isBuild = command === 'build';

    return {
        base: isBuild ? '/wp-content/themes/tailpress/dist/' : '/',
        server: {
            port: 3000,
            cors: true,
            origin: 'http://mpma-poc.local:8080',
        },
        build: {
            manifest: true,
            outDir: 'dist',
            rollupOptions: {
                input: {
                    app: 'resources/js/app.js',
                    editor: 'resources/js/editor.js',
                    'homepage-blocks': 'resources/js/homepage-blocks.js',
                    'app-css': 'resources/css/app.css',
                    'editor-style': 'resources/css/editor-style.css'
                },
                external: [
                    '@wordpress/blocks',
                    '@wordpress/block-editor',
                    '@wordpress/components',
                    '@wordpress/element',
                    '@wordpress/i18n',
                    'react'
                ],
                output: {
                    globals: {
                        '@wordpress/blocks': 'wp.blocks',
                        '@wordpress/block-editor': 'wp.blockEditor',
                        '@wordpress/components': 'wp.components',
                        '@wordpress/element': 'wp.element',
                        '@wordpress/i18n': 'wp.i18n',
                        'react': 'React'
                    }
                }
            },
        },
        plugins: [
            tailwindcss(),
        ],
    }
});
