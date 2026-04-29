import { fileURLToPath } from 'node:url';
import path from 'node:path';
import { defineConfig } from 'vite';
import { createExtensionConfig } from './tools/vite/createExtensionConfig.mjs';

const packageDirectory = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig(
    createExtensionConfig({
        packageDirectory,
        base: '/vendor/reno/cms/build/',
        entryDefinitions: [
            {
                type: 'file',
                name: 'admin',
                relativePath: 'admin.js',
            },
            {
                type: 'directory',
                relativeDirectory: 'api',
                extension: '.js',
            },
            {
                type: 'directory',
                relativeDirectory: 'components',
                extension: '.vue',
            },
            {
                type: 'directory',
                relativeDirectory: 'custom-components',
                extension: '.vue',
            },
            {
                type: 'directory',
                relativeDirectory: 'plugins',
                extension: '.js',
            },
            {
                type: 'directory',
                relativeDirectory: 'shared',
                extension: '.js',
            },
            {
                type: 'directory',
                relativeDirectory: 'utils',
                extension: '.js',
            },
        ],
        externalizeCmsRuntime: false,
    }),
);
