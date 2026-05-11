/**
 * Получение префикса админки из конфига
 */
export function getAdminPrefix() {
    return window.CMS_CONFIG?.adminPrefix || 'admin';
}

/**
 * Получение URL сайта из Laravel-конфига
 */
export function getAppUrl() {
    return window.CMS_CONFIG?.appUrl || '/';
}

/**
 * Получение префикса API
 */
export function getApiPrefix() {
    return `/${getAdminPrefix()}/api`;
}

// Экспорт всех API функций
export * from './auth';
export * from './dashboard';
export * from './contexts';
export * from './resources';
export * from './users';
export * from './roles';
export * from './permissions';
export * from './settings';
export * from './resourceLayouts';
export * from './resourceCatalog';
export * from './resourceFields';
export * from './media';
export * from './cache';
export * from './routes';

