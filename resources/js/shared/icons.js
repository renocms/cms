const iconModules = import.meta.glob('../../../node_modules/lucide-vue-next/dist/esm/icons/*.js');

function getIconFileName(modulePath) {
    const parts = modulePath.split('/');
    const fileName = parts.at(-1) ?? '';

    return fileName.replace(/\.js$/u, '');
}

function normalizeIconKey(value) {
    return value
        .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
        .replace(/[^a-zA-Z0-9]+/g, '-')
        .replace(/-{2,}/g, '-')
        .replace(/^-|-$/g, '')
        .toLowerCase();
}

const resolvedIconModules = Object.entries(iconModules).reduce((icons, [modulePath, loader]) => {
    const fileName = getIconFileName(modulePath);
    const condensedName = fileName.replace(/-/g, '');

    icons[fileName] = loader;
    icons[condensedName] = loader;

    return icons;
}, {});

export async function loadIcon(name) {
    if (typeof name !== 'string' || name.trim() === '') {
        return null;
    }

    const normalizedName = normalizeIconKey(name);
    const condensedName = normalizedName.replace(/-/g, '');
    const loader = resolvedIconModules[normalizedName] || resolvedIconModules[condensedName];

    if (!loader) {
        return null;
    }

    const module = await loader();

    return module.default ?? null;
}

export const iconNames = Object.keys(iconModules)
    .map((modulePath) => getIconFileName(modulePath))
    .sort();
