import axios from 'axios';
import { authStore } from '../store/auth';
import { getApiPrefix } from './index';

/**
 * Авторизация пользователя
 */
export async function login(email, password) {
    const response = await axios.post(`${getApiPrefix()}/auth/login`, {
        email,
        password,
    });
    
    // Сохраняем пользователя из ответа
    if (response.data.user) {
        authStore.setUser(response.data.user);
    }
    
    return response.data;
}

/**
 * Выход пользователя
 */
export async function logout() {
    const response = await axios.post(`${getApiPrefix()}/auth/logout`);
    
    // Очищаем пользователя
    authStore.clearUser();
    
    return response.data;
}

/**
 * Получение текущего пользователя
 */
export async function getCurrentUser() {
    // Сначала проверяем, есть ли пользователь в хранилище
    const cachedUser = authStore.getUser();
    if (cachedUser) {
        return { user: cachedUser };
    }
    
    // Если нет - запрашиваем с сервера
    const response = await axios.get(`${getApiPrefix()}/auth/user`);
    
    // Сохраняем пользователя
    if (response.data.user) {
        authStore.setUser(response.data.user);
    }
    
    return response.data;
}

