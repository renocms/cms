<template>
    <div class="admin-page">
        <div class="page-header">
            <h1>{{ $t('site_settings') }}</h1>
            <div class="context-selector">
                <label for="context-select">{{ $t('context') }}:</label>
                <select id="context-select" v-model="selectedContextId" @change="loadSettings" class="form-control">
                    <option v-for="context in contexts" :key="context.id" :value="context.id">
                        {{ context.name }}
                    </option>
                </select>
            </div>
        </div>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>{{ $t('setting_key') }}</th>
                        <th>{{ $t('setting_value') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(setting, index) in settingsList" :key="index">
                        <td>
                            <input
                                v-model="setting.key"
                                type="text"
                                class="form-control"
                                :placeholder="$t('setting_key')"
                            />
                        </td>
                        <td>
                            <input
                                v-model="setting.value"
                                type="text"
                                class="form-control"
                                :placeholder="$t('setting_value')"
                            />
                        </td>
                        <td>
                            <div class="item-actions">
                                <span v-if="setting.isExisting" @click="handleDelete(setting.id, index)" :title="$t('delete')">
                                    <Icon name="trash-2" :size="16"/>
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="table-actions">
                <button @click="addNewSetting" class="btn btn-secondary">
                    {{ $t('add_setting') }}
                </button>
                <button @click="handleSave" class="btn btn-primary" :disabled="saving">
                    {{ saving ? $t('loading') : $t('save') }}
                </button>
            </div>
        </div>
        
        <!-- Всплывающие уведомления -->
        <ErrorNotification :message="error" @close="error = null" />
        <SuccessNotification :message="successMessage" @close="successMessage = null" />
        
        <!-- Индикатор загрузки -->
        <LoadingOverlay :show="loading" />
    </div>
</template>

<script>
import { getSettings, updateSettings, deleteSetting, getContexts } from '../../api';
import ErrorNotification from '../common/ErrorNotification.vue';
import SuccessNotification from '../common/SuccessNotification.vue';
import LoadingOverlay from '../common/LoadingOverlay.vue';
import Icon from "../common/Icon.vue";

export default {
    name: 'Settings',
    components: {
        Icon,
        ErrorNotification,
        SuccessNotification,
        LoadingOverlay,
    },
    data() {
        return {
            contexts: [],
            selectedContextId: null,
            settingsList: [],
            loading: false,
            saving: false,
            error: null,
            successMessage: null,
        };
    },
    async mounted() {
        await this.loadContexts();
        if (this.contexts.length > 0) {
            this.selectedContextId = this.contexts[0].id;
            await this.loadSettings();
        }
    },
    methods: {
        async loadContexts() {
            try {
                const response = await getContexts();
                this.contexts = response.data || [];
            } catch (error) {
                console.error(this.$t('error_loading_contexts'), error);
                this.error = this.$t('error_loading_contexts');
            }
        },
        async loadSettings() {
            if (!this.selectedContextId) {
                this.settingsList = [];
                return;
            }

            this.loading = true;
            this.error = null;
            try {
                const response = await getSettings(this.selectedContextId);
                const settings = response.data || [];

                this.settingsList = settings.map(setting => ({
                    id: setting.id,
                    key: setting.key,
                    originalKey: setting.key, // Сохраняем оригинальный ключ для отслеживания изменений
                    value: typeof setting.value === 'object' 
                        ? JSON.stringify(setting.value, null, 2) 
                        : String(setting.value),
                    isExisting: true,
                }));
            } catch (error) {
                console.error(this.$t('error_loading_settings'), error);
                if (error.response?.status === 403) {
                    this.error = this.$t('insufficient_permissions_users');
                } else {
                    this.error = this.$t('error_loading_settings');
                }
            } finally {
                this.loading = false;
            }
        },
        addNewSetting() {
            this.settingsList.push({
                id: null,
                key: '',
                originalKey: '',
                value: '',
                isExisting: false,
            });
        },
        async handleDelete(id, index) {
            if (!confirm(this.$t('confirm_delete_user'))) {
                return;
            }

            this.saving = true;
            this.error = null;
            this.successMessage = null;
            try {
                await deleteSetting(id);
                this.successMessage = this.$t('setting_deleted');
                this.settingsList.splice(index, 1);
            } catch (error) {
                console.error(this.$t('error_deleting_setting'), error);
                if (error.response?.status === 403) {
                    this.error = this.$t('insufficient_permissions_users');
                } else {
                    this.error = this.$t('error_deleting_setting');
                }
            } finally {
                this.saving = false;
            }
        },
        async handleSave() {
            this.saving = true;
            this.error = null;
            this.successMessage = null;
            try {
                // Фильтруем пустые ключи
                const validSettings = this.settingsList.filter(s => s.key.trim() !== '');

                if (validSettings.length === 0) {
                    this.error = this.$t('no_settings_to_save');
                    this.saving = false;
                    return;
                }

                // Удаляем настройки, у которых изменился ключ
                const settingsToDelete = [];
                validSettings.forEach(setting => {
                    if (setting.isExisting && setting.originalKey !== setting.key.trim()) {
                        settingsToDelete.push(setting.id);
                    }
                });

                // Удаляем настройки со старыми ключами
                for (const id of settingsToDelete) {
                    await deleteSetting(id);
                }

                // Преобразуем в объект для API
                const settingsToSave = {};
                validSettings.forEach(setting => {
                    const key = setting.key.trim();
                    let value = setting.value.trim();

                    // Пытаемся распарсить JSON, если не получается - оставляем как строку
                    if (value.startsWith('{') || value.startsWith('[')) {
                        try {
                            value = JSON.parse(value);
                        } catch (e) {
                            // Оставляем как строку
                        }
                    }

                    settingsToSave[key] = value;
                });

                await updateSettings(this.selectedContextId, settingsToSave);
                this.successMessage = this.$t('settings_saved');
                await this.loadSettings();
            } catch (error) {
                console.error(this.$t('error_saving_settings'), error);
                if (error.response?.status === 403) {
                    this.error = this.$t('insufficient_permissions_save_settings');
                } else if (error.response?.data?.errors) {
                    this.error = Object.values(error.response.data.errors).flat().join(', ');
                } else {
                    this.error = this.$t('failed_to_save_settings');
                }
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>

<style scoped>
.context-selector {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.context-selector label {
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 0.9rem;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-control[readonly] {
    background: #f8f9fa;
    cursor: not-allowed;
}

.table-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 0.9rem;
    transition: background-color 0.2s;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.85rem;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2980b9;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-delete {
    background: #e74c3c;
    color: white;
}

.btn-delete:hover:not(:disabled) {
    background: #c0392b;
}

.btn-delete:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.new-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background: #27ae60;
    color: white;
    border-radius: 3px;
    font-size: 0.75rem;
}
</style>
