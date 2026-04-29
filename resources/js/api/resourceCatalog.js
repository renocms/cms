import axios from 'axios';
import { getApiPrefix } from './index';

export async function getResourceCatalog(catalogId, resourceId) {
    const response = await axios.get(`${getApiPrefix()}/resources/catalog`, {
        params: {
            catalog_id: catalogId,
            resource_id: resourceId,
        },
    });

    return response.data;
}

export async function getResourceCatalogResources(catalogId, resourceId, page = 1, perPage = 20) {
    const response = await axios.get(`${getApiPrefix()}/resource/catalog/resources`, {
        params: {
            catalog_id: catalogId,
            resource_id: resourceId,
            page,
            per_page: perPage,
        },
    });

    return response.data;
}
