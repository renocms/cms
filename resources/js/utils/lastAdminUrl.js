import { getAdminPrefix } from '../api';

const STORAGE_KEY = 'cms_last_admin_path';

/**
 * Сохраняет путь внутри зоны админки (префикс из конфига), исключая страницу входа.
 */
export function rememberLastAdminUrl(fullPath) {
    const prefix = getAdminPrefix();
    const adminRoot = `/${prefix}`;

    if (!fullPath.startsWith(`${adminRoot}/`) && fullPath !== adminRoot) {
        return;
    }

    if (fullPath === `${adminRoot}/login` || fullPath.startsWith(`${adminRoot}/login?`)) {
        return;
    }

    if (fullPath.includes('..')) {
        return;
    }

    try {
        localStorage.setItem(STORAGE_KEY, fullPath);
    } catch {
        // storage недоступен
    }
}

/**
 * Путь для перехода после входа: последний URL в админке или корень админки.
 */
export function getLastAdminUrlOrDefault() {
    const prefix = getAdminPrefix();
    const fallback = `/${prefix}`;

    let stored = null;
    try {
        stored = localStorage.getItem(STORAGE_KEY);
    } catch {
        return fallback;
    }

    if (!stored || !isSafeAdminPath(stored)) {
        return fallback;
    }

    return stored;
}

function isSafeAdminPath(path) {
    const prefix = getAdminPrefix();
    const adminRoot = `/${prefix}`;

    if (!path.startsWith(adminRoot)) {
        return false;
    }

    if (path === `${adminRoot}/login` || path.startsWith(`${adminRoot}/login?`)) {
        return false;
    }

    if (path.includes('..')) {
        return false;
    }

    return true;
}
