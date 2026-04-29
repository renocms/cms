<template>
    <div class="select-field">
        <select
            :id="id"
            :name="name"
            class="form-control"
            :value="localValue"
            :required="isRequired"
            @change="onChange"
        >
            <option v-if="showEmptyOption" value="">{{ emptyLabel }}</option>
            <option
                v-for="option in normalizedOptionList"
                :key="`${name}-${option.value}`"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>
    </div>
</template>

<script>
export default {
    name: 'Select',
    props: {
        modelValue: {
            type: [String, Number, null],
            default: '',
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
        isRequired: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['update:modelValue'],
    computed: {
        localValue() {
            if (this.modelValue === null || this.modelValue === undefined) {
                return '';
            }

            return String(this.modelValue);
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
        showEmptyOption() {
            if (this.isRequired) {
                return false;
            }

            return true;
        },
        emptyLabel() {
            return this.configuration.empty_label ?? '';
        },
    },
    methods: {
        onChange(event) {
            const value = event.target.value;
            this.$emit('update:modelValue', value);
        },
    },
};
</script>

<style scoped>
.select-field {
    width: 100%;
}
</style>
