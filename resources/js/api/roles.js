import axios from 'axios';
import { getApiPrefix } from './index';

/**
 * Получить список ролей
 */
export async function getRoles() {
    const response = await axios.get(`${getApiPrefix()}/roles`);
    return response.data;
}

/**
 * Получить роль по ID
 */
export async function getRole(id) {
    const response = await axios.get(`${getApiPrefix()}/roles/${id}`);
    return response.data;
}

/**
 * Создать роль
 */
export async function createRole(data) {
    const response = await axios.post(`${getApiPrefix()}/roles`, data);
    return response.data;
}

/**
 * Обновить роль
 */
export async function updateRole(id, data) {
    const response = await axios.put(`${getApiPrefix()}/roles/${id}`, data);
    return response.data;
}

/**
 * Удалить роль
 */
export async function deleteRole(id) {
    const response = await axios.delete(`${getApiPrefix()}/roles/${id}`);
    return response.data;
}

