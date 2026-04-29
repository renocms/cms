import axios from 'axios';
import { getApiPrefix } from './index';

/**
 * Получить список пользователей
 */
export async function getUsers() {
    const response = await axios.get(`${getApiPrefix()}/users`);
    return response.data;
}

/**
 * Получить пользователя по ID
 */
export async function getUser(id) {
    const response = await axios.get(`${getApiPrefix()}/users/${id}`);
    return response.data;
}

/**
 * Создать пользователя
 */
export async function createUser(data) {
    const response = await axios.post(`${getApiPrefix()}/users`, data);
    return response.data;
}

/**
 * Обновить пользователя
 */
export async function updateUser(id, data) {
    const response = await axios.put(`${getApiPrefix()}/users/${id}`, data);
    return response.data;
}

/**
 * Удалить пользователя
 */
export async function deleteUser(id) {
    const response = await axios.delete(`${getApiPrefix()}/users/${id}`);
    return response.data;
}

