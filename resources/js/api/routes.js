import { getApiPrefix } from './index';
import axios from 'axios';

/**
 * Получить JavaScript-роуты
 * 
 * @returns {Promise<Object>}
 */
export async function getJavascriptRoutes() {
    const apiPrefix = getApiPrefix();
    const response = await axios.get(`${apiPrefix}/javascript-routes`);
    return response.data;
}

/**
 * Получить пункты меню в шапке
 * 
 * @returns {Promise<Object>}
 */
export async function getTopMenuItems() {
    const apiPrefix = getApiPrefix();
    const response = await axios.get(`${apiPrefix}/top-menu-items`);
    return response.data;
}
