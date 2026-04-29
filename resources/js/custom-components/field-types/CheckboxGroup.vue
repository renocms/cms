<template>
    <div class="checkbox-group-field">
        <label
            v-for="item in normalizedOptionList"
            :key="`${name}-${item.value}`"
            class="checkbox-option"
        >
            <input
                :id="getOptionId(item.value)"
                :name="`${name}[]`"
                type="checkbox"
                :checked="isSelected(item.value)"
                :value="item.value"
                @change="onToggle(item.value, $event.target.checked)"
            />
            <span>{{ item.label }}</span>
        </label>
    </div>
</template>

<script>
export default {
    name: 'CheckboxGroup',
    props: {
        modelValue: {
            type: [Array, String, null],
            default: () => [],
        },
        name: {
            type: String,
            default: '',
        },
        id: {
            type: String,
            default: '',
        },
        configuration: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: ['update:modelValue'],
    computed: {
        selectedKeys() {
            return this.normalizeValue(this.modelValue);
        },
        normalizedOptionList() {
            const raw = this.configuration?.options;
            if (raw == null) {
                return [];
            }

            if (Array.isArray(raw)) {
                return raw.map((label, index) => ({
                    value: String(index),
                    label: String(label),
                }));
            }

            return Object.entries(raw).map(([value, label]) => ({
                value: String(value),
                label: String(label),
            }));
        },
    },
    methods: {
        normalizeValue(value) {
            if (value == null) {
                return [];
            }

            if (Array.isArray(value)) {
                return value.map((v) => String(v));
            }

            if (typeof value === 'string') {
                try {
                    const parsed = JSON.parse(value);
                    if (Array.isArray(parsed)) {
                        return parsed.map((v) => String(v));
                    }
                } catch {
                    return [];
                }
            }

            return [];
        },
        isSelected(value) {
            const key = String(value);
            return this.selectedKeys.includes(key);
        },
        onToggle(value, checked) {
            const key = String(value);
            const next = new Set(this.selectedKeys);
            if (checked) {
                next.add(key);
            } else {
                next.delete(key);
            }
            this.$emit('update:modelValue', Array.from(next));
        },
        getOptionId(value) {
            return this.id ? `${this.id}-${value}` : `${this.name}-${value}`;
        },
    },
};
</script>

<style scoped>
.checkbox-group-field {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.checkbox-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
</style>
