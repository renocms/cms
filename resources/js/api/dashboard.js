import axios from 'axios';
import { getApiPrefix } from './index';

/**
 * Получение данных дашборда
 */
export async function getDashboard() {
    const response = await axios.get(`${getApiPrefix()}/dashboard`);
    return response.data;
}

