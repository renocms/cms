<template>
    <div class="layout">
        <ErrorNotification :message="errorMessage" @close="errorMessage = null" />
        <SuccessNotification :message="successMessage" @close="successMessage = null" />
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14">
                        <path d="M6.83 0a1 1 0 0 0-.65.25L3.47 3.02a.85.85 0 0 0 0 1.1l2.71 2.76q.83.84 1.64 0l2.71-2.76a.85.85 0 0 0 0-1.1L7.82.25C7.68.11 7.24-.02 6.83 0m-5 4.67S.48 6.06.2 6.33a.85.85 0 0 0 0 1.1l6.26 6.36c.27.28.81.28 1.08 0l6.26-6.35a.85.85 0 0 0 0-1.1l-1.63-1.67a.8.8 0 0 0-1.1 0L8.1 7.71c-.55.56-1.63.56-2.18 0L2.92 4.67a.8.8 0 0 0-1.09 0"/>
                    </svg>
                </div>
                <nav class="header-nav">
                    <router-link :to="`/${adminPrefix}`" class="nav-link">
                        {{ $t('dashboard') }}
                    </router-link>
                    <div
                        v-for="menu in menus"
                        :key="menu.id"
                        class="dropdown"
                        @mouseenter="openDropdown(menu.id)"
                        @mouseleave="closeDropdown(menu.id)"
                    >
                        <button class="nav-link dropdown-toggle">
                            {{ menu.label }}
                            <Icon name="chevron-down" :size="16" />
                        </button>
                        <div v-if="activeDropdown === menu.id" class="dropdown-menu">
                            <router-link
                                v-for="item in menu.children"
                                :key="item.path || item.id"
                                :to="item.path ? `/${adminPrefix}/${item.path}` : '#'"
                                class="dropdown-item"
                                @click="closeDropdown(menu.id)"
                            >
                                {{ $t(item.label) }}
                            </router-link>
                        </div>
                    </div>
                </nav>
                <div class="header-actions">
                    <div
                        class="dropdown header-cache-dropdown"
                        @mouseenter="cacheDropdownOpen = true"
                        @mouseleave="cacheDropdownOpen = false"
                    >
                        <button type="button" class="nav-link dropdown-toggle cache-menu-btn">
                            {{ $t('cache_menu') }}
                            <Icon name="chevron-down" :size="16" />
                        </button>
                        <div v-if="cacheDropdownOpen" class="dropdown-menu dropdown-menu-end">
                            <div class="dropdown-item" @click="handleFlushCms">
                                {{ $t('flush_cms_cache') }}
                            </div>
                            <div class="dropdown-item" @click="handleFlushFull">
                                {{ $t('flush_full_cache') }}
                            </div>
                        </div>
                    </div>

                    <div class="user-card" v-if="user">
                        <Icon name="circle-user-round" />
                        <div class="user-name">
                            <div class="name">{{ user.name }}</div>
                            <div class="email">{{ user.email }}</div>
                        </div>
                    </div>

                    <a
                        :href="appUrl"
                        class="site-link-btn"
                        :title="$t('view_site')"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <Icon name="eye" :size="20" />
                    </a>

                    <button @click="handleLogout" class="logout-btn" :title="$t('logout')">
                        <Icon name="log-out" :size="16"/>
                    </button>
                </div>
            </div>
        </header>
        <div class="layout-body">
            <aside class="sidebar">
                <ResourceTree />
            </aside>
            <main class="main-content">
                <router-view :key="$route.fullPath" />
            </main>
        </div>
    </div>
</template>

<script>
import { getCurrentUser, logout, getAdminPrefix, getAppUrl, flushCmsCache, flushFullCache } from '../../api';
import { loadMenuItems } from '../../utils/menuLoader';
import { authStore } from '../../store/auth';
import ResourceTree from '../resources/ResourceTree.vue';
import Icon from "./Icon.vue";
import ErrorNotification from './ErrorNotification.vue';
import SuccessNotification from './SuccessNotification.vue';

