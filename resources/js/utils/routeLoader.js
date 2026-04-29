import { apiFetch } from '../api/http';

/**
 * Утилита для загрузки и преобразования JavaScript-роутов
 */

/**
 * Загружает роуты с API
 * 
 * @param {string} apiPrefix - Префикс API
 * @param {string} adminPrefix - Префикс админки
 * @returns {Promise<Array>} Массив роутов
 */
export async function loadRoutes(apiPrefix, adminPrefix) {
    try {
        const response = await apiFetch(`${apiPrefix}/javascript-routes`);
        const data = await response.json();
        
        // Нормализуем данные (может быть data.data или просто data)
        const routesData = Array.isArray(data) ? data : (data?.data || []);
        
        return transformRoutes(routesData, adminPrefix);
    } catch (error) {
        console.error('Failed to load routes:', error);
        return [];
    }
}

/**
 * Преобразует роуты из API в формат Vue Router
 * 
 * @param {Array} routesData - Массив роутов из API
 * @param {string} adminPrefix - Префикс админки
 * @returns {Array} Массив роутов для Vue Router
 */
export function transformRoutes(routesData, adminPrefix) {
    const otherRoutes = [];
    
    // Преобразуем каждый роут
    for (const routeData of routesData) {
        const jsModule = routeData.js_module;
        const componentLoader = async () => {
            const componentModule = await import(/* @vite-ignore */ jsModule);

            return componentModule.default ?? componentModule;
        };
        
        // Создаем объект роута
        const route = {
            path: routeData.path,
            name: routeData.name,
            component: componentLoader,
            meta: {
                requiresAuth: true,
                ...routeData.meta,
            },
        };
        
        otherRoutes.push(route);
    }
    
    // Создаем Layout роут с children
    const layoutRoute = {
        path: `/${adminPrefix}`,
        component: () => import('../components/common/Layout.vue'),
        meta: { requiresAuth: true },
        children: otherRoutes,
    };
    
    return [layoutRoute];
}
