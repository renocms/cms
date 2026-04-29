<template>
    <div class="admin-page">
        <div class="page-header no-bottom">
            <h1>
                <template v-if="$route.params.id === 'new'">
                    {{ $t('create_resource_page') }}
                    <span v-if="resource?.context?.name" class="context-name">({{ resource.context.name }})</span>
                </template>
                <template v-else>
                    {{ $t('edit_resource') }}: {{ resource?.title || resource?.slug || $t('loading') }}
                    <span v-if="resource?.context?.name" class="context-name">({{ resource.context.name }})</span>
                </template>
            </h1>
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
            <div class="form-main">
                <div v-if="schemaTabs.length > 0" class="tabs-container">
                    <div class="tabs-nav">
                        <button
                            v-for="tab in schemaTabs"
                            :key="tab.tab_key"
                            type="button"
                            class="tab-button"
                            :class="{ active: tab.tab_key === activeSchemaTab?.tab_key }"
                            @click="activeTabKey = tab.tab_key"
                        >
                            {{ tab.name }}
                        </button>
                    </div>

                    <div v-if="activeSchemaTab" class="tab-panel">
                        <div v-if="activeSchemaTab.schema.length > 0" class="form-section">
                            <template v-for="(schemaField, index) in activeSchemaTab.schema">
                                <div
                                    v-if="schemaField.is_system"
                                    :key="getSchemaFieldKey(schemaField, index)"
                                    class="form-group"
                                >
                                    <template v-if="schemaField.system_key === 'resource_layout_id'">
                                        <label for="resource_layout_id">
                                            {{ schemaField.name }}
                                            <span v-if="schemaField.is_required" class="required">*</span>
                                        </label>
                                        <select
                                            id="resource_layout_id"
                                            v-model="form.resource_layout_id"
                                            class="form-control"
                                            :required="schemaField.is_required"
                                            @change="onLayoutChange"
                                        >
                                            <option
                                                v-for="layout in availableLayouts"
                                                :key="layout.id"
                                                :value="layout.id"
                                            >
                                                {{ layout.name }}
                                            </option>
                                        </select>
                                    </template>

                                    <template v-else-if="schemaField.system_key === 'slug'">
                                        <label for="slug">
                                            {{ schemaField.name }}
                                            <span v-if="schemaField.is_required" class="required">*</span>
                                        </label>
                                        <input
                                            id="slug"
                                            v-model="form.slug"
                                            type="text"
                                            class="form-control"
                                            :required="schemaField.is_required"
                                        />
                                    </template>

                                    <template v-else-if="schemaField.system_key === 'status'">
                                        <label for="status">{{ schemaField.name }}</label>
                                        <select
                                            id="status"
                                            v-model="form.status"
                                            class="form-control"
                                        >
                                            <option value="draft">{{ $t('draft') }}</option>
                                            <option value="published">{{ $t('published') }}</option>
                                            <option value="archived">{{ $t('archived') }}</option>
                                        </select>
                                    </template>

                                    <template v-else-if="schemaField.system_key === 'sort_order'">
                                        <label for="sort_order">{{ schemaField.name }}</label>
                                        <input
                                            id="sort_order"
                                            v-model.number="form.sort_order"
                                            type="number"
                                            min="0"
                                            class="form-control"
                                        />
                                    </template>

                                    <template v-else-if="schemaField.system_key === 'show_in_menu'">
                                        <label class="checkbox-label" for="show_in_menu">
                                            <input
                                                id="show_in_menu"
                                                v-model="form.show_in_menu"
                                                type="checkbox"
                                            />
                                            {{ schemaField.name }}
                                        </label>
                                    </template>
                                </div>

                                <div
                                    v-else
                                    :key="getSchemaFieldKey(schemaField, index)"
                                    class="form-group"
                                >
                                    <label :for="`field-${schemaField.key}`">
                                        {{ schemaField.name }}
                                        <span v-if="schemaField.is_required" class="required">*</span>
                                    </label>
                                    <component
                                        v-if="getFieldEditorComponent(schemaField)"
                                        :is="getFieldEditorComponent(schemaField)"
                                        :key="`editor-${schemaField.id || schemaField.key}-${schemaField.type}`"
                                        v-model="form.values[schemaField.key]"
                                        v-bind="getFieldEditorProps(schemaField)"
                                    />
                                    <div v-else class="field-info">
                                        {{ $t('field_type_not_supported') }}: {{ schemaField.type }}
                                    </div>
                                    <p v-if="schemaField.configuration?.note" class="field-note">
                                        {{ schemaField.configuration.note }}
                                    </p>
                                </div>
                            </template>
                        </div>

                        <div v-else class="no-fields">
                            {{ $t('no_fields_in_layout') }}
                        </div>
                    </div>
                </div>

                <div v-else class="no-fields">
                    {{ $t('no_fields_in_layout') }}
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
import { makeDraftResource, createResource, getResource, updateResource, getResourceEditPlugins } from '../../api/resources';
import { getResourceLayouts } from '../../api/resourceLayouts';
import { getAdminPrefix } from '../../api';
import { loadComponent } from '../../utils/componentLoader';
import { getFieldEditorModulePath } from '../../utils/fieldEditor';
import {
    fieldSchemaHasDefault,
    getEmptyFormValueForField,
    normalizeCheckboxFormValue,
    normalizeCheckboxGroupFormValue,
} from '../../utils/fieldSchema';
import { loadPlugins, destroyPlugins } from '../../utils/pluginLoader';
import ErrorNotification from '../common/ErrorNotification.vue';
import SuccessNotification from '../common/SuccessNotification.vue';
import LoadingOverlay from '../common/LoadingOverlay.vue';

