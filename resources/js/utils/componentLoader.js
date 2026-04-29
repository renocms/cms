import { defineAsyncComponent } from 'vue';

// Общий кэш для компонентов
const componentCache = new Map();

/**
 * Динамическая загрузка Vue-компонента
 *
 * @param {string} moduleUrl - URL JS-модуля компонента от корня сайта
 * @param {Object} options - Опции для настройки поведения загрузки
 * @param {string} options.errorMessage - Сообщение об ошибке (должно быть переведено через $t() в компоненте)
 * @param {string} options.loadingMessage - Сообщение о загрузке (должно быть переведено через $t() в компоненте)
 * @param {number} options.delay - Задержка перед показом индикатора загрузки в мс (по умолчанию: 200)
 * @param {number} options.timeout - Таймаут загрузки в мс (по умолчанию: 5000)
 * @returns {Promise|null} Асинхронный компонент или null, если путь не указан
 */
export function loadComponent(moduleUrl, options = {}) {
    if (!moduleUrl) {
        return null;
    }
    
    // Если компонент уже загружен, возвращаем его из кэша
    if (componentCache.has(moduleUrl)) {
        return componentCache.get(moduleUrl);
    }
    
    const {
        errorMessage,
        loadingMessage,
        delay = 200,
        timeout = 5000,
    } = options;
    
    // Значения по умолчанию для сообщений (будут использоваться только если не переданы)
    const defaultErrorMessage = errorMessage || 'Component not found';
    const defaultLoadingMessage = loadingMessage || 'Loading component...';
    
    // Создаем новый асинхронный компонент
    const component = defineAsyncComponent({
        loader: async () => {
            const componentModule = await import(/* @vite-ignore */ moduleUrl);

            return componentModule.default ?? componentModule;
        },
        errorComponent: {
            template: `<div class="error-message">${defaultErrorMessage}: {{ moduleUrl }}</div>`,
            data() {
                return { moduleUrl };
            },
        },
        loadingComponent: {
            template: `<div class="loading-message">${defaultLoadingMessage}</div>`,
        },
        delay,
        timeout,
        onError(error, retry, fail, attempts) {
            console.error(`Failed to load component "${moduleUrl}":`, error);
            if (attempts < 3) {
                retry();
            } else {
                fail();
            }
        },
    });
    
    // Сохраняем в кэш
    componentCache.set(moduleUrl, component);
    
    return component;
}

