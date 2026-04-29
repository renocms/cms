import axios from 'axios';
import { getApiPrefix } from './index';

/**
 * Получить список разрешений
 */
export async function getPermissions() {
    const response = await axios.get(`${getApiPrefix()}/permissions`);
    return response.data;
}

/**
 * Получить разрешение по ID
 */
export async function getPermission(id) {
    const response = await axios.get(`${getApiPrefix()}/permissions/${id}`);
    return response.data;
}

