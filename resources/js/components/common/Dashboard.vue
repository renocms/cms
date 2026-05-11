<template>
    <div class="admin-page dashboard">
        <div class="blocks">
            <div
                v-for="(block, index) in blocks"
                :key="`${block.js_module}-${index}`"
                class="blocks-item"
                :class="block.is_full_width ? 'blocks-item--full' : 'blocks-item--half'"
            >
                <component
                    :is="getBlockComponent(block.js_module)"
                    :data="block.data"
                />
            </div>
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
    padding: 1.5rem;
}

.blocks {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.5rem;
}

.blocks-item--full {
    grid-column: 1 / -1;
}

.blocks-item--half {
    grid-column: span 1;
}

@media (max-width: 992px) {
    .blocks {
        grid-template-columns: minmax(0, 1fr);
    }

    .blocks-item--full,
    .blocks-item--half {
        grid-column: span 1;
    }
}
</style>

