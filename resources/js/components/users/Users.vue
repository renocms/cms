<template>
    <div class="admin-page">
        <div class="page-header">
            <h1>{{ $t('users') }}</h1>
            <div class="header-actions">
                <router-link :to="`/${adminPrefix}/users/new`" class="btn btn-primary">
                    {{ $t('create_user') }}
                </router-link>
            </div>
        </div>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ $t('user_name') }}</th>
                        <th>{{ $t('user_email') }}</th>
                        <th>{{ $t('user_roles') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td>{{ user.id }}</td>
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>
                            <span
                                v-for="role in user.roles"
                                :key="role.id"
                                class="role-badge"
                            >
                                {{ role.name }}
                            </span>
                            <span v-if="user.roles.length === 0" class="no-roles">-</span>
                        </td>
                        <td>
                            <div class="item-actions">
                                <router-link :to="`/${adminPrefix}/users/${user.id}`" :title="$t('edit')">
                                    <Icon name="pencil" :size="16" />
                                </router-link>
                                <span @click="handleDelete(user.id)" :title="$t('delete')">
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
import { getUsers, deleteUser, getAdminPrefix } from '../../api';
import ErrorNotification from '../common/ErrorNotification.vue';
import SuccessNotification from '../common/SuccessNotification.vue';
import LoadingOverlay from '../common/LoadingOverlay.vue';
import Icon from "../common/Icon.vue";

export default {
    name: 'Users',
    components: {
        Icon,
        ErrorNotification,
        SuccessNotification,
        LoadingOverlay,
    },
    data() {
        return {
            users: [],
            loading: false,
            error: null,
            successMessage: null,
            adminPrefix: getAdminPrefix(),
        };
    },
    async mounted() {
        await this.loadUsers();
    },
    methods: {
        async loadUsers() {
            this.loading = true;
            this.error = null;
            try {
                const response = await getUsers();
                this.users = response.data || [];
            } catch (error) {
                console.error(this.$t('error_loading_users'), error);
                if (error.response?.status === 403) {
                    this.error = this.$t('insufficient_permissions_users');
                } else {
                    this.error = this.$t('failed_to_load_users');
                }
            } finally {
                this.loading = false;
            }
        },
        async handleDelete(id) {
            if (!confirm(this.$t('confirm_delete_user'))) {
                return;
            }
            this.error = null;
            this.successMessage = null;
            try {
                await deleteUser(id);
                this.successMessage = this.$t('user_deleted');
                await this.loadUsers();
            } catch (error) {
                console.error(this.$t('error_deleting_user'), error);
                if (error.response?.status === 403) {
                    this.error = this.$t('insufficient_permissions_delete_user');
                } else {
                    this.error = this.$t('failed_to_delete_user');
                }
            }
        },
    },
};
</script>

<style scoped>
/* Стили шапки, кнопок, таблиц и загрузки вынесены в общий файл forms.css */
</style>

