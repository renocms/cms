<template>
    <div class="changes-card">
        <h3>{{ $t('latest_resource_changes') }}</h3>

        <ul v-if="items.length > 0" class="changes-list">
            <li v-for="item in items" :key="item.id" class="changes-item">
                <a :href="getResourceLink(item.id)" class="changes-link">
                    {{ item.title }}
                </a>
                <div class="changes-meta">
                    <span>{{ formatDate(item.updated_at) }}</span>
                    <span v-if="item.editor_name"> - {{ item.editor_name }}</span>
                </div>
            </li>
        </ul>

        <p v-else class="changes-empty">
            {{ $t('no_recent_resource_changes') }}
        </p>
    </div>
</template>

<script>
import { getAdminPrefix } from '../../api';

export default {
    name: 'RecentResourceChanges',
    props: {
        data: {
            type: Object,
            required: true,
            default: () => ({
                items: [],
            }),
        },
    },
    computed: {
        items() {
            return Array.isArray(this.data?.items) ? this.data.items : [];
        },
    },
    methods: {
        getResourceLink(resourceId) {
            const adminPrefix = getAdminPrefix();
            return `/${adminPrefix}/resources/${resourceId}`;
        },
        formatDate(value) {
            if (!value) {
                return '-';
            }

            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }

            return date.toLocaleString();
        },
    },
};
</script>

<style scoped>
.changes-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.changes-card h3 {
    margin: 0 0 0.75rem 0;
    color: #666;
    font-size: 0.9rem;
}

.changes-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 0.75rem;
}

.changes-item {
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 0.6rem;
}

.changes-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.changes-link {
    color: #1f2937;
    text-decoration: none;
    font-weight: 600;
}

.changes-link:hover {
    text-decoration: underline;
}

.changes-meta {
    margin-top: 0.25rem;
    color: #6b7280;
    font-size: 0.8rem;
}

.changes-empty {
    margin: 0;
    color: #6b7280;
}
</style>
