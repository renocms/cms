import axios from 'axios';
import { getApiPrefix } from './index';

/**
 * Получение списка контекстов
 */
export async function getContexts() {
    const response = await axios.get(`${getApiPrefix()}/contexts`);
    return response.data;
}

