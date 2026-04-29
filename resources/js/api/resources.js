import axios from 'axios';
import { getApiPrefix } from './index';

let resourceEditPluginsCache = null;
let resourceEditPluginsRequest = null;

/**
 * Получение дерева ресурсов
 */
export async function getResourcesTree(contextId = null, expandedIds = []) {
    const params = contextId ? { context_id: contextId } : {};
    if (expandedIds && Array.isArray(expandedIds) && expandedIds.length > 0) {
        params.ids = expandedIds;
    }
    const response = await axios.get(`${getApiPrefix()}/resources`, { params });
    return response.data;
}

/**
 * Инициализация нового ресурса (получение данных из родителя или корня контекста)
 *
 * @param {number|null} parentId
 * @param {number|null} contextId — для ресурса в корне без родителя
 */
export async function makeDraftResource(parentId = null, contextId = null) {
    const params = {};
    if (parentId != null) {
        params.parent_id = parentId;
    }
    if (contextId != null) {
        params.context_id = contextId;
    }
    const response = await axios.get(`${getApiPrefix()}/resources/make-draft`, { params });
    return response.data;
}

/**
 * Получить ресурс по ID
 */
export async function getResource(id) {
    const response = await axios.get(`${getApiPrefix()}/resources/${id}`);
    return response.data;
}

/**
 * Обновить ресурс
 */
export async function updateResource(id, resourceData) {
    const response = await axios.put(`${getApiPrefix()}/resources/${id}`, resourceData);
    return response.data;
}

/**
 * Создать ресурс
 */
export async function createResource(resourceData) {
    const response = await axios.post(`${getApiPrefix()}/resources`, resourceData);
    return response.data;
}

/**
 * Переместить ресурс
 */
export async function moveResource(id, parentId, sortOrder) {
    const response = await axios.post(`${getApiPrefix()}/resources/${id}/move`, {
        parent_id: parentId,
        sort_order: sortOrder,
    });
    return response.data;
}

/**
 * Получить дочерние ресурсы конкретного ресурса
 */
export async function getResourceChildren(resourceId) {
    const response = await axios.get(`${getApiPrefix()}/resources/${resourceId}/children`);
    return response.data;
}

/**
 * Удалить ресурс
 */
export async function deleteResource(id) {
    const response = await axios.delete(`${getApiPrefix()}/resources/${id}`);
    return response.data;
}

/**
 * Получить список плагинов для страницы редактирования ресурса
 */
export async function getResourceEditPlugins() {
    if (resourceEditPluginsCache !== null) {
        return resourceEditPluginsCache;
    }

    if (resourceEditPluginsRequest) {
        return resourceEditPluginsRequest;
    }

    resourceEditPluginsRequest = axios
        .get(`${getApiPrefix()}/resources/plugins`)
        .then((response) => {
            resourceEditPluginsCache = response.data;
            return resourceEditPluginsCache;
        })
        .finally(() => {
            resourceEditPluginsRequest = null;
        });

    return resourceEditPluginsRequest;
}

