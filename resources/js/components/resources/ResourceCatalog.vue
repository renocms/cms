<template>
    <div class="admin-page resource-catalog-page">
        <div class="page-header">
            <div>
                <h1>{{ catalogTitle }}</h1>
            </div>
            <div class="header-actions">
                <button
                    v-if="showCatalogBack"
                    type="button"
                    class="btn btn-secondary"
                    @click="goBackCatalog"
                >
                    {{ $t('back') }}
                </button>
                <button
                    v-if="canAddChildren"
                    type="button"
                    class="btn btn-primary"
                    @click="handleAddChild"
                >
                    {{ $t('create_child_resource') }}
                </button>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="openCatalogResource"
                >
                    {{ $t('edit_resource') }}
                </button>
            </div>
        </div>

        <ErrorNotification :message="error" @close="error = null" />

        <div v-if="!loading" class="admin-table-container">
            <table v-if="rows.length > 0" class="admin-table">
                <thead>
                    <tr>
                        <th
                            v-for="column in schema"
                            :key="column.key"
                            :style="columnWidthStyle(column)"
                        >
                            {{ column.label }}
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in rows"
                        :key="row.id"
                        class="catalog-row catalog-row--interactive"
                        @click="handleRowClick($event, row)"
                    >
                        <td
                            v-for="column in schema"
                            :key="`${row.id}-${column.key}`"
                            :style="columnWidthStyle(column)"
                        >
                            <template v-if="getCell(row, column.key).type === 'image'">
                                <img
                                    v-if="getCell(row, column.key).media?.url"
                                    :src="getCell(row, column.key).media.url"
                                    :alt="getCell(row, column.key).media.alt_text || row.title"
                                    class="catalog-image"
                                />
                                <span v-else class="catalog-empty-value">{{ emptyValue }}</span>
                            </template>
                            <template v-else>
                                {{ formatCellValue(getCell(row, column.key).value) }}
                            </template>
                        </td>
                        <td>
                            <div class="item-actions" @click.stop>
                                <span
                                    v-if="row.is_folder"
                                    :title="$t('open_catalog')"
                                    @click="openCatalogForRow(row.id)"
                                >
                                    <Icon name="folder-input" :size="16" />
                                </span>
                                <span
                                    v-if="row.allow_children"
                                    :title="$t('create_child_resource')"
                                    @click="handleAddChildForRow(row)"
                                >
                                    <Icon name="plus" :size="16" />
                                </span>
                                <span @click="openChildResource(row.id)">
                                    <Icon name="pencil" :size="16" />
                                </span>
                                <span
                                    v-if="!row.is_home"
                                    @click="removeChildResource(row.id)"
                                >
                                    <Icon name="trash-2" :size="16" />
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-else class="empty-state">
                {{ $t('no_child_resources') }}
            </div>
        </div>

        <PaginationControls
            :meta="meta"
            :loading="rowsLoading"
            @change-page="changePage"
        />

        <LayoutSelectionModal
            v-if="showLayoutModal"
            :show="showLayoutModal"
            :layouts="layoutModalLayouts"
            :parent-id="layoutModalParentId"
            @close="closeLayoutModal"
        />

        <LoadingOverlay :show="loading || rowsLoading" />
    </div>
</template>

<script>
import {deleteResource, getAdminPrefix} from '../../api';
import { getResourceCatalog, getResourceCatalogResources } from '../../api/resourceCatalog';
import LayoutSelectionModal from './LayoutSelectionModal.vue';
import LoadingOverlay from '../common/LoadingOverlay.vue';
import ErrorNotification from '../common/ErrorNotification.vue';
import PaginationControls from '../common/PaginationControls.vue';
import Icon from "../common/Icon.vue";

