<template>
    <div 
        class="tree-node"
        :class="{ 
            'dragging': isDragging,
            'drag-over': isDragOver,
            'drag-over-top': dragOverPosition === 'top',
            'drag-over-bottom': dragOverPosition === 'bottom',
            'drag-over-inside': dragOverPosition === 'inside'
        }"
    >
        <div 
            class="node-item" 
            :class="{ 'has-children': hasChildren || resource.is_folder, 'is-active': isActive }" 
            :draggable="true"
            @dragstart="handleDragStart"
            @dragend="handleDragEnd"
            @dragover.prevent="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDrop"
            @click="handleNodeClick"
            @mouseleave="handleMouseLeave"
        >
            <span class="node-icon" @click.stop="handleToggleExpanded">
                <Icon v-if="resource.is_home" name="house" :size="16" />
                <Icon v-else-if="resource.layout && resource.layout.is_catalog" name="layout-list" :size="16" />
                <Icon v-else-if="!hasChildren && !resource.is_folder" name="file-text" :size="16" />
                <Icon v-else-if="isExpanded && (hasLoadedChildren || childrenLoaded)" name="chevron-down" :size="16" />
                <Icon v-else name="chevron-right" :size="16" />
            </span>
            <span class="node-label" @click.stop="handleLabelClick" :title="(resource.title || resource.slug || $t('no_title')) + ' (' + resource.id + ')'">
                {{ resource.title || resource.slug || $t('no_title') }}
            </span>
            <div class="node-actions" @click.stop>
                <div class="dropdown" :class="{ 'dropdown-open': isMenuOpen }">
                    <button 
                        class="btn-menu" 
                        :title="$t('actions')"
                        @click="toggleMenu"
                    >
                        ⋮
                    </button>
                    <div class="dropdown-menu" v-if="isMenuOpen" @click.stop @mouseenter="handleMenuEnter" @mouseleave="handleMenuLeave">
                        <button 
                            v-if="allowChildren"
                            class="dropdown-item" 
                            @click="handleAddChild"
                        >
                            {{ $t('create_child_resource') }}
                        </button>
                        <button
                            v-if="!resource.is_home"
                            class="dropdown-item dropdown-item-danger"
                            @click="handleDelete"
                        >
                            {{ $t('delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="node-children" v-if="isExpanded && (resource.layout && !resource.layout.is_catalog) && (hasChildren || resource.is_folder)">
            <div v-if="loadingChildren" class="loading-children">
                {{ $t('loading') }}
            </div>
            <template v-else>
                <ResourceTreeNode
                    v-for="child in (resource.children || [])"
                    :key="child.id"
                    :resource="child"
                    :depth="depth + 1"
                    :active-resource-id="activeResourceId"
                    :expanded-resources="expandedResources"
                    :dragged-resource-id="draggedResourceId"
                    @toggle-expanded="$emit('toggle-expanded', $event)"
                    @resource-moved="$emit('resource-moved', $event)"
                    @resource-deleted="$emit('resource-deleted', $event)"
                    @drag-start="$emit('drag-start', $event)"
                    @drag-end="handleChildDragEnd"
                    @move-request="$emit('move-request', $event)"
                />
            </template>
        </div>
        
        <LayoutSelectionModal
            v-if="showLayoutModal"
            :show="showLayoutModal"
            :layouts="availableLayouts"
            :parent-id="resource.id"
            @close="showLayoutModal = false"
        />
    </div>
</template>

<script>
import { getAdminPrefix } from '../../api';
import { getResourceChildren, deleteResource } from '../../api/resources';
import LayoutSelectionModal from './LayoutSelectionModal.vue';
import Icon from "../common/Icon.vue";

export default {
    name: 'ResourceTreeNode',
    components: {
        Icon,
        LayoutSelectionModal,
    },
    props: {
        resource: {
            type: Object,
            required: true,
        },
        depth: {
            type: Number,
            default: 0,
        },
        expandedResources: {
            type: Array,
            default: () => [],
        },
        activeResourceId: {
            type: Number,
            default: null,
        },
        draggedResourceId: {
            type: Number,
            default: null,
        },
    },
    data() {
        return {
            isDragging: false,
            isDragOver: false,
            dragOverPosition: null, // 'top', 'bottom', 'inside'
            dragStartTime: null,
            childrenLoaded: false,
            loadingChildren: false,
            isMenuOpen: false,
            showLayoutModal: false,
            availableLayouts: [],
        };
    },
    computed: {
        hasChildren() {
            // Проверяем is_folder для показа иконки раскрытия
            // И наличие загруженных детей для отображения
            return this.resource.is_folder === true;
        },
        hasLoadedChildren() {
            // Проверяем наличие реально загруженных детей
            return this.resource.children && Array.isArray(this.resource.children) && this.resource.children.length > 0;
        },
        isExpanded() {
            return this.expandedResources.includes(this.resource.id);
        },
        isActive() {
            return this.activeResourceId !== null && this.resource.id === this.activeResourceId;
        },
        allowChildren() {
            return this.resource.layout?.allow_children !== false;
        },
    },
    mounted() {
        // Закрываем меню при клике вне его
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },
    methods: {
        async handleToggleExpanded(event) {
            event.stopPropagation();
            if (this.resource.layout?.is_catalog) {
                this.navigateToCatalog();
                return;
            }

            if (!this.hasChildren) {
                return;
            }

            const isCurrentlyExpanded = this.isExpanded;
            
            // Эмитим событие для обновления состояния в родительском компоненте
            this.$emit('toggle-expanded', this.resource.id);

            // Если узел открывается и дети еще не загружены, загружаем их
            // Дети могут быть уже загружены при первоначальной загрузке дерева через getResourcesTree
            if (!isCurrentlyExpanded && this.resource.is_folder) {
                // Проверяем, есть ли уже загруженные дети
                if (this.resource.children && Array.isArray(this.resource.children) && this.resource.children.length > 0) {
                    // Дети уже загружены при первоначальной загрузке
                    this.childrenLoaded = true;
                } else if (!this.childrenLoaded) {
                    // Дети не загружены, загружаем их
                    await this.loadChildren();
                }
            }
        },
        async loadChildren() {
            if (this.loadingChildren || this.childrenLoaded) {
                return;
            }

            this.loadingChildren = true;
            try {
                const response = await getResourceChildren(this.resource.id);

                // ResourceTreeResource::collection возвращает { data: [...] }
                // axios уже извлек response.data, поэтому response уже содержит { data: [...] }
                const children = (response && response.data && Array.isArray(response.data)) 
                    ? response.data 
                    : (Array.isArray(response) ? response : []);

                // Обновляем children в ресурсе реактивно
                // В Vue 3 реактивность работает автоматически, $set не нужен
                this.resource.children = children;
                this.childrenLoaded = true;
            } catch (error) {
                console.error('Failed to load child resources:', error);
                // Устанавливаем пустой массив в случае ошибки
                this.resource.children = [];
            } finally {
                this.loadingChildren = false;
            }
        },
        handleLabelClick(event) {
            event.stopPropagation();
            this.navigateToResource();
        },
        handleNodeClick(event) {
            // Если это был drag (перемещение началось), не обрабатываем клик
            if (this.isDragging || (this.dragStartTime && Date.now() - this.dragStartTime < 200)) {
                return;
            }
            // Если клик был на иконке или кнопке действий, не обрабатываем
            if (event.target.closest('.node-icon') || event.target.closest('.node-actions')) {
                return;
            }
            // Открываем страницу редактирования при клике на узел
            this.navigateToResource();
        },
        navigateToResource() {
            if (this.resource.layout?.is_catalog) {
                this.navigateToCatalog();
                return;
            }

            // Открываем страницу редактирования
            try {
                const adminPrefix = getAdminPrefix();
                if (this.$router) {
                    if (this.$route?.name === 'resource-catalog') {
                        const catalogId = Number.parseInt(String(this.$route.params.catalogId ?? ''), 10);

                        if (!Number.isNaN(catalogId)) {
                            this.$router.push({
                                path: `/${adminPrefix}/resources/${this.resource.id}`,
                                query: { catalog_id: String(catalogId) },
                            });

                            return;
                        }
                    }

                    this.$router.push(`/${adminPrefix}/resources/${this.resource.id}`);
                } else {
                    console.error('Router is not available');
                }
            } catch (error) {
                console.error('Error navigating to resource:', error);
            }
        },
        navigateToCatalog() {
            try {
                const adminPrefix = getAdminPrefix();
                if (this.$router) {
                    this.$router.push({
                        path: `/${adminPrefix}/resources/catalog/${this.resource.id}`,
                    });
                } else {
                    console.error('Router is not available');
                }
            } catch (error) {
                console.error('Error navigating to catalog:', error);
            }
        },
        handleDragStart(event) {
            this.isDragging = true;
            this.dragStartTime = Date.now();
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', this.resource.id.toString());
            // Сохраняем ID в глобальном хранилище для доступа из других компонентов
            this.$emit('drag-start', this.resource.id);
        },
        handleDragEnd(event) {
            this.isDragging = false;
            this.isDragOver = false;
            this.dragOverPosition = null;
            this.dragStartTime = null;
            // Сбрасываем состояние всех узлов
            this.$emit('drag-end');
        },
        handleChildDragEnd() {
            // Сбрасываем состояние и передаем событие дальше
            this.resetDragState();
            this.$emit('drag-end');
        },
        resetDragState() {
            // Метод для сброса состояния при получении события drag-end от родителя
            this.isDragOver = false;
            this.dragOverPosition = null;
        },
        handleDragOver(event) {
            // Останавливаем всплытие, чтобы не срабатывало на дочерних элементах
            event.stopPropagation();
            
            // Получаем ID перетаскиваемого ресурса из props или события
            const draggedId = this.draggedResourceId || parseInt(event.dataTransfer.getData('text/plain'));
            if (!draggedId || draggedId === this.resource.id) {
                this.isDragOver = false;
                this.dragOverPosition = null;
                return;
            }
            
            // Устанавливаем dropEffect для визуальной обратной связи
            event.dataTransfer.dropEffect = 'move';
            
            this.isDragOver = true;
            
            const rect = event.currentTarget.getBoundingClientRect();
            const y = event.clientY - rect.top;
            const height = rect.height;
            // Уменьшаем порог для более точного определения позиции
            const threshold = Math.max(height / 4, 10);
            
            if (y < threshold) {
                this.dragOverPosition = 'top';
            } else if (y > height - threshold) {
                this.dragOverPosition = 'bottom';
            } else {
                this.dragOverPosition = 'inside';
            }
        },
        handleDragLeave(event) {
            // Останавливаем всплытие
            event.stopPropagation();
            
            // Проверяем, что мы действительно покинули элемент
            const rect = event.currentTarget.getBoundingClientRect();
            const x = event.clientX;
            const y = event.clientY;
            
            // Если курсор вышел за границы элемента, сбрасываем состояние
            if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
                this.isDragOver = false;
                this.dragOverPosition = null;
            }
            
            // Также проверяем, что мы не переходим на дочерний элемент
            const relatedTarget = event.relatedTarget;
            if (relatedTarget && !event.currentTarget.contains(relatedTarget)) {
                this.isDragOver = false;
                this.dragOverPosition = null;
            }
        },
        handleDrop(event) {
            event.preventDefault();
            event.stopPropagation();
            
            // Используем ID из props (переданный от родителя) или из dataTransfer
            const draggedId = this.draggedResourceId || parseInt(event.dataTransfer.getData('text/plain'));
            
            if (!draggedId || draggedId === this.resource.id) {
                this.isDragOver = false;
                this.dragOverPosition = null;
                return;
            }
            
            // Определяем позицию на основе текущего состояния или вычисляем заново
            let position = this.dragOverPosition;
            
            // Если позиция не определена, вычисляем её на основе координат курсора
            if (!position) {
                const rect = event.currentTarget.getBoundingClientRect();
                const y = event.clientY - rect.top;
                const height = rect.height;
                const threshold = Math.max(height / 4, 10);
                
                if (y < threshold) {
                    position = 'top';
                } else if (y > height - threshold) {
                    position = 'bottom';
                } else {
                    position = 'inside';
                }
            }
            
            // Определяем новый parent_id и sort_order
            let newParentId = null;
            let newSortOrder = 0;
            
            if (position === 'inside') {
                // Проверяем, разрешено ли добавление детей
                if (!this.allowChildren) {
                    this.isDragOver = false;
                    this.dragOverPosition = null;
                    alert(this.$t('cannot_add_children_to_resource'));
                    return;
                }
                // Перемещаем внутрь этого ресурса
                newParentId = this.resource.id;
                const childrenCount = (this.resource.children && this.resource.children.length) || 0;
                newSortOrder = childrenCount;
            } else if (position === 'top') {
                // Перемещаем перед этим ресурсом (тот же родитель)
                // parent_id может быть null для корневых элементов
                newParentId = this.resource.parent_id !== undefined ? this.resource.parent_id : null;
                newSortOrder = (this.resource.sort_order || 1) - 1;
            } else if (position === 'bottom') {
                // Перемещаем после этого ресурса (тот же родитель)
                // parent_id может быть null для корневых элементов
                newParentId = this.resource.parent_id !== undefined ? this.resource.parent_id : null;
                newSortOrder = (this.resource.sort_order || 0) + 1;
            } else {
                // Если позиция все еще не определена, используем inside по умолчанию
                if (!this.allowChildren) {
                    this.isDragOver = false;
                    this.dragOverPosition = null;
                    alert(this.$t('cannot_add_children_to_resource'));
                    return;
                }
                newParentId = this.resource.id;
                const childrenCount = (this.resource.children && this.resource.children.length) || 0;
                newSortOrder = childrenCount;
                position = 'inside';
            }
            
            // Убеждаемся, что parent_id явно установлен (может быть null для корня)
            // Преобразуем undefined в null для корректной передачи на сервер
            if (newParentId === undefined) {
                newParentId = null;
            }
            
            // Сбрасываем состояние
            this.isDragOver = false;
            this.dragOverPosition = null;

            this.$emit('move-request', {
                draggedId,
                newParentId,
                newSortOrder,
                position,
                targetResource: this.resource,
            });
        },
        toggleMenu(event) {
            event.stopPropagation();
            this.isMenuOpen = !this.isMenuOpen;
        },
        async handleAddChild(event) {
            event.stopPropagation();
            this.isMenuOpen = false;
            
            try {
                const adminPrefix = getAdminPrefix();
                if (!this.$router) {
                    console.error('Router is not available');
                    return;
                }
                
                if (!this.resource.layout || !this.resource.layout.children_layouts) {
                    this.$router.push({
                        path: `/${adminPrefix}/resources/new`,
                        query: { parent_id: this.resource.id }
                    });
                    return;
                }

                // Если макет один - сразу передаем layout_id
                if (this.resource.layout.children_layouts.length === 1) {
                    this.$router.push({
                        path: `/${adminPrefix}/resources/new`,
                        query: { parent_id: this.resource.id, layout_id: this.resource.layout.children_layouts[0].id }
                    });
                    return;
                }
                
                this.availableLayouts = this.resource.layout.children_layouts;
                this.showLayoutModal = true;
            } catch (error) {
                console.error('Error navigating to create resource:', error);
            }
        },
        handleClickOutside(event) {
            if (this.isMenuOpen && !this.$el.contains(event.target)) {
                this.isMenuOpen = false;
            }
        },
        handleMouseLeave(event) {
            // Закрываем меню при уходе курсора с элемента ресурса
            // Проверяем, что курсор не переходит на меню
            const relatedTarget = event.relatedTarget;
            if (relatedTarget && this.$el.querySelector('.dropdown-menu')?.contains(relatedTarget)) {
                return; // Курсор переходит на меню, не закрываем
            }
            this.isMenuOpen = false;
        },
        handleMenuEnter() {
            // При входе курсора на меню ничего не делаем, меню остается открытым
        },
        handleMenuLeave(event) {
            // Закрываем меню при уходе курсора с меню
            const relatedTarget = event.relatedTarget;
            // Проверяем, что курсор не переходит обратно на элемент ресурса
            if (relatedTarget && this.$el.querySelector('.node-item')?.contains(relatedTarget)) {
                return; // Курсор переходит на элемент, не закрываем
            }
            this.isMenuOpen = false;
        },
        async handleDelete(event) {
            event.stopPropagation();
            this.isMenuOpen = false;
            
            if (!confirm(this.$t('confirm_delete_resource'))) {
                return;
            }
            
            try {
                await deleteResource(this.resource.id);

                // Эмитим событие для обновления дерева в родительском компоненте
                this.$emit('resource-deleted', {
                    resourceId: this.resource.id,
                    branchIds: this.collectLoadedBranchIds(this.resource),
                });
            } catch (error) {
                console.error('Error deleting resource:', error);
                const message = error.response?.data?.message || this.$t('error_deleting_resource');
                alert(message);
            }
        },
        collectLoadedBranchIds(resource) {
            const branchIds = [];

            const traverse = (node) => {
                if (!node || typeof node.id !== 'number') {
                    return;
                }

                branchIds.push(node.id);

                if (!Array.isArray(node.children)) {
                    return;
                }

                for (const child of node.children) {
                    traverse(child);
                }
            };

            traverse(resource);

            return branchIds;
        },
    },
};
</script>

<style scoped>
.tree-node {
    margin: 0;
    user-select: none;
    position: relative;
    padding-left: 1rem;
}

.tree-node.dragging {
    opacity: 0.5;
}

.tree-node.drag-over-top {
    position: relative;
}

.tree-node.drag-over-top::before {
    content: '';
    position: absolute;
    top: -2px;
    left: 0;
    right: 0;
    height: 4px;
    background-color: #3b82f6;
    z-index: 1000;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.8);
    border-radius: 2px;
}

.tree-node.drag-over-bottom {
    position: relative;
}

.tree-node.drag-over-bottom::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 4px;
    background-color: #3b82f6;
    z-index: 1000;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.8);
    border-radius: 2px;
}

