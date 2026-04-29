<template>
    <component
        :is="iconComponent"
        v-if="iconComponent"
    />
</template>

<script>
import { markRaw } from 'vue';
import { loadIcon } from '../../shared/icons';

export default {
    name: 'Icon',
    props: {
        name: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            iconComponent: null,
        };
    },
    watch: {
        name: {
            immediate: true,
            async handler() {
                await this.resolveIconComponent();
            },
        },
    },
    methods: {
        async resolveIconComponent() {
            const requestedIconName = this.name;

            if (!requestedIconName) {
                this.iconComponent = null;
                return;
            }

            const iconComponent = await loadIcon(requestedIconName);

            if (this.name !== requestedIconName) {
                return;
            }

            this.iconComponent = iconComponent ? markRaw(iconComponent) : null;

            if (!iconComponent) {
                console.warn(`[reno-cms] Icon "${requestedIconName}" was not found in lucide-vue-next.`);
            }
        },
    },
};
</script>
