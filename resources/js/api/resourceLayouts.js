import axios from 'axios';
import { getApiPrefix } from './index';

let resourceLayoutsCache = null;
let resourceLayoutsRequest = null;

/**
 * Получить список макетов ресурсов
 */
export async function getResourceLayouts() {
    if (resourceLayoutsCache !== null) {
        return resourceLayoutsCache;
    }

    if (resourceLayoutsRequest) {
        return resourceLayoutsRequest;
    }

    resourceLayoutsRequest = axios
        .get(`${getApiPrefix()}/resource-layouts`)
        .then((response) => {
            resourceLayoutsCache = response.data;
            return resourceLayoutsCache;
        })
        .finally(() => {
            resourceLayoutsRequest = null;
        });

    return resourceLayoutsRequest;
}
