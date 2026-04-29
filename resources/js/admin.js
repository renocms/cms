import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';
import { configureCmsAxios } from './api/http';

// Стили
import '../css/admin.css';
import './styles/forms.css';
import './styles/modals.css';

// API
import { getAdminPrefix, getApiPrefix, getCurrentUser } from './api';
import { rememberLastAdminUrl } from './utils/lastAdminUrl';

// i18n
import i18n, { getLocale } from './i18n';

// Event Bus
import eventBus from './utils/eventBus';

// Утилиты
import { loadRoutes } from './utils/routeLoader';

// Компоненты
import App from './components/common/App.vue';
import Login from './components/common/Login.vue';

// Настройка axios
configureCmsAxios(() => getLocale());

// Перехватчик ответов для обработки 401
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            const adminPrefix = getAdminPrefix();
            const current =
                window.location.pathname + window.location.search + window.location.hash;
            rememberLastAdminUrl(current);
            if (window.location.pathname !== `/${adminPrefix}/login`) {
                window.location.href = `/${adminPrefix}/login`;
            }
        }
        return Promise.reject(error);
    }
);

// Получение префикса из конфига
const adminPrefix = getAdminPrefix();
const apiPrefix = getApiPrefix();
let areDynamicRoutesLoaded = false;
let dynamicRoutesLoadingPromise = null;

// Роут логина (статический)
const loginRoute = {
    path: `/${adminPrefix}/login`,
    name: 'login',
    component: Login,
    meta: { requiresAuth: false }
};

// Placeholder для защищенных роутов.
// Нужен, чтобы перед загрузкой динамических роутов срабатывала auth-проверка.
const protectedPlaceholderRoute = {
    path: `/${adminPrefix}/:pathMatch(.*)*`,
    name: 'admin-protected-placeholder',
    component: { template: '<div />' },
    meta: { requiresAuth: true },
};

async function ensureDynamicRoutes(router) {
    if (areDynamicRoutesLoaded) {
        return;
    }

    if (dynamicRoutesLoadingPromise) {
        await dynamicRoutesLoadingPromise;
        return;
    }

    dynamicRoutesLoadingPromise = (async () => {
        const dynamicRoutes = await loadRoutes(apiPrefix, adminPrefix);

        for (const route of dynamicRoutes) {
            router.addRoute(route);
        }

        areDynamicRoutesLoaded = true;
    })();

    try {
        await dynamicRoutesLoadingPromise;
    } finally {
        dynamicRoutesLoadingPromise = null;
    }
}

// Инициализация приложения
async function initApp() {
    const routes = [
        loginRoute,
        protectedPlaceholderRoute,
    ];
    
    const router = createRouter({
        history: createWebHistory('/'),
        routes,
    });

    // Проверка авторизации
    router.beforeEach(async (to, from, next) => {
        const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
        
        if (requiresAuth) {
            try {
                // getCurrentUser использует кэш, если пользователь уже есть
                const response = await getCurrentUser();
                if (response.user) {
                    if (!areDynamicRoutesLoaded) {
                        await ensureDynamicRoutes(router);
                        next({
                            path: to.path,
                            query: to.query,
                            hash: to.hash,
                            replace: true,
                        });
                        return;
                    }

                    next();
                } else {
                    rememberLastAdminUrl(to.fullPath);
                    next(`/${adminPrefix}/login`);
                }
            } catch (error) {
                rememberLastAdminUrl(to.fullPath);
                next(`/${adminPrefix}/login`);
            }
        } else {
            next();
        }
    });

    router.afterEach((to) => {
        const requiresAuth = to.matched.some((record) => record.meta.requiresAuth);
        if (requiresAuth) {
            rememberLastAdminUrl(to.fullPath);
        }
    });

    // Создание приложения
    const app = createApp(App);

    app.use(router);

    // Добавляем глобальный доступ к переводам
    app.config.globalProperties.$t = i18n.t;

    // Добавляем глобальный доступ к event bus
    app.config.globalProperties.$eventBus = eventBus;

    // Ждем готовности роутера перед монтированием
    router.isReady().then(() => {
        app.mount('#admin-app');
    });
}

// Запускаем инициализацию
initApp();