export default {
    name: 'Layout',
    components: {
        Icon,
        ResourceTree,
        ErrorNotification,
        SuccessNotification,
    },
    data() {
        return {
            user: null,
            adminPrefix: getAdminPrefix(),
            appUrl: getAppUrl(),
            activeDropdown: null,
            menuItems: [],
            cacheDropdownOpen: false,
            cacheLoading: false,
            successMessage: null,
            errorMessage: null,
        };
    },
    computed: {
        menus() {
            return this.menuItems.map(menu => ({
                ...menu,
                label: this.$t(menu.label),
            }));
        },
    },
    async mounted() {
        // Сначала пытаемся получить пользователя из хранилища
        const cachedUser = authStore.getUser();
        if (cachedUser) {
            this.user = cachedUser;
        } else {
            // Если нет - запрашиваем с сервера
            await this.loadUser();
        }
        
        // Загружаем элементы меню
        await this.loadMenuItems();
    },
    methods: {
        async loadUser() {
            try {
                const response = await getCurrentUser();
                this.user = response.user;
            } catch (error) {
                console.error(this.$t('error_loading_user_data'), error);
            }
        },
        async loadMenuItems() {
            try {
                const apiPrefix = `/${this.adminPrefix}/api`;
                const items = await loadMenuItems(apiPrefix);
                this.menuItems = items;
            } catch (error) {
                console.error('Failed to load menu items:', error);
                this.menuItems = [];
            }
        },
        async handleLogout() {
            try {
                await logout();
                this.user = null;
                this.$router.push(`/${this.adminPrefix}/login`);
            } catch (error) {
                console.error(this.$t('error_logout'), error);
            }
        },
        openDropdown(menuId) {
            this.activeDropdown = menuId;
        },
        closeDropdown(menuId) {
            if (this.activeDropdown === menuId) {
                this.activeDropdown = null;
            }
        },
        async handleFlushCms() {
            await this.runCacheAction(() => flushCmsCache());
        },
        async handleFlushFull() {
            await this.runCacheAction(() => flushFullCache());
        },
        async runCacheAction(action) {
            this.cacheLoading = true;
            this.errorMessage = null;
            this.successMessage = null;
            try {
                const data = await action();
                this.successMessage = data.message ?? '';
                this.cacheDropdownOpen = false;
            } catch (error) {
                const status = error.response?.status;
                if (status === 403) {
                    this.errorMessage = this.$t('insufficient_permissions_settings');
                } else {
                    this.errorMessage = error.response?.data?.message || this.$t('error_cache_flush');
                }
            } finally {
                this.cacheLoading = false;
            }
        },
    },
};
</script>

<style scoped>
.layout {
    height: 100vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.header {
    background: #2c3e50;
    color: white;
    padding: 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    z-index: 100;
}

.header-content {
    max-width: 100%;
    margin: 0;
    padding: 0 1.5rem 0 1rem;
    display: flex;
    align-items: center;
    height: 60px;
}

.logo {
    width: 1.75rem;
    height: 1.75rem;
    margin-right: 1rem;
}

.logo svg path {
    fill: #ffffff5c;
}

.header-nav {
    display: flex;
    align-items: center;
    position: relative;
}

.dropdown {
    position: relative;
}

.dropdown-toggle {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    cursor: pointer;
    border: none;
    background: none;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.dropdown-toggle:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 0;
    background: white;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    min-width: 180px;
    z-index: 1000;
    overflow: hidden;
}

.dropdown-item {
    display: block;
    padding: 0.75rem 1rem;
    color: #333;
    font-size: 0.875rem;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.2s;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.header-cache-dropdown .dropdown-menu-end {
    left: auto;
    right: 0;
}

.cache-menu-btn {
    cursor: pointer;
}

.nav-link {
    color: white;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: background-color 0.2s;
    font-size: 0.875rem;
}

.nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-left: auto;
}

.user-card {
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    column-gap: 0.5rem;
}

.user-card .email {
    font-size: 0.625rem;
    opacity: 0.7;
}

.logout-btn {
    width: 2rem;
    height: 2rem;
    background: #e74c3c;
    color: #fff;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: .9rem;
    transition: background-color .2s;
}

.logout-btn:hover {
    background: #c0392b;
}

.logout-btn svg {
    display: block;
    margin: 0 auto;
}

.site-link-btn {
    width: 2rem;
    height: 2rem;
    background: #556d85;
    color: #fff;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: .9rem;
    transition: background-color .2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.site-link-btn:hover {
    background: #6b849d;
}

.site-link-btn svg {
    display: block;
    margin: 0 auto;
}

.layout-body {
    display: flex;
    flex: 1;
    overflow: hidden;
    min-height: 0;
}

.sidebar {
    width: 300px;
    background: #f8f9fa;
    border-right: 1px solid #dee2e6;
    overflow-y: auto;
    flex-shrink: 0;
    min-height: 0;
}

.main-content {
    flex: 1;
    background: #f5f5f5;
    position: relative;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 0;
    min-height: 0;
    min-width: 0;
    font-size: 0.875rem;
    color: #333;
}
</style>