.tree-node.drag-over-inside > .node-item {
    background-color: #dbeafe !important;
    outline: 2px dashed #3b82f6 !important;
    border-radius: 4px;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.3);
}

.tree-node.drag-over-top > .node-item,
.tree-node.drag-over-bottom > .node-item {
    background-color: #eff6ff;
}

.tree-node:before {
    content: '';
    position: absolute;
    left: 1.125rem;
    top: 0;
    height: 100%;
    border-left: 1px dashed #ccc;
}

.node-item {
    display: flex;
    align-items: center;
    padding: 0 0.75rem 0 0.5rem;
    cursor: pointer;
    transition: background-color 0.15s, border 0.15s;
    font-size: 0.875rem;
    color: #374151;
    min-height: 28px;
    line-height: 1.5;
    position: relative;
    border: 1px solid transparent;
}

.node-item:hover .node-actions {
    opacity: 1;
}

.node-item:before {
    content: '';
    position: absolute;
    left: 0.125rem;
    top: 50%;
    width: 0.375rem;
    border-top: 1px dashed #ccc;
}

.tree-node:last-child:before {
    height: 0.9375rem;
}

.node-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
    cursor: pointer;
}

.node-label {
    font-size: 0.875rem;
    color: #374151;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 0 0.375rem 0 0.25rem;
}

.node-item.is-active .node-label {
    background: #e7e7e7;
    border-radius: 3px;
}

.node-actions {
    display: flex;
    align-items: center;
    margin-left: auto;
    opacity: 0;
    transition: opacity 0.15s;
    position: relative;
}

.dropdown {
    position: relative;
}

.btn-menu {
    background: transparent;
    color: #6b7280;
    border: none;
    border-radius: 4px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
    padding: 0;
    transition: background-color 0.15s, color 0.15s;
    line-height: 1;
}

.btn-menu:hover {
    background: #f3f4f6;
    color: #374151;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    min-width: 180px;
    z-index: 1000;
    padding: 4px 0;
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 8px 12px;
    text-align: left;
    background: none;
    border: none;
    color: #374151;
    font-size: 0.875rem;
    cursor: pointer;
    transition: background-color 0.15s;
}

.dropdown-item:hover {
    background-color: #f3f4f6;
}

.dropdown-item-danger {
    color: #dc2626;
}

.dropdown-item-danger:hover {
    background-color: #fee2e2;
    color: #991b1b;
}

.node-children {
    margin-left: 0;
}

.loading-children {
    padding: 0.5rem 1rem;
    color: #6b7280;
    font-size: 0.875rem;
    font-style: italic;
}
</style>

