/**
 * Утилита для загрузки и инициализации JavaScript-плагинов
 * 
 * Плагины позволяют кастомизировать поведение Vue-компонентов
 * без изменения основного кода компонентов.
 */

/**
 * Загружает и инициализирует плагины для компонента
 * 
 * @param {Array} pluginsData - Массив данных о плагинах из API
 * @param {Object} component - Vue-компонент, для которого загружаются плагины
 * @returns {Promise<Array>} Массив инициализированных плагинов
 */
export async function loadPlugins(pluginsData, component) {
    const plugins = [];
    
    // Нормализуем данные плагинов (может быть data.data или просто data)
    const normalizedPlugins = Array.isArray(pluginsData) 
        ? pluginsData 
        : (pluginsData?.data || []);
    
    for (const pluginData of normalizedPlugins) {
        try {
            if (!pluginData.js_module) {
                console.warn('Plugin without js_module was skipped:', pluginData);
                continue;
            }
            
            // Динамически импортируем JS-модуль плагина
            const pluginModule = await import(/* @vite-ignore */ pluginData.js_module);
            
            let pluginInstance = null;
            
            // Инициализируем плагин, если у него есть метод init
            if (pluginModule.default && typeof pluginModule.default.init === 'function') {
                pluginInstance = pluginModule.default.init({
                    component,
                    config: pluginData.config || {},
                });
            } else if (typeof pluginModule.default === 'function') {
                // Если плагин экспортирует функцию напрямую
                pluginInstance = pluginModule.default({
                    component,
                    config: pluginData.config || {},
                });
            } else if (pluginModule.default) {
                // Если плагин экспортирует объект напрямую
                pluginInstance = pluginModule.default;
            } else {
                console.warn(`Plugin ${pluginData.name} does not have an init method or a default export function`);
                continue;
            }
            
            if (pluginInstance) {
                plugins.push({
                    name: pluginData.name,
                    instance: pluginInstance,
                });
            }
        } catch (error) {
            console.error(`Failed to load plugin ${pluginData.name || 'unknown'}:`, error);
        }
    }
    
    return plugins;
}

/**
 * Уничтожает все загруженные плагины
 * 
 * @param {Array} plugins - Массив плагинов, возвращенный из loadPlugins
 */
export function destroyPlugins(plugins) {
    plugins.forEach(({ instance }) => {
        if (instance && typeof instance.destroy === 'function') {
            try {
                instance.destroy();
            } catch (error) {
                console.error('Failed to destroy plugin:', error);
            }
        }
    });
}
