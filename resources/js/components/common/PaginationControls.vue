<template>
    <div class="pagination">
        <button
            type="button"
            class="btn btn-hollow"
            :disabled="meta.current_page <= 1 || loading"
            @click="emitPage(1)"
        >
            <Icon name="chevron-first" :size="16" />
        </button>
        <button
            type="button"
            class="btn btn-hollow"
            :disabled="meta.current_page <= 1 || loading"
            @click="emitPage(meta.current_page - 1)"
        >
            <Icon name="chevron-left" :size="16" />
        </button>
        <span class="pagination-info">
            {{ meta.current_page }} / {{ meta.last_page }}
        </span>
        <button
            type="button"
            class="btn btn-hollow"
            :disabled="meta.current_page >= meta.last_page || loading"
            @click="emitPage(meta.current_page + 1)"
        >
            <Icon name="chevron-right" :size="16" />
        </button>
        <button
            type="button"
            class="btn btn-hollow"
            :disabled="meta.current_page >= meta.last_page || loading"
            @click="emitPage(meta.last_page)"
        >
            <Icon name="chevron-last" :size="16" />
        </button>
    </div>
</template>

<script>
import Icon from './Icon.vue';

export default {
    name: 'PaginationControls',
    components: {
        Icon,
    },
    props: {
        meta: {
            type: Object,
            required: true,
        },
        loading: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['change-page'],
    methods: {
        emitPage(page) {
            this.$emit('change-page', page);
        },
    },
};
</script>
