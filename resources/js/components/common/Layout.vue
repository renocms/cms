<template>
    <div class="layout">
        <ErrorNotification :message="errorMessage" @close="errorMessage = null" />
        <SuccessNotification :message="successMessage" @close="successMessage = null" />
        <header class="header">
            <div class="header-content">
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
                            <button
                                type="button"
                                class="dropdown-item"
                                :disabled="cacheLoading"
                                @click="handleFlushCms"
                            >
                                {{ $t('flush_cms_cache') }}
                            </button>
                            <button
                                type="button"
                                class="dropdown-item"
                                :disabled="cacheLoading"
                                @click="handleFlushFull"
                            >
                                {{ $t('flush_full_cache') }}
                            </button>
                        </div>
                    </div>
                    <span class="user-name">{{ user?.name || $t('user') }}</span>
                    <button @click="handleLogout" class="logout-btn">{{ $t('logout') }}</button>
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
import { getCurrentUser, logout, getAdminPrefix, flushCmsCache, flushFullCache } from '../../api';
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
    justify-content: space-between;
    align-items: center;
    height: 60px;
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
    text-decoration: none;
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

button.dropdown-item {
    width: 100%;
    border: none;
    background: white;
    font: inherit;
    text-align: left;
    cursor: pointer;
}

button.dropdown-item:disabled {
    opacity: 0.55;
    cursor: not-allowed;
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
}

.user-name {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.9);
}

.logout-btn {
    padding: 0.5rem 1rem;
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background-color 0.2s;
}

.logout-btn:hover {
    background: #c0392b;
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
    overflow-y: auto;
    overflow-x: hidden;
    padding: 0;
    min-height: 0;
    min-width: 0;
    font-size: 0.875rem;
    color: #333;
}
</style>

