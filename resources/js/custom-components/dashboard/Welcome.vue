<template>
    <div class="welcome-card">
        <h3>{{ $t('dashboard_welcome_title') }}</h3>
        <p class="welcome-text">{{ $t('dashboard_welcome_intro') }}</p>

        <div class="welcome-links">
            <a :href="appUrl" target="_blank" rel="noopener noreferrer">
                {{ $t('dashboard_welcome_link_site') }}
            </a>
            <a :href="adminLink('users')">{{ $t('users') }}</a>
            <a :href="adminLink('roles')">{{ $t('roles') }}</a>
            <a :href="adminLink('permissions')">{{ $t('permissions') }}</a>
            <a :href="adminLink('settings')">{{ $t('site_settings') }}</a>
        </div>

        <p class="welcome-hint">{{ $t('dashboard_welcome_tree_hint') }}</p>
    </div>
</template>

<script>
import { getAdminPrefix, getAppUrl } from '../../api';

export default {
    name: 'Welcome',
    computed: {
        appUrl() {
            return getAppUrl();
        },
    },
    methods: {
        adminLink(path = '') {
            const adminPrefix = getAdminPrefix();
            const normalizedPath = String(path || '').replace(/^\/+/, '');
            return normalizedPath ? `/${adminPrefix}/${normalizedPath}` : `/${adminPrefix}`;
        },
    },
};
</script>

<style scoped>
.welcome-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1.25rem 1.5rem;
}

.welcome-card h3 {
    margin: 0 0 0.5rem 0;
    color: #0f172a;
}

.welcome-text {
    margin: 0 0 1rem 0;
    color: #334155;
}

.welcome-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 0.75rem;
    margin-bottom: 1rem;
}

.welcome-links a {
    display: inline-flex;
    align-items: center;
    color: #2c3e50;
    text-decoration: none;
    font-weight: 600;
}

.welcome-links a:hover {
    text-decoration: underline;
}

.welcome-hint {
    margin: 0;
    color: #475569;
}
</style>
