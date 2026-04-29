<template>
    <div class="admin-page">
        <div class="page-header">
            <h1>{{ $t('roles') }}</h1>
            <div class="header-actions">
                <router-link :to="`/${adminPrefix}/roles/new`" class="btn btn-primary">
                    {{ $t('create_role') }}
                </router-link>
            </div>
        </div>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ $t('role_name') }}</th>
                        <th>{{ $t('description') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="role in roles" :key="role.id">
                        <td>{{ role.id }}</td>
                        <td>{{ role.name }}</td>
                        <td>{{ role.description || '-' }}</td>
                        <td>
                            <div class="item-actions">
                                <router-link :to="`/${adminPrefix}/roles/${role.id}`" :title="$t('edit')">
                                    <Icon name="pencil" :size="16" />
                                </router-link>
                                <span @click="handleDelete(role.id)" :title="$t('delete')">
                                    <Icon name="trash-2" :size="16" />
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Всплывающие уведомления -->
        <ErrorNotification :message="error" @close="error = null" />
        <SuccessNotification :message="successMessage" @close="successMessage = null" />
        
        <!-- Индикатор загрузки -->
        <LoadingOverlay :show="loading" />
    </div>
</template>

<script>
import { getRoles, deleteRole, getAdminPrefix } from '../../api';
import ErrorNotification from '../common/ErrorNotification.vue';
import SuccessNotification from '../common/SuccessNotification.vue';
import LoadingOverlay from '../common/LoadingOverlay.vue';
import Icon from "../common/Icon.vue";

export default {
    name: 'Roles',
    components: {
        Icon,
        ErrorNotification,
        SuccessNotification,
        LoadingOverlay,
    },
    data() {
        return {
            roles: [],
            loading: false,
            error: null,
            successMessage: null,
            adminPrefix: getAdminPrefix(),
        };
    },
    async mounted() {
        await this.loadRoles();
    },
    methods: {
        async loadRoles() {
            this.loading = true;
            this.error = null;
            try {
                const response = await getRoles();
                this.roles = response.data || [];
            } catch (error) {
                console.error(this.$t('error_loading_roles'), error);
                if (error.response?.status === 403) {
                    this.error = this.$t('insufficient_permissions_users');
                } else {
                    this.error = this.$t('error_loading_roles');
                }
            } finally {
                this.loading = false;
            }
        },
        async handleDelete(id) {
            if (!confirm(this.$t('confirm_delete_role'))) {
                return;
            }
            this.error = null;
            this.successMessage = null;
            try {
                await deleteRole(id);
                this.successMessage = this.$t('role_deleted');
                await this.loadRoles();
            } catch (error) {
                console.error(this.$t('error_deleting_role'), error);
                if (error.response?.status === 403) {
                    this.error = this.$t('insufficient_permissions_users');
                } else {
                    this.error = this.$t('error_deleting_role');
                }
            }
        },
    },
};
</script>

<style scoped>
/* Стили шапки, кнопок, таблиц и загрузки вынесены в общий файл forms.css */
</style>

