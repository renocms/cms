/**
 * Простое хранилище состояния авторизации
 */
let currentUser = null;

export const authStore = {
    /**
     * Установить текущего пользователя
     */
    setUser(user) {
        currentUser = user;
    },

    /**
     * Получить текущего пользователя
     */
    getUser() {
        return currentUser;
    },

    /**
     * Очистить пользователя
     */
    clearUser() {
        currentUser = null;
    },
};

