import axios from 'axios';
import { getApiPrefix } from './index';

/**
 * Получить список медиа-файлов
 */
export async function getMediaList(params = {}) {
    const response = await axios.get(`${getApiPrefix()}/media`, { params });
    return response.data;
}

/**
 * Получить медиа-файл по ID
 */
export async function getMedia(id) {
    const response = await axios.get(`${getApiPrefix()}/media/${id}`);
    return response.data;
}

/**
 * Получить миниатюры для списка медиа-файлов
 */
export async function getMediaThumbnails(ids = [], params = {}) {
    const payload = {
        ids,
        width: params.width ?? 80,
        height: params.height ?? 80,
        options: params.options ?? 'zc=1',
    };
    const response = await axios.post(`${getApiPrefix()}/media/thumbnails`, payload);
    return response.data;
}

/**
 * Загрузить новый медиа-файл
 */
export async function uploadMedia(file, altText = null, description = null) {
    const formData = new FormData();
    formData.append('file', file);
    if (altText) {
        formData.append('alt_text', altText);
    }
    if (description) {
        formData.append('description', description);
    }
    
    const response = await axios.post(`${getApiPrefix()}/media`, formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    });
    return response.data;
}

/**
 * Обновить медиа-файл
 */
export async function updateMedia(id, data) {
    const response = await axios.put(`${getApiPrefix()}/media/${id}`, data);
    return response.data;
}

/**
 * Удалить медиа-файл
 */
export async function deleteMedia(id) {
    const response = await axios.delete(`${getApiPrefix()}/media/${id}`);
    return response.data;
}
