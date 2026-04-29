<template>
    <div class="permissions-page">
        <h1>{{ $t('permissions') }}</h1>
        <div class="permissions-list">
            <div
                v-for="(permissions, group) in permissionsByGroup"
                :key="group"
                class="permission-group"
            >
                <h2 class="group-title">{{ getGroupName(group) }}</h2>
                <div class="permissions-grid">
                    <div
                        v-for="permission in permissions"
                        :key="permission.id"
                        class="permission-card"
                    >
                        <div class="permission-header">
                            <h3>{{ permission.name }}</h3>
                            <span class="permission-slug">{{ permission.slug }}</span>
                        </div>
                        <p v-if="permission.description" class="permission-description">
                            {{ permission.description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Всплывающие уведомления -->
        <ErrorNotification :message="error" @close="error = null" />
        
        <!-- Индикатор загрузки -->
        <LoadingOverlay :show="loading" />
    </div>
</template>

<script>
import { getPermissions } from '../../api';
import ErrorNotification from '../common/ErrorNotification.vue';
import LoadingOverlay from '../common/LoadingOverlay.vue';

export default {
    name: 'Permissions',
    components: {
        ErrorNotification,
        LoadingOverlay,
    },
    data() {
        return {
            permissionsByGroup: {},
            loading: false,
            error: null,
        };
    },
    async mounted() {
        await this.loadPermissions();
    },
    methods: {
        async loadPermissions() {
            this.loading = true;
            this.error = null;
            try {
                const response = await getPermissions();
                this.permissionsByGroup = response.data || {};
            } catch (error) {
                console.error(this.$t('error_loading_permissions_list'), error);
                this.error = this.$t('failed_to_load_permissions');
            } finally {
                this.loading = false;
            }
        },
        getGroupName(group) {
            const groupNames = {
                'resources': this.$t('resources_group'),
                'media': this.$t('media_group'),
                'settings': this.$t('settings_group'),
            };
            return groupNames[group] || group;
        },
    },
};
</script>

<style scoped>
.permissions-page {
    padding: 2rem;
}

.permissions-page h1 {
    margin-bottom: 2rem;
    font-size: 1.5rem;
    color: #333;
}

.loading,
.error {
    padding: 2rem;
    text-align: center;
    color: #666;
}

.error {
    color: #e74c3c;
}

.permissions-list {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.permission-group {
    background: white;
    border-radius: 4px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.group-title {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    color: #333;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.5rem;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.permission-card {
    background: #f8f9fa;
    border-radius: 4px;
    padding: 1rem;
    border-left: 3px solid #3498db;
}

.permission-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.permission-header h3 {
    margin: 0;
    font-size: 1rem;
    color: #333;
}

.permission-slug {
    padding: 0.25rem 0.5rem;
    background: #e9ecef;
    color: #666;
    border-radius: 3px;
    font-size: 0.75rem;
    font-family: monospace;
}

.permission-description {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}
</style>

