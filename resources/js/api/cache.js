import axios from 'axios';
import { getApiPrefix } from './index';

/**
 * Сбросить только хранилище кэша CMS (store «cms»).
 */
export async function flushCmsCache() {
    const response = await axios.post(`${getApiPrefix()}/cache/cms`);
    return response.data;
}

/**
 * Полный сброс: store «cms» и artisan cache:clear.
 */
export async function flushFullCache() {
    const response = await axios.post(`${getApiPrefix()}/cache/full`);
    return response.data;
}
