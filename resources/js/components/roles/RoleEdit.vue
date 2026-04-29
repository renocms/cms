<template>
    <div class="admin-page">
        <div class="page-header">
            <h1>{{ isNew ? $t('create_role_page') : $t('edit_role_page') }}</h1>
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
                <label for="name">{{ $t('name_required') }}</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    class="form-control"
                />
            </div>
            <div class="form-group">
                <label for="slug">{{ $t('slug_required') }}</label>
                <input
                    id="slug"
                    v-model="form.slug"
                    type="text"
                    required
                    class="form-control"
                />
            </div>
            <div class="form-group">
                <label for="description">{{ $t('role_description') }}</label>
                <textarea
                    id="description"
                    v-model="form.description"
                    class="form-control"
                    rows="3"
                ></textarea>
            </div>
            <div class="form-group">
                <label>{{ $t('permissions') }}</label>
                <div v-for="(permissions, group) in permissionsByGroup" :key="group" class="permission-group">
                    <h4>{{ getGroupName(group) }}</h4>
                    <div class="checkbox-list">
                        <label
                            v-for="permission in permissions"
                            :key="permission.id"
                            class="checkbox-item"
                        >
                            <input
                                type="checkbox"
                                :value="permission.id"
                                v-model="form.permissions"
                            />
                            <span>{{ permission.name }}</span>
                        </label>
                    </div>
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
import { getRole, createRole, updateRole, getPermissions, getAdminPrefix } from '../../api';
import ErrorNotification from '../common/ErrorNotification.vue';
import SuccessNotification from '../common/SuccessNotification.vue';
import LoadingOverlay from '../common/LoadingOverlay.vue';

export default {
    name: 'RoleEdit',
    components: {
        ErrorNotification,
        SuccessNotification,
        LoadingOverlay,
    },
    data() {
        return {
            form: {
                name: '',
                slug: '',
                description: '',
                permissions: [],
            },
            permissionsByGroup: {},
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
        roleId() {
            return this.$route.params.id;
        },
    },
    async mounted() {
        await this.loadPermissions();
        if (!this.isNew) {
            await this.loadRole();
        }
    },
    methods: {
        async loadRole() {
            this.loading = true;
            this.error = null;
            try {
                const response = await getRole(this.roleId);
                const role = response.data;
                this.form = {
                    name: role.name,
                    slug: role.slug,
                    description: role.description || '',
                    permissions: role.permissions?.map(p => p.id) || [],
                };
            } catch (error) {
                console.error(this.$t('error_loading_role'), error);
                if (error.response?.status === 403) {
                    this.error = this.$t('insufficient_permissions_view_roles');
                } else {
                    this.error = this.$t('failed_to_load_role');
                }
            } finally {
                this.loading = false;
            }
        },
        async loadPermissions() {
            try {
                const response = await getPermissions();
                this.permissionsByGroup = response.data || {};
            } catch (error) {
                console.error(this.$t('error_loading_permissions_list'), error);
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
        async handleSubmit() {
            this.saving = true;
            this.error = null;
            this.successMessage = null;
            try {
                if (this.isNew) {
                    await createRole(this.form);
                    this.successMessage = this.$t('role_created');
                } else {
                    await updateRole(this.roleId, this.form);
                    this.successMessage = this.$t('role_updated');
                }
                // Переходим на список через 1.5 секунды после успешного сохранения
                setTimeout(() => {
                    this.$router.push(`/${this.adminPrefix}/roles`);
                }, 1500);
            } catch (error) {
                console.error(this.$t('error_saving_role'), error);
                if (error.response?.status === 403) {
                    this.error = this.$t('insufficient_permissions_save_roles');
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
                    this.error = this.$t('failed_to_save_role');
                }
            } finally {
                this.saving = false;
            }
        },
        goBack() {
            this.$router.push(`/${this.adminPrefix}/roles`);
        },
    },
};
</script>

<style scoped>
/* Стили вынесены в общий файл edit-forms.css */
</style>

