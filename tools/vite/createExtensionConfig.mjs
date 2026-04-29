import fs from 'node:fs';
import path from 'node:path';
import vue from '@vitejs/plugin-vue';
import cssInjectedByJsPlugin from 'vite-plugin-css-injected-by-js';

function collectEntries(baseDirectory, relativeDirectory, extension) {
    const targetDirectory = path.resolve(baseDirectory, relativeDirectory);

    if (!fs.existsSync(targetDirectory)) {
        return {};
    }

    const entries = {};

    const walk = (currentDirectory) => {
        for (const entry of fs.readdirSync(currentDirectory, { withFileTypes: true })) {
            const absolutePath = path.resolve(currentDirectory, entry.name);

            if (entry.isDirectory()) {
                walk(absolutePath);
                continue;
            }

            if (!entry.isFile() || !absolutePath.endsWith(extension)) {
                continue;
            }

            const relativePath = path.relative(baseDirectory, absolutePath);
            const outputName = relativePath.replace(new RegExp(`${extension}$`, 'u'), '');

            entries[outputName] = absolutePath;
        }
    };

    walk(targetDirectory);

    return entries;
}

function mapExternalModulePath(moduleId) {
    if (moduleId === 'vue') {
        return '/vendor/reno/cms/build/shared/vue.js';
    }

    if (moduleId === 'axios') {
        return '/vendor/reno/cms/build/shared/axios.js';
    }

    if (moduleId === '@reno-cms/api') {
        return '/vendor/reno/cms/build/api/index.js';
    }

    if (moduleId.startsWith('@reno-cms/')) {
        return `/vendor/reno/cms/build/${moduleId.replace('@reno-cms/', '')}.js`;
    }

    return moduleId;
}

function isExternalCmsModule(moduleId) {
    return moduleId === 'vue'
        || moduleId === 'axios'
        || moduleId === '@reno-cms/api'
        || moduleId.startsWith('@reno-cms/');
}

function resolveInputEntries(packageDirectory, entryDefinitions) {
    const resourcesDirectory = path.resolve(packageDirectory, 'resources/js');
    const inputEntries = {};

    for (const entryDefinition of entryDefinitions) {
        if (entryDefinition.type === 'file') {
            inputEntries[entryDefinition.name] = path.resolve(resourcesDirectory, entryDefinition.relativePath);
            continue;
        }

        if (entryDefinition.type === 'directory') {
            Object.assign(
                inputEntries,
                collectEntries(resourcesDirectory, entryDefinition.relativeDirectory, entryDefinition.extension),
            );
        }
    }

    return inputEntries;
}

/**
 * Собирает зависимости из node_modules в два чанка, чтобы уменьшить число мелких файлов в chunks/.
 * moduleId в Rollup нормализуется с прямыми слэшами.
 */
function manualChunks(moduleId) {
    if (!moduleId.includes('node_modules')) {
        return undefined;
    }

    const id = moduleId.replace(/\\/g, '/');

    if (
        id.includes('/vue/')
        || id.includes('/@vue/')
        || id.includes('/vue-router/')
    ) {
        return 'vendor-vue';
    }

    return 'vendor';
}

export function createExtensionConfig({
    packageDirectory,
    base,
    entryDefinitions,
    externalizeCmsRuntime = false,
}) {
    const config = {
        publicDir: false,
        plugins: [
            vue(),
            cssInjectedByJsPlugin({
                relativeCSSInjection: true,
            }),
        ],
        base,
        build: {
            cssCodeSplit: true,
            manifest: 'manifest.json',
            outDir: path.resolve(packageDirectory, 'public/build'),
            emptyOutDir: true,
            rollupOptions: {
                preserveEntrySignatures: 'strict',
                input: resolveInputEntries(packageDirectory, entryDefinitions),
                output: {
                    entryFileNames: '[name].js',
                    chunkFileNames: 'chunks/[name]-[hash].js',
                    assetFileNames: 'assets/[name]-[hash][extname]',
                    manualChunks,
                },
            },
        },
    };

    if (externalizeCmsRuntime) {
        config.build.rollupOptions.external = isExternalCmsModule;
        config.build.rollupOptions.output.paths = mapExternalModulePath;
    }

    return config;
}
