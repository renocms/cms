import axios from 'axios';
import { getApiPrefix } from './index';

/**
 * Получить список типов полей (ресурсных полей)
 */
export async function getResourceFields() {
    const response = await axios.get(`${getApiPrefix()}/resource-fields`);
    return response.data;
}

/**
 * Получить тип поля по ID
 */
export async function getResourceField(id) {
    const response = await axios.get(`${getApiPrefix()}/resource-fields/${id}`);
    return response.data;
}

/**
 * Создать тип поля
 */
export async function createResourceField(data) {
    const response = await axios.post(`${getApiPrefix()}/resource-fields`, data);
    return response.data;
}

/**
 * Обновить тип поля
 */
export async function updateResourceField(id, data) {
    const response = await axios.put(`${getApiPrefix()}/resource-fields/${id}`, data);
    return response.data;
}

/**
 * Удалить тип поля
 */
export async function deleteResourceField(id) {
    const response = await axios.delete(`${getApiPrefix()}/resource-fields/${id}`);
    return response.data;
}

/**
 * Получить список доступных кастомных типов полей
 */
export async function getAvailableFieldTypes() {
    const response = await axios.get(`${getApiPrefix()}/resource-fields/available-types`);
    return response.data;
}