export default {
    name: 'ResourceEdit',
    components: {
        ErrorNotification,
        SuccessNotification,
        LoadingOverlay,
    },
    props: {
        initialResource: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            resource: null,
            activeTabKey: null,
            loading: true,
            saving: false,
            error: null,
            successMessage: null,
            availableLayouts: [],
            plugins: [],
            resolvedCatalogId: null,
            form: {
                context_id: null,
                resource_type_id: null,
                parent_id: null,
                resource_layout_id: null,
                slug: '',
                status: 'draft',
                sort_order: 0,
                show_in_menu: true,
                values: {},
            },
        };
    },
    computed: {
        layoutSchema() {
            if (!this.resource || !Array.isArray(this.resource.schema)) {
                return [];
            }

            return this.resource.schema;
        },
        schemaTabs() {
            return this.layoutSchema
                .filter((schemaElement) => schemaElement?.element === 'tab')
                .map((tab, index) => ({
                    ...tab,
                    tab_key: this.getTabKey(tab, index),
                    schema: Array.isArray(tab.schema) ? tab.schema : [],
                }));
        },
        activeSchemaTab() {
            if (this.schemaTabs.length === 0) {
                return null;
            }

            return this.schemaTabs.find((tab) => tab.tab_key === this.activeTabKey) || this.schemaTabs[0];
        },
        layoutFields() {
            return this.collectUserFields(this.layoutSchema);
        },
        valuesMap() {
            const map = {};
            (this.resource?.values || []).forEach(v => {
                map[v.resource_field_id] = v;
            });
            return map;
        },
    },
    async mounted() {
        await this.initializeResource();
        await this.loadLayouts();
        await this.loadPlugins();
    },
    beforeDestroy() {
        // Уничтожаем все плагины при уничтожении компонента
        destroyPlugins(this.plugins);
    },
    watch: {
        schemaTabs: {
            handler(tabs) {
                if (!tabs.length) {
                    this.activeTabKey = null;
                    return;
                }

                const hasActiveTab = tabs.some((tab) => tab.tab_key === this.activeTabKey);
                if (!hasActiveTab) {
                    this.activeTabKey = tabs[0].tab_key;
                }
            },
            immediate: true,
        },
        initialResource: {
            async handler(resource) {
                if (!resource) {
                    return;
                }

                this.applyResource(resource);
                await this.loadLayouts();
            },
            immediate: false,
        },
        '$route.params.id': {
            async handler() {
                if (this.initialResource) {
                    return;
                }

                // Перезагружаем ресурс при изменении ID в URL
                await this.loadResource();
                await this.loadLayouts();
            },
            immediate: false,
        },
    },
    methods: {
        async initializeResource() {
            if (this.initialResource) {
                this.applyResource(this.initialResource);
                this.loading = false;
                return;
            }

            await this.loadResource();
        },
        applyResource(resource) {
            const resourceId = this.$route.params.id;
            const parentId = this.$route.query.parent_id;
            const layoutId = this.$route.query.layout_id;
            const contextIdFromQuery = this.$route.query.context_id;
            const parsedContextId = contextIdFromQuery ? parseInt(contextIdFromQuery, 10) : null;

            this.resource = {
                ...resource,
                schema: Array.isArray(resource?.schema) ? resource.schema : [],
            };

            this.form.context_id = resource?.context_id || parsedContextId || null;
            this.form.resource_type_id = resource?.resource_type_id || null;
            this.form.parent_id = resource?.parent_id || (parentId ? parseInt(parentId, 10) : null);
            this.form.slug = resource?.slug || '';
            this.form.status = resource?.status || 'draft';
            this.form.sort_order = resource?.sort_order || 0;
            this.form.show_in_menu = resource?.show_in_menu !== undefined ? !!resource.show_in_menu : true;

            if (resourceId === 'new') {
                const defaultLayoutId = resource?.resource_layout_id || null;
                this.form.resource_layout_id = layoutId ? parseInt(layoutId, 10) : defaultLayoutId;
                return;
            }

            this.form.resource_layout_id = resource?.resource_layout_id || null;
        },
        getSelectedLayout() {
            if (!this.form.resource_layout_id || !Array.isArray(this.availableLayouts)) {
                return null;
            }

            const selectedLayoutId = Number(this.form.resource_layout_id);
            return this.availableLayouts.find((layout) => Number(layout.id) === selectedLayoutId) || null;
        },
        syncLayoutSchemaWithSelectedLayout() {
            if (!this.resource) {
                this.resource = { values: [], schema: [] };
            }

            const selectedLayout = this.getSelectedLayout();
            this.resource.schema = Array.isArray(selectedLayout?.schema) ? selectedLayout.schema : [];
        },
        initializeFormValuesFromResourceValues() {
            const valuesMapById = {};
            (this.resource?.values || []).forEach((value) => {
                valuesMapById[value.resource_field_id] = value.value;
            });

            const newValues = {};
            this.layoutFields.forEach((layoutField) => {
                const resourceFieldId = layoutField.id;
                const resourceFieldKey = layoutField.key;

                if (!resourceFieldKey) {
                    return;
                }

                const stored = valuesMapById[resourceFieldId];
                const hasStored = stored !== undefined;

                if (layoutField.type === 'checkbox') {
                    if (hasStored) {
                        newValues[resourceFieldKey] = normalizeCheckboxFormValue(stored);
                    } else {
                        newValues[resourceFieldKey] = getEmptyFormValueForField(layoutField);
                    }

                    return;
                }

                if (layoutField.type === 'checkbox_group') {
                    if (hasStored) {
                        newValues[resourceFieldKey] = normalizeCheckboxGroupFormValue(stored);
                    } else {
                        newValues[resourceFieldKey] = getEmptyFormValueForField(layoutField);
                    }

                    return;
                }

                if (layoutField.type === 'repeater' || layoutField.type === 'gallery') {
                    if (hasStored) {
                        newValues[resourceFieldKey] = Array.isArray(stored) ? stored : [];
                    } else if (fieldSchemaHasDefault(layoutField.configuration)) {
                        const d = layoutField.configuration.default;
                        newValues[resourceFieldKey] = Array.isArray(d) ? d : [];
                    } else {
                        newValues[resourceFieldKey] = [];
                    }

                    return;
                }

                if (hasStored) {
                    newValues[resourceFieldKey] = stored;
                    return;
                }

                newValues[resourceFieldKey] = getEmptyFormValueForField(layoutField);
            });

            this.form.values = newValues;
        },
        async applyLayoutAndPopulateValues() {
            this.syncLayoutSchemaWithSelectedLayout();
            await this.$nextTick();
            this.initializeFormValuesFromResourceValues();
        },
        getTabKey(tab, index) {
            return `${index}-${tab?.name || 'tab'}`;
        },
        getSchemaFieldKey(schemaField, index) {
            return `${schemaField.system_key || schemaField.id || schemaField.key || 'field'}-${index}`;
        },
        collectUserFields(schema) {
            if (!Array.isArray(schema)) {
                return [];
            }

            return schema.reduce((fields, schemaElement) => {
                if (schemaElement?.element === 'tab') {
                    fields.push(...this.collectUserFields(schemaElement.schema));
                    return fields;
                }

                if (schemaElement?.element === 'field' && !schemaElement.is_system) {
                    fields.push(schemaElement);
                }

                return fields;
            }, []);
        },
        getFieldEditorComponent(layoutField) {
            const jsModule = getFieldEditorModulePath(layoutField);
            if (!jsModule) {
                return null;
            }

            return this.getValueEditorComponent(jsModule);
        },
        getValueEditorComponent(jsModule) {
            const component = loadComponent(jsModule, {
                errorMessage: this.$t('editor_component_not_found'),
                loadingMessage: this.$t('editor_component_loading'),
            });
            if (!component) {
                console.error('Failed to load component:', jsModule);
            }
            return component;
        },
        getFieldEditorProps(layoutField) {
            const props = {
                id: `field-${layoutField.key}`,
                name: layoutField.key,
                media: this.getMediaForField(layoutField.id),
                configuration: layoutField.configuration || {},
                isRequired: layoutField.is_required,
                resourceId: this.getCurrentResourceId(),
                resourceFieldId: layoutField.id,
            };

            return props;
        },
        getMediaForField(resourceFieldId) {
            const value = this.valuesMap[resourceFieldId];
            return value?.media || null;
        },
        getCurrentResourceId() {
            const resourceId = this.$route.params.id;

            if (!resourceId || resourceId === 'new') {
                return null;
            }

            return Number.parseInt(resourceId, 10) || null;
        },
        async loadResource() {
            try {
                this.loading = true;
                this.error = null;
                const resourceId = this.$route.params.id;
                
                // Если это новый ресурс, вызываем createDraft для получения данных из родителя
                if (resourceId === 'new') {
                    const parentId = this.$route.query.parent_id;
                    const contextId = this.$route.query.context_id;
                    const response = await makeDraftResource(
                        parentId ? parseInt(parentId, 10) : null,
                        contextId ? parseInt(contextId, 10) : null,
                    );
                    
                    // Laravel Resource может обернуть ответ в data
                    const initData = response.data || response;

                    this.applyResource(initData);

                    if (this.availableLayouts.length > 0) {
                        await this.applyLayoutAndPopulateValues();
                    } else {
                        this.form.values = {};
                    }

                    return;
                }
                
                const response = await getResource(resourceId);
                this.applyResource(response.data || response);

                if (this.availableLayouts.length > 0) {
                    await this.applyLayoutAndPopulateValues();
                } else {
                    this.form.values = {};
                }
            } catch (error) {
                console.error('Error loading resource:', error);
                this.error = this.$t('error_loading_resource');
            } finally {
                this.loading = false;
            }
        },
        async loadLayouts() {
            try {
                const response = await getResourceLayouts();

                const layouts = response.data || response || [];
                this.availableLayouts = Array.isArray(layouts) ? layouts : [];
                
                // Если у ресурса нет макета, устанавливаем макет по умолчанию
                if (!this.form.resource_layout_id && this.availableLayouts.length > 0) {
                    // Ищем макет по умолчанию
                    const defaultLayout = this.availableLayouts.find(layout => layout.is_default);
                    if (defaultLayout) {
                        this.form.resource_layout_id = defaultLayout.id;
                    } else if (this.availableLayouts.length === 1) {
                        // Если макет только один, используем его
                        this.form.resource_layout_id = this.availableLayouts[0].id;
                    }
                }

                await this.applyLayoutAndPopulateValues();
                await this.syncCatalogFocus();
            } catch (error) {
                console.error('Error loading layouts:', error);
            }
        },
        getCatalogIdFromRouteQuery() {
            const rawCatalogId = this.$route.query.catalog_id;
            const parsedCatalogId = Number.parseInt(String(rawCatalogId ?? ''), 10);

            return Number.isNaN(parsedCatalogId) ? null : parsedCatalogId;
        },
        async syncCatalogFocus() {
            const catalogIdFromQuery = this.getCatalogIdFromRouteQuery();
            this.resolvedCatalogId = catalogIdFromQuery;
        },
        getCatalogReturnResourceId(resourceId = null) {
            const parentId = Number.parseInt(String(this.resource?.parent_id ?? this.form.parent_id ?? ''), 10);
            if (!Number.isNaN(parentId) && parentId > 0) {
                return parentId;
            }

            if (this.resource?.is_folder === true) {
                const currentId = resourceId ?? this.getCurrentResourceId();
                if (currentId !== null) {
                    return currentId;
                }
            }

            return this.resolvedCatalogId;
        },
        goToCatalog(resourceId = null) {
            if (this.resolvedCatalogId === null || !this.$router) {
                return;
            }

            const adminPrefix = getAdminPrefix();
            const path = `/${adminPrefix}/resources/catalog/${this.resolvedCatalogId}`;
            const targetResourceId = this.getCatalogReturnResourceId(resourceId);
            const query = targetResourceId !== null && targetResourceId !== this.resolvedCatalogId
                ? { resource_id: String(targetResourceId) }
                : {};

            this.$router.push({ path, query });
        },
        async onLayoutChange() {
            if (!this.form.resource_layout_id) {
                return;
            }

            await this.applyLayoutAndPopulateValues();
        },
        async handleSubmit() {
            try {
                this.saving = true;
                this.error = null;
                this.successMessage = null;

                // Преобразуем form.values из объекта в массив для отправки на сервер
                const submitData = {
                    ...this.form,
                    values: this.prepareValuesForSubmit(),
                };

                const resourceId = this.$route.params.id;
                
                // Если это новый ресурс, создаем его
                if (resourceId === 'new') {
                    // Используем обязательные параметры из form
                    if (!this.form.context_id || !this.form.resource_type_id) {
                        this.error = this.$t('error_saving_resource');
                        return;
                    }
                    
                    submitData.context_id = this.form.context_id;
                    submitData.resource_type_id = this.form.resource_type_id;
                    if (this.form.parent_id) {
                        submitData.parent_id = this.form.parent_id;
                    }
                    
                    const response = await createResource(submitData);
                    const newResourceId = response.data?.id || response.id;
                    
                    // Обновляем дерево ресурсов
                    this.refreshResourceTree();
                    
                    // Показываем сообщение об успехе
                    this.successMessage = this.$t('resource_updated');

                    if (this.resolvedCatalogId !== null) {
                        this.goToCatalog(newResourceId);
                        return;
                    }

                    // Перенаправляем на страницу редактирования нового ресурса
                    const adminPrefix = getAdminPrefix();
                    this.$router.push(`/${adminPrefix}/resources/${newResourceId}`);
                } else {
                    // Обновляем существующий ресурс
                    await updateResource(resourceId, submitData);

                    // Обновляем дерево ресурсов
                    this.refreshResourceTree();

                    // Показываем сообщение об успехе
                    this.successMessage = this.$t('resource_updated');

                    if (this.resolvedCatalogId !== null) {
                        this.goToCatalog(Number.parseInt(String(resourceId), 10));
                        return;
                    }

                    // Перезагружаем ресурс для отображения обновленных данных
                    await this.loadResource();
                }
            } catch (error) {
                console.error('Error saving resource:', error);
                
                // Обработка ошибок валидации
                if (error.response?.status === 422 && error.response?.data?.errors) {
                    // Формируем сообщение об ошибках валидации
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
                    this.error = error.response?.data?.message || this.$t('error_saving_resource');
                }
            } finally {
                this.saving = false;
            }
        },
        prepareValuesForSubmit() {
            // Преобразуем form.values из объекта в массив для отправки на сервер
            if (Array.isArray(this.form.values)) {
                return this.form.values;
            }

            const result = [];
            // Используем computed свойство layoutFields для получения нормализованных данных
            const layoutFieldsArray = this.layoutFields;
            
            if (layoutFieldsArray && layoutFieldsArray.length > 0) {
                layoutFieldsArray.forEach(layoutField => {
                    const resourceFieldKey = layoutField.key;
                    const resourceFieldId = layoutField.id;
                    
                    // Добавляем значение, если оно есть в form.values (даже пустое, но не undefined)
                    if (resourceFieldId && resourceFieldKey && this.form.values.hasOwnProperty(resourceFieldKey)) {
                        result.push({
                            resource_field_id: resourceFieldId,
                            value: this.form.values[resourceFieldKey] !== undefined ? this.form.values[resourceFieldKey] : null,
                        });
                    }
                });
            }
            
            return result;
        },
        refreshResourceTree() {
            // Отправляем событие для обновления дерева ресурсов
            this.$eventBus.emit('refresh-resource-tree');
        },
        goBack() {
            if (this.resolvedCatalogId !== null) {
                this.goToCatalog();
                return;
            }

            const adminPrefix = getAdminPrefix();
            this.$router.push(`/${adminPrefix}`);
        },
        async loadPlugins() {
            try {
                const pluginsData = await getResourceEditPlugins();
                this.plugins = await loadPlugins(pluginsData, this);
            } catch (error) {
                console.error('Ошибка загрузки плагинов:', error);
            }
        },
    },
};
</script>
