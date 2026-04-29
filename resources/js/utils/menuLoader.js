import { apiFetch } from '../api/http';

/**
 * Утилита для загрузки и обработки пунктов меню
 */

/**
 * Загружает элементы меню с API
 * 
 * @param {string} apiPrefix - Префикс API
 * @returns {Promise<Array>} Массив элементов меню
 */
export async function loadMenuItems(apiPrefix) {
    try {
        const response = await apiFetch(`${apiPrefix}/top-menu-items`);
        const data = await response.json();
        
        // Нормализуем данные (может быть data.data или просто data)
        const menuItemsData = Array.isArray(data) ? data : (data?.data || []);
        
        // Сортируем и фильтруем элементы меню
        const sorted = sortMenuItems(menuItemsData);
        const filtered = filterVisibleItems(sorted);
        
        return filtered;
    } catch (error) {
        console.error('Failed to load menu items:', error);
        return [];
    }
}

/**
 * Сортирует пункты меню по полю order
 * 
 * @param {Array} items - Массив элементов меню
 * @returns {Array} Отсортированный массив
 */
export function sortMenuItems(items) {
    // Создаем копию массива для сортировки
    const sorted = [...items].sort((a, b) => {
        const orderA = a.order || 0;
        const orderB = b.order || 0;
        return orderA - orderB;
    });
    
    // Рекурсивно сортируем вложенные элементы
    for (const item of sorted) {
        if (item.children && Array.isArray(item.children) && item.children.length > 0) {
            item.children = sortMenuItems(item.children);
        }
    }
    
    return sorted;
}

/**
 * Фильтрует видимые пункты меню
 * 
 * @param {Array} items - Массив элементов меню
 * @returns {Array} Отфильтрованный массив
 */
export function filterVisibleItems(items) {
    const filtered = [];
    
    for (const item of items) {
        // Пропускаем невидимые элементы
        if (item.visible === false) {
            continue;
        }
        
        // Создаем копию элемента
        const filteredItem = { ...item };
        
        // Рекурсивно фильтруем вложенные элементы
        if (item.children && Array.isArray(item.children) && item.children.length > 0) {
            filteredItem.children = filterVisibleItems(item.children);
        }
        
        filtered.push(filteredItem);
    }
    
    return filtered;
}
