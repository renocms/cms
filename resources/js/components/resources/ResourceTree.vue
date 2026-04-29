<template>
    <div class="resource-tree">
        <div class="tree-header">
            <select v-model="selectedContext" @change="loadResources" class="context-select">
                <option v-for="context in contexts" :key="context.id" :value="context.id">
                    {{ context.name }}
                </option>
            </select>
            <div class="tree-header-right">
                <span
                    v-if="selectedContext"
                    class="tree-create-root"
                    :title="$t('create_root_resource')"
                    :aria-label="$t('create_root_resource')"
                    @click="handleCreateRootResource"
                >
                    <Icon name="file-plus-corner" :size="18" />
                </span>
            </div>
        </div>
        <div class="tree-content" v-if="resources.length === 0 && !loading">
            <p class="empty-message">{{ $t('no_resources') }}</p>
        </div>
        <div 
            class="tree-content" 
            v-else
            @dragover.prevent="handleTreeDragOver"
            @dragleave="handleTreeDragLeave"
            @drop="handleTreeDrop"
            :class="{ 'drag-over-root': isDragOverRoot }"
        >
            <ResourceTreeNode
                v-for="resource in resources"
                :key="resource.id"
                :resource="resource"
                :depth="0"
                :active-resource-id="activeResourceId"
                :expanded-resources="expandedResourceIds"
                :dragged-resource-id="draggedResourceId"
                @toggle-expanded="handleToggleExpanded"
                @resource-moved="handleResourceMoved"
                @resource-deleted="handleResourceDeleted"
                @drag-start="handleDragStart"
                @drag-end="handleDragEnd"
                @move-request="handleMoveRequest"
            />
        </div>
        
        <!-- Индикатор загрузки -->
        <LoadingOverlay :show="loading" />

        <MoveResourceConfirmModal
            :show="showMoveConfirmModal"
            :source-title="moveModalSourceTitle"
            :destination-line="moveModalDestinationLine"
            :confirming="moveConfirmLoading"
            @close="cancelPendingMove"
            @confirm="confirmPendingMove"
        />
    </div>
</template>

<script>
import { getResourcesTree, getContexts, getAdminPrefix } from '../../api';
import { moveResource } from '../../api/resources';
import ResourceTreeNode from './ResourceTreeNode.vue';
import LoadingOverlay from '../common/LoadingOverlay.vue';
import MoveResourceConfirmModal from './MoveResourceConfirmModal.vue';
import Icon from '../common/Icon.vue';

