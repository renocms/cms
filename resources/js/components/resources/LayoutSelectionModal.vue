<template>
    <div v-if="show" class="modal-overlay" @click="handleOverlayClick">
        <div class="modal-content" @click.stop>
            <div class="modal-header">
                <h3>{{ $t('select_layout') }}</h3>
                <button class="modal-close" @click="handleClose">×</button>
            </div>
            <div class="modal-body">
                <p v-if="layouts.length === 0" class="no-layouts">
                    {{ $t('no_available_layouts') }}
                </p>
                <div v-else class="layouts-list">
                    <button
                        v-for="layout in layouts"
                        :key="layout.id"
                        class="layout-button"
                        @click="handleSelectLayout(layout.id)"
                    >
                        <div class="layout-name">{{ layout.name }}</div>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="handleClose">{{ $t('cancel') }}</button>
            </div>
        </div>
    </div>
</template>

<script>
import { getAdminPrefix } from '../../api';

export default {
    name: 'LayoutSelectionModal',
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        layouts: {
            type: Array,
            default: () => [],
        },
        parentId: {
            type: Number,
            default: null,
        },
    },
    methods: {
        handleSelectLayout(layoutId) {
            const adminPrefix = getAdminPrefix();
            if (this.$router) {
                const query = { parent_id: this.parentId, layout_id: layoutId };
                this.$router.push({
                    path: `/${adminPrefix}/resources/new`,
                    query: query,
                });
            }
            this.$emit('close');
        },
        handleClose() {
            this.$emit('close');
        },
        handleOverlayClick(event) {
            if (event.target === event.currentTarget) {
                this.handleClose();
            }
        },
    },
};
</script>

<style scoped>
.no-layouts {
    color: #6b7280;
    text-align: center;
    padding: 2rem 0;
}

.layouts-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.layout-button {
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: white;
    cursor: pointer;
    text-align: left;
    transition: all 0.15s;
}

.layout-button:hover {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

.layout-name {
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.layout-description {
    font-size: 0.875rem;
    color: #6b7280;
}
</style>
