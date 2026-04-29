<template>
    <div class="admin-page">
        <div class="page-header">
            <h1>{{ isNew ? $t('create_user_page') : $t('edit_user_page') }}</h1>
            <div class="header-actions">
                <button type="button" @click="handleSubmit" class="btn btn-primary" :disabled="saving || loading">
                    {{ saving ? $t('saving') : $t('save') }}
                </button>
                <button type="button" @click="goBack" class="btn btn-secondary">
                    {{ $t('cancel') }}
                </button>
            </div>
        </div>
        <form @submit.prevent="handleSubmit" class="edit-form">
            <div class="form-group">
                <label for="name">{{ $t('name_required_user') }}</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autocomplete="off"
                    class="form-control"
                />
            </div>
            <div class="form-group">
                <label for="email">{{ $t('email_required') }}</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="off"
                    class="form-control"
                />
            </div>
            <div class="form-group">
                <label for="password">{{ isNew ? $t('password_required') : $t('password_optional') }}</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    :required="isNew"
                    autocomplete="new-password"
                    class="form-control"
                />
            </div>
            <div class="form-group">
                <label>{{ $t('roles') }}</label>
                <div class="checkbox-list">
                    <label
                        v-for="role in availableRoles"
                        :key="role.id"
                        class="checkbox-item"
                    >
                        <input
                            type="checkbox"
                            :value="role.id"
                            v-model="form.roles"
                        />
                        <span>{{ role.name }}</span>
                    </label>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" :disabled="saving">
                    {{ saving ? $t('saving') : $t('save') }}
                </button>
                <button type="button" @click="goBack" class="btn btn-secondary">
                    {{ $t('cancel') }}
                </button>
            </div>
        </form>
        
        <!-- Всплывающие уведомления -->
        <ErrorNotification :message="error" @close="error = null" />
        <SuccessNotification :message="successMessage" @close="successMessage = null" />
        
        <!-- Индикатор загрузки -->
        <LoadingOverlay :show="loading" />
    </div>
</template>

<script>
import { getUser, createUser, updateUser, getRoles, getAdminPrefix } from '../../api';
import ErrorNotification from '../common/ErrorNotification.vue';
import SuccessNotification from '../common/SuccessNotification.vue';
import LoadingOverlay from '../common/LoadingOverlay.vue';

export default {
    name: 'UserEdit',
    components: {
        ErrorNotification,
        SuccessNotification,
        LoadingOverlay,
    },
    data() {
        return {
            form: {
                name: '',
                email: '',
                password: '',
                roles: [],
            },
            availableRoles: [],
            loading: false,
            saving: false,
            error: null,
            successMessage: null,
            adminPrefix: getAdminPrefix(),
        };
    },
    computed: {
        isNew() {
            return this.$route.params.id === 'new';
        },
        userId() {
            return this.$route.params.id;
        },
    },
    async mounted() {
        await this.loadRoles();
        if (!this.isNew) {
            await this.loadUser();
        }
    },
    methods: {
        async loadUser() {
            this.loading = true;
            this.error = null;
            try {
                const response = await getUser(this.userId);
                const user = response.data;
                this.form = {
                    name: user.name,
                    email: user.email,
                    password: '',
                    roles: user.roles?.map(r => r.id) || [],
                };
            } catch (error) {
                console.error(this.$t('error_loading_user'), error);
                if (error.response?.status === 403) {
                    this.error = this.$t('insufficient_permissions_users');
                } else {
                    this.error = this.$t('failed_to_load_user');
                }
            } finally {
                this.loading = false;
            }
        },
        async loadRoles() {
            try {
                const response = await getRoles();
                this.availableRoles = response.data || [];
            } catch (error) {
                console.error(this.$t('error_loading_roles'), error);
            }
        },
        async handleSubmit() {
            this.saving = true;
            this.error = null;
            this.successMessage = null;
            try {
                const data = { ...this.form };
                if (!data.password && !this.isNew) {
                    delete data.password;
                }
                if (this.isNew) {
                    await createUser(data);
                    this.successMessage = this.$t('user_created');
                } else {
                    await updateUser(this.userId, data);
                    this.successMessage = this.$t('user_updated');
                }
                // Переходим на список через 1.5 секунды после успешного сохранения
                setTimeout(() => {
                    this.$router.push(`/${this.adminPrefix}/users`);
                }, 1500);
            } catch (error) {
                console.error(this.$t('error_saving_user'), error);
                if (error.response?.status === 403) {
                    this.error = this.$t('insufficient_permissions_users');
                } else if (error.response?.data?.errors) {
                    const validationErrors = error.response.data.errors;
                    const errorMessages = [];
                    for (const field in validationErrors) {
                        if (validationErrors[field] && Array.isArray(validationErrors[field])) {
                            errorMessages.push(...validationErrors[field]);
                        }
                    }
                    this.error = errorMessages.length > 0 
                        ? errorMessages.join(', ') 
                        : this.$t('validation_error');
                } else {
                    this.error = this.$t('failed_to_save_user');
                }
            } finally {
                this.saving = false;
            }
        },
        goBack() {
            this.$router.push(`/${this.adminPrefix}/users`);
        },
    },
};
</script>

<style scoped>
/* Стили вынесены в общий файл edit-forms.css */
</style>

