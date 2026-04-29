<template>
    <div class="dashboard">
        <h1>{{ $t('dashboard') }}</h1>
        <div class="stats">
            <component
                v-for="(block, index) in blocks"
                :key="index"
                :is="getBlockComponent(block.js_module)"
                :data="block.data"
            />
        </div>
    </div>
</template>

<script>
import { getDashboard } from '../../api';
import { loadComponent } from '../../utils/componentLoader';

export default {
    name: 'Dashboard',
    data() {
        return {
            blocks: [],
        };
    },
    async mounted() {
        await this.loadDashboard();
    },
    methods: {
        async loadDashboard() {
            try {
                const response = await getDashboard();
                if (response.data) {
                    this.blocks = response.data;
                }
            } catch (error) {
                console.error(this.$t('error_loading_dashboard'), error);
            }
        },
        getBlockComponent(moduleUrl) {
            return loadComponent(moduleUrl, {
                errorMessage: this.$t('block_component_not_found'),
                loadingMessage: this.$t('block_component_loading'),
            });
        },
    },
};
</script>

<style scoped>
.dashboard {
    padding: 2rem;
}

h1 {
    margin-bottom: 2rem;
}

.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-card h3 {
    margin: 0 0 0.5rem 0;
    color: #666;
    font-size: 0.9rem;
}

.stat-card p {
    margin: 0;
    font-size: 2rem;
    font-weight: bold;
    color: #333;
}
</style>

