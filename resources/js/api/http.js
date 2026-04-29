import axios from 'axios';

let isAxiosConfigured = false;

/**
 * Возвращает базовые заголовки для API-запросов
 */
export function getApiRequestHeaders(extraHeaders = {}) {
    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...extraHeaders,
    };
}

/**
 * Глобальная настройка axios для CMS
 */
export function configureCmsAxios(getLocale = null) {
    if (isAxiosConfigured) {
        return;
    }

    axios.defaults.withCredentials = true;
    axios.defaults.headers.common = {
        ...axios.defaults.headers.common,
        ...getApiRequestHeaders(),
    };

    if (typeof getLocale === 'function') {
        axios.interceptors.request.use(
            (config) => {
                config.headers = getApiRequestHeaders(config.headers || {});
                config.headers['Accept-Language'] = getLocale();

                return config;
            },
            (error) => Promise.reject(error)
        );
    }

    isAxiosConfigured = true;
}

/**
 * Выполняет fetch с едиными API-заголовками
 */
export function apiFetch(url, options = {}) {
    const requestOptions = {
        method: 'GET',
        credentials: 'same-origin',
        ...options,
    };

    requestOptions.headers = getApiRequestHeaders(options.headers || {});

    return fetch(url, requestOptions);
}