export default {
    name: 'ResourceTree',
    components: {
        ResourceTreeNode,
        LoadingOverlay,
        MoveResourceConfirmModal,
        Icon,
    },
    data() {
        return {
            contexts: [],
            selectedContext: null,
            previousContext: null, // Предыдущий контекст для сохранения состояния
            resources: [],
            loading: false,
            expandedResourceIds: [], // Массив открытых ID ресурсов
            isDragOverRoot: false,
            draggedResourceId: null,
            isMoving: false,
            showMoveConfirmModal: false,
            moveModalSourceTitle: '',
            moveModalDestinationLine: '',
            pendingMove: null,
            moveConfirmLoading: false,
        };
    },
    async mounted() {
        // Загружаем контексты и ресурсы
        await this.loadContexts();
        
        // Слушаем событие обновления дерева
        this.$eventBus.on('refresh-resource-tree', this.loadResources);
    },
    beforeUnmount() {
        // Отписываемся от события при уничтожении компонента
        this.$eventBus.off('refresh-resource-tree', this.loadResources);
    },
    methods: {
        async loadContexts() {
            try {
                const response = await getContexts();
                this.contexts = response.data || [];
                if (this.contexts.length > 0 && !this.selectedContext) {
                    this.selectedContext = this.contexts[0].id;
                    await this.loadResources();
                }
            } catch (error) {
                console.error(this.$t('error_loading_contexts'), error);
            }
        },
        async loadResources() {
            if (!this.selectedContext) {
                return;
            }
            
            // Если контекст изменился, сохраняем состояние для предыдущего контекста
            if (this.previousContext !== null && this.previousContext !== this.selectedContext) {
                this.saveExpandedResources(this.previousContext);
            }
            
            // Загружаем состояние для нового контекста
            this.loadExpandedResources(this.selectedContext);
            
            // Обновляем предыдущий контекст
            this.previousContext = this.selectedContext;
            
            this.loading = true;
            try {
                // Передаем expandedResourceIds на сервер, чтобы загрузить все открытые ресурсы и их детей
                // Сервер вернет корневые ресурсы + открытые ресурсы + их детей в одном запросе
                const response = await getResourcesTree(this.selectedContext, this.expandedResourceIds);
                this.resources = response.data || [];
                
                // Синхронизируем expandedResourceIds с фактическим состоянием загруженного дерева
                // Это гарантирует, что состояние соответствует реально раскрытым ресурсам
                this.$nextTick(() => {
                    this.syncExpandedResourcesFromTree();
                });
            } catch (error) {
                console.error(this.$t('error_loading_resources'), error);
                this.resources = [];
            } finally {
                this.loading = false;
            }
        },
        handleToggleExpanded(resourceId) {
            const index = this.expandedResourceIds.indexOf(resourceId);
            if (index > -1) {
                this.expandedResourceIds.splice(index, 1);
            } else {
                this.expandedResourceIds.push(resourceId);
            }
            // Сохраняем состояние для текущего контекста
            this.saveExpandedResources();
        },
        /**
         * Получить ключ localStorage для состояния раскрытых ресурсов контекста
         * @param {number} contextId - ID контекста
         * @returns {string} Ключ localStorage
         */
        getExpandedResourcesKey(contextId) {
            return `cms_expanded_resources_${contextId}`;
        },
        /**
         * Загрузить состояние раскрытых ресурсов для контекста
         * @param {number} contextId - ID контекста
         */
        loadExpandedResources(contextId) {
            if (!contextId) {
                this.expandedResourceIds = [];
                return;
            }
            try {
                const key = this.getExpandedResourcesKey(contextId);
                const saved = localStorage.getItem(key);
                if (saved) {
                    const ids = JSON.parse(saved);
                    this.expandedResourceIds = Array.isArray(ids) ? ids : [];
                } else {
                    this.expandedResourceIds = [];
                }
            } catch (error) {
                console.error('Failed to load expanded nodes state:', error);
                this.expandedResourceIds = [];
            }
        },
        /**
         * Сохранить состояние раскрытых ресурсов для контекста
         * @param {number} contextId - ID контекста (если не указан, используется текущий)
         */
        saveExpandedResources(contextId) {
            const targetContextId = contextId || this.selectedContext;
            if (!targetContextId) {
                return;
            }
            try {
                const key = this.getExpandedResourcesKey(targetContextId);
                localStorage.setItem(key, JSON.stringify(this.expandedResourceIds));
            } catch (error) {
                console.error('Failed to save expanded nodes state:', error);
            }
        },
        restoreExpandedState() {
            // Состояние восстанавливается через props в ResourceTreeNode
            // Сервер уже вернул все открытые ресурсы и их детей через getResourcesTree
            // Дополнительная загрузка не требуется
        },
        /**
         * Синхронизировать expandedResourceIds с фактическим состоянием дерева
         * Определяет реально раскрытые ресурсы (имеющие загруженных детей) и обновляет состояние
         */
        syncExpandedResourcesFromTree() {
            const actualExpandedIds = [];
            
            /**
             * Рекурсивно проходит по дереву и собирает ID ресурсов с загруженными детьми
             * @param {Array} resources - массив ресурсов для обработки
             */
            const collectExpandedIds = (resources) => {
                if (!Array.isArray(resources)) {
                    return;
                }
                
                for (const resource of resources) {
                    // Проверяем, есть ли у ресурса загруженные дети И раскрыт ли он в данный момент
                    const hasLoadedChildren = resource.children && Array.isArray(resource.children) && resource.children.length > 0;
                    const isCurrentlyExpanded = this.expandedResourceIds.includes(resource.id);
                    
                    if (hasLoadedChildren && isCurrentlyExpanded) {
                        actualExpandedIds.push(resource.id);
                        // Рекурсивно обрабатываем детей
                        collectExpandedIds(resource.children);
                    }
                }
            };
            
            // Собираем ID всех раскрытых ресурсов из дерева
            collectExpandedIds(this.resources);
            
            // Обновляем expandedResourceIds фактическим состоянием
            this.expandedResourceIds = actualExpandedIds;
            
            // Сохраняем синхронизированное состояние в localStorage для текущего контекста
            this.saveExpandedResources();
        },
        handleDragStart(resourceId) {
            this.draggedResourceId = resourceId;
        },
        handleResourceMoved() {
            // Перезагружаем дерево после перемещения только один раз
            if (!this.isMoving) {
                this.isMoving = true;
                this.loadResources().finally(() => {
                    this.isMoving = false;
                });
            }
        },
        handleResourceDeleted(resourceId) {
            // Перезагружаем дерево после удаления ресурса
            this.loadResources();
        },
        handleDragEnd() {
            // Сбрасываем состояние drag во всех узлах
            this.isDragOverRoot = false;
            this.draggedResourceId = null;
            // Принудительно обновляем все узлы для сброса состояния
            this.$forceUpdate();
        },
        /**
         * @param {Object} payload
         * @param {number} payload.draggedId
         * @param {number|null} payload.newParentId
         * @param {number} payload.newSortOrder
         * @param {'root'|'inside'|'top'|'bottom'} payload.position
         * @param {Object|null} payload.targetResource
         */
        handleMoveRequest(payload) {
            const sourceTitle = this.getResourceDisplayTitleById(payload.draggedId);
            const destinationLine = this.buildMoveDestinationLine(payload);
            this.pendingMove = {
                draggedId: payload.draggedId,
                newParentId: payload.newParentId,
                newSortOrder: payload.newSortOrder,
            };
            this.moveModalSourceTitle = sourceTitle;
            this.moveModalDestinationLine = destinationLine;
            this.showMoveConfirmModal = true;
        },
        cancelPendingMove() {
            if (this.moveConfirmLoading) {
                return;
            }
            this.showMoveConfirmModal = false;
            this.pendingMove = null;
            this.moveModalSourceTitle = '';
            this.moveModalDestinationLine = '';
        },
        async confirmPendingMove() {
            if (!this.pendingMove || this.moveConfirmLoading) {
                return;
            }
            this.moveConfirmLoading = true;
            try {
                await moveResource(
                    this.pendingMove.draggedId,
                    this.pendingMove.newParentId,
                    this.pendingMove.newSortOrder
                );
                this.cancelPendingMove();
                this.handleResourceMoved();
            } catch (error) {
                console.error('Error moving resource:', error);
                alert(this.$t('error_moving_resource'));
            } finally {
                this.moveConfirmLoading = false;
            }
        },
        /**
         * @param {Array} resources
         * @param {number} id
         * @returns {Object|null}
         */
        findResourceInTree(resources, id) {
            if (!Array.isArray(resources)) {
                return null;
            }
            for (const resource of resources) {
                if (resource.id === id) {
                    return resource;
                }
                if (resource.children && resource.children.length > 0) {
                    const found = this.findResourceInTree(resource.children, id);
                    if (found) {
                        return found;
                    }
                }
            }
            return null;
        },
        /**
         * @param {Object|null} resource
         * @returns {string}
         */
        formatResourceTitle(resource) {
            if (!resource) {
                return '';
            }
            return resource.title || resource.slug || this.$t('no_title');
        },
        /**
         * @param {number} id
         * @returns {string}
         */
        getResourceDisplayTitleById(id) {
            const resource = this.findResourceInTree(this.resources, id);
            if (!resource) {
                return `${this.$t('no_title')} (#${id})`;
            }
            return this.formatResourceTitle(resource);
        },
        /**
         * @param {Object} payload
         * @returns {string}
         */
        buildMoveDestinationLine(payload) {
            if (payload.position === 'root') {
                return this.$t('move_resource_dest_root');
            }
            const name = this.formatResourceTitle(payload.targetResource);
            if (payload.position === 'inside') {
                return this.$t('move_resource_dest_inside', { name });
            }
            if (payload.position === 'top') {
                return this.$t('move_resource_dest_before', { name });
            }
            if (payload.position === 'bottom') {
                return this.$t('move_resource_dest_after', { name });
            }
            return '';
        },
        handleTreeDragOver(event) {
            // Проверяем, что перетаскивается ресурс
            const draggedId = this.draggedResourceId || event.dataTransfer.getData('text/plain');
            if (!draggedId) {
                return;
            }
            
            // Проверяем, что курсор находится в области корня дерева
            const rect = event.currentTarget.getBoundingClientRect();
            const y = event.clientY - rect.top;
            
            // Если курсор в верхней части (первые 50px), показываем индикатор
            if (y < 50) {
                this.isDragOverRoot = true;
            } else {
                this.isDragOverRoot = false;
            }
        },
        handleTreeDragLeave(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const x = event.clientX;
            const y = event.clientY;
            
            if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
                this.isDragOverRoot = false;
            }
        },
        handleCreateRootResource() {
            if (!this.selectedContext || !this.$router) {
                return;
            }

            const adminPrefix = getAdminPrefix();
            this.$router.push({
                path: `/${adminPrefix}/resources/new`,
                query: { context_id: String(this.selectedContext) },
            });
        },
        handleTreeDrop(event) {
            event.preventDefault();
            event.stopPropagation();
            
            this.isDragOverRoot = false;
            
            const draggedId = this.draggedResourceId || parseInt(event.dataTransfer.getData('text/plain'), 10);
            if (!draggedId) {
                return;
            }
            
            const maxSortOrder = this.resources.length > 0
                ? Math.max(...this.resources.map((r) => r.sort_order || 0)) + 1
                : 0;
            
            this.handleMoveRequest({
                draggedId,
                newParentId: null,
                newSortOrder: maxSortOrder,
                position: 'root',
                targetResource: null,
            });
        },
    },
    computed: {
        activeResourceId() {
            if (this.$route?.name === 'resource-catalog') {
                const catalogId = Number.parseInt(String(this.$route.params.catalogId ?? ''), 10);

                return Number.isNaN(catalogId) ? null : catalogId;
            }

            if (this.$route?.name === 'resource-edit') {
                const fromCatalogId = Number.parseInt(String(this.$route.query.catalog_id ?? ''), 10);

                if (!Number.isNaN(fromCatalogId)) {
                    return fromCatalogId;
                }

                const resourceId = Number.parseInt(String(this.$route.params.id ?? ''), 10);

                return Number.isNaN(resourceId) ? null : resourceId;
            }

            return null;
        },
    },
};
</script>

<style scoped>
.resource-tree {
    height: 100%;
    background: #ffffff;
    display: flex;
    flex-direction: column;
}

.tree-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
}

.tree-header-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

.tree-create-root {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    padding: 0;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    background-color: #35678fdb;
    color: #fff;
}

.tree-create-root:hover {
    background-color: #35678f;
}

.tree-create-root :deep(svg) {
    flex-shrink: 0;
}

.tree-header h3 {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.context-select {
    padding: 0.375rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 0.875rem;
    background: white;
    color: #374151;
    cursor: pointer;
    width: 100%;
}

.context-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.tree-content {
    flex: 1;
    overflow-y: auto;
    padding: 0.25rem 0;
    background: #ffffff;
    position: relative;
}

.tree-content.drag-over-root::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background-color: #3b82f6;
    z-index: 10;
}

.empty-message {
    color: #6b7280;
    font-size: 0.875rem;
    text-align: center;
    padding: 2rem 0;
}
</style>