export default {
    name: 'ResourceCatalog',
    components: {
        Icon,
        ErrorNotification,
        LayoutSelectionModal,
        LoadingOverlay,
        PaginationControls,
    },
    data() {
        return {
            catalog: null,
            rows: [],
            loading: true,
            rowsLoading: false,
            error: null,
            showLayoutModal: false,
            layoutModalLayouts: [],
            layoutModalParentId: null,
            perPage: 20,
            meta: {
                current_page: 1,
                last_page: 1,
                per_page: 20,
                total: 0,
                from: null,
                to: null,
            },
        };
    },
    computed: {
        catalogId() {
            const param = this.$route.params.catalogId;

            if (param !== undefined && param !== null && param !== '') {
                const parsed = Number.parseInt(String(param), 10);

                return Number.isNaN(parsed) ? null : parsed;
            }

            const fromQuery = this.$route.query.catalog_id;

            if (fromQuery !== undefined && fromQuery !== null && fromQuery !== '') {
                const parsed = Number.parseInt(String(fromQuery), 10);

                return Number.isNaN(parsed) ? null : parsed;
            }

            const legacyOnly = this.$route.query.resource_id;

            if (legacyOnly !== undefined && legacyOnly !== null && legacyOnly !== '') {
                const parsed = Number.parseInt(String(legacyOnly), 10);

                return Number.isNaN(parsed) ? null : parsed;
            }

            return null;
        },
        currentResourceId() {
            const fromResource = this.$route.query.resource_id;

            if (fromResource !== undefined && fromResource !== null && fromResource !== '') {
                const parsed = Number.parseInt(String(fromResource), 10);

                return Number.isNaN(parsed) ? null : parsed;
            }

            return this.catalogId;
        },
        schema() {
            return Array.isArray(this.catalog?.schema) ? this.catalog.schema : [];
        },
        canAddChildren() {
            return this.catalog?.allow_children !== false;
        },
        catalogChildrenLayouts() {
            return Array.isArray(this.catalog?.children_layouts) ? this.catalog.children_layouts : [];
        },
        catalogTitle() {
            if (!this.catalog) {
                return this.$t('loading');
            }

            const label = this.catalog.label;
            const resource = this.catalog.resource;

            // Название папки добавляем только во вложенном каталоге (есть родитель).
            // Если папка и есть корень каталога — достаточно label.
            if (resource?.is_folder && resource.parent_id != null && resource.title) {
                return `${label} - ${resource.title}`;
            }

            return label;
        },
        showCatalogBack() {
            return this.catalog?.resource?.parent_id != null;
        },
        emptyValue() {
            return '-';
        },
    },
    async mounted() {
        await this.loadCatalogPage();
    },
    watch: {
        '$route.params.catalogId': {
            async handler() {
                await this.loadCatalogPage();
            },
            immediate: false,
        },
        '$route.query.resource_id': {
            async handler() {
                await this.loadCatalogPage();
            },
            immediate: false,
        },
        '$route.query.catalog_id': {
            async handler() {
                await this.loadCatalogPage();
            },
            immediate: false,
        },
    },
    methods: {
        columnWidthStyle(column) {
            if (!column?.width) {
                return undefined;
            }

            return { width: column.width };
        },
        async loadCatalogPage() {
            if (!this.catalogId || this.currentResourceId == null) {
                this.error = this.$t('error_loading_resource');
                this.catalog = null;
                this.rows = [];
                this.loading = false;
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                const response = await getResourceCatalog(this.catalogId, this.currentResourceId);
                this.catalog = response.data || response;
                await this.loadRows(1);
            } catch (error) {
                console.error('Error loading resource catalog:', error);
                this.error = error.response?.data?.message || this.$t('error_loading_resource');
                this.catalog = null;
                this.rows = [];
            } finally {
                this.loading = false;
            }
        },
        async loadRows(page = 1) {
            if (!this.catalogId || this.currentResourceId == null) {
                return;
            }

            this.rowsLoading = true;

            try {
                const response = await getResourceCatalogResources(
                    this.catalogId,
                    this.currentResourceId,
                    page,
                    this.perPage,
                );
                this.rows = Array.isArray(response.data) ? response.data : [];
                this.meta = {
                    ...this.meta,
                    ...(response.meta || {}),
                };
            } catch (error) {
                console.error('Error loading catalog rows:', error);
                this.error = error.response?.data?.message || this.$t('error_loading_resources');
                this.rows = [];
            } finally {
                this.rowsLoading = false;
            }
        },
        async changePage(page) {
            if (page < 1 || page > this.meta.last_page) {
                return;
            }

            await this.loadRows(page);
        },
        getCell(row, key) {
            return row.cells?.[key] || {};
        },
        formatCellValue(value) {
            if (value === null || value === undefined || value === '') {
                return this.emptyValue;
            }

            return value;
        },
        goBackCatalog() {
            const parentId = this.catalog?.resource?.parent_id;

            if (!parentId || !this.$router) {
                return;
            }

            this.pushCatalogRoute(parentId);
        },
        pushCatalogRoute(resourceId) {
            const adminPrefix = getAdminPrefix();

            if (!this.$router || !this.catalogId) {
                return;
            }

            const path = `/${adminPrefix}/resources/catalog/${this.catalogId}`;
            const query = resourceId !== this.catalogId ? { resource_id: resourceId } : {};

            this.$router.push({ path, query });
        },
        openCatalogResource() {
            const adminPrefix = getAdminPrefix();
            this.$router.push({
                path: `/${adminPrefix}/resources/${this.currentResourceId}`,
                query: { catalog_id: String(this.catalogId) },
            });
        },
        handleRowClick(event, row) {
            if (event.target.closest('.item-actions')) {
                return;
            }

            if (row.is_folder) {
                this.openCatalogForRow(row.id);
            } else {
                this.openChildResource(row.id);
            }
        },
        openCatalogForRow(resourceId) {
            this.pushCatalogRoute(resourceId);
        },
        openChildResource(resourceId) {
            const adminPrefix = getAdminPrefix();
            this.$router.push({
                path: `/${adminPrefix}/resources/${resourceId}`,
                query: { catalog_id: String(this.catalogId) },
            });
        },
        async removeChildResource(resourceId) {
            if (!confirm(this.$t('confirm_delete_resource'))) {
                return;
            }

            try {
                await deleteResource(resourceId);
                await this.loadRows(this.meta.current_page);
            } catch (error) {
                console.error('Error deleting resource:', error);
                const message = error.response?.data?.message || this.$t('error_deleting_resource');
                this.error = message;
            }
        },
        handleAddChild() {
            this.startAddChildUnderParent(this.currentResourceId, this.catalogChildrenLayouts);
        },
        handleAddChildForRow(row) {
            if (!row?.allow_children) {
                return;
            }

            this.startAddChildUnderParent(row.id, Array.isArray(row.children_layouts) ? row.children_layouts : []);
        },
        startAddChildUnderParent(parentId, layouts) {
            const adminPrefix = getAdminPrefix();

            if (!this.$router || !parentId) {
                return;
            }

            const list = Array.isArray(layouts) ? layouts : [];

            if (list.length === 0) {
                this.$router.push({
                    path: `/${adminPrefix}/resources/new`,
                    query: { parent_id: parentId },
                });

                return;
            }

            if (list.length === 1) {
                this.$router.push({
                    path: `/${adminPrefix}/resources/new`,
                    query: {
                        parent_id: parentId,
                        layout_id: list[0].id,
                    },
                });

                return;
            }

            this.layoutModalParentId = parentId;
            this.layoutModalLayouts = list;
            this.showLayoutModal = true;
        },
        closeLayoutModal() {
            this.showLayoutModal = false;
            this.layoutModalParentId = null;
            this.layoutModalLayouts = [];
        },
    },
};
</script>

<style scoped>
.catalog-row--interactive {
    cursor: pointer;
}

.catalog-row--interactive:hover {
    background: #f5f5f5;
}

.catalog-image {
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 4px;
    display: block;
}

.catalog-empty-value {
    color: #9ca3af;
}

.empty-state {
    padding: 2rem;
    text-align: center;
    color: #6b7280;
    background: #fff;
    border-radius: 6px;
}

</style>
