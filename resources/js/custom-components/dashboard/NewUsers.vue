<template>
    <div class="users-card">
        <h3>{{ $t('new_users') }}</h3>

        <ul v-if="items.length > 0" class="users-list">
            <li v-for="item in items" :key="item.id" class="users-item">
                <a :href="getUserLink(item.id)" class="users-link">
                    {{ item.name }}
                </a>
                <div class="users-meta">
                    {{ formatDate(item.created_at) }}
                </div>
            </li>
        </ul>

        <p v-else class="users-empty">
            {{ $t('no_new_users') }}
        </p>
    </div>
</template>

<script>
import { getAdminPrefix } from '../../api';

export default {
    name: 'NewUsers',
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
        getUserLink(userId) {
            const adminPrefix = getAdminPrefix();
            return `/${adminPrefix}/users/${userId}`;
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
.users-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.users-card h3 {
    margin: 0 0 0.75rem 0;
    color: #666;
    font-size: 0.9rem;
}

.users-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 0.75rem;
}

.users-item {
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 0.6rem;
}

.users-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.users-link {
    color: #1f2937;
    text-decoration: none;
    font-weight: 600;
}

.users-link:hover {
    text-decoration: underline;
}

.users-meta {
    margin-top: 0.25rem;
    color: #6b7280;
    font-size: 0.8rem;
}

.users-empty {
    margin: 0;
    color: #6b7280;
}
</style>
