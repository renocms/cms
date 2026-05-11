<template>
    <component
        :is="editComponent"
        v-if="editComponent"
        :key="$route.fullPath"
        :initial-resource="initialResource"
    />
    <LoadingOverlay :show="loading" />
</template>

<script>
import { markRaw } from 'vue';
import ResourceEdit from './ResourceEdit.vue';
import { getResource } from '../../api/resources';
import { loadComponent } from '../../utils/componentLoader';
import LoadingOverlay from '../common/LoadingOverlay.vue';

export default {
    name: 'ResourceEditWrapper',
    components: {
        LoadingOverlay,
    },
    data() {
        return {
            editComponent: null,
            initialResource: null,
            loading: true,
        };
    },
    async mounted() {
        await this.loadEditComponent();
    },
    watch: {
        '$route.params.id': {
            async handler() {
                await this.loadEditComponent();
            },
            immediate: false,
        },
    },
    methods: {
        async loadEditComponent() {
            try {
                this.loading = true;
                this.editComponent = null;
                this.initialResource = null;
                const resourceId = this.$route.params.id;
                
                let resource = null;
                
                if (!resourceId || resourceId === 'new') {
                    // Для нового ресурса загружаем данные через makeDraftResource
                    const { makeDraftResource } = await import('../../api/resources');
                    const parentId = this.$route.query.parent_id;
                    const contextId = this.$route.query.context_id;
                    const response = await makeDraftResource(
                        parentId ? parseInt(parentId, 10) : null,
                        contextId ? parseInt(contextId, 10) : null,
                    );
                    resource = response.data || response;
                } else {
                    const response = await getResource(resourceId);
                    resource = response.data;
                }

                this.initialResource = resource;

                if (resource && resource.js_module) {
                    // Загружаем кастомный компонент
                    const customComponent = loadComponent(resource.js_module, {
                        errorMessage: this.$t('editor_component_not_found'),
                        loadingMessage: this.$t('editor_component_loading'),
                    });
                    
                    if (customComponent) {
                        this.editComponent = markRaw(customComponent);
                    } else {
                        // Если не удалось загрузить кастомный компонент, используем стандартный
                        this.editComponent = markRaw(ResourceEdit);
                    }
                } else {
                    // Используем стандартный компонент
                    this.editComponent = markRaw(ResourceEdit);
                }
            } catch (error) {
                console.error('Error loading resource:', error);
                // В случае ошибки используем стандартный компонент
                this.editComponent = markRaw(ResourceEdit);
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
