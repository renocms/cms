import axios from 'axios';
import { getApiPrefix } from './index';

/**
 * Получить все настройки для контекста
 */
export async function getSettings(contextId) {
    const response = await axios.get(`${getApiPrefix()}/settings`, {
        params: { context_id: contextId },
    });
    return response.data;
}

/**
 * Обновить несколько настроек
 */
export async function updateSettings(contextId, settings) {
    const response = await axios.put(`${getApiPrefix()}/settings`, {
        context_id: contextId,
        settings,
    });
    return response.data;
}

/**
 * Удалить настройку
 */
export async function deleteSetting(id) {
    const response = await axios.delete(`${getApiPrefix()}/settings/${id}`);
    return response.data;
}

