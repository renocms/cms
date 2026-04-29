<template>
    <div
        class="radio-buttons-field"
        :class="{ 'radio-buttons-field-horizontal': isHorizontal }"
    >
        <label
            v-for="(label, value) in normalizedOptions"
            :key="`${name}-${value}`"
            class="radio-option"
            :class="{ 'radio-option-horizontal': isHorizontal }"
        >
            <input
                :id="getOptionId(value)"
                :name="name"
                :checked="localValue === String(value)"
                type="radio"
                :value="value"
                @change="onChange"
            />
            <span>{{ label }}</span>
        </label>
    </div>
</template>

<script>
export default {
    name: 'RadioButtons',
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
    },
    emits: ['update:modelValue'],
    computed: {
        localValue() {
            return this.modelValue == null ? '' : String(this.modelValue);
        },
        normalizedOptions() {
            return this.configuration.options || {};
        },
        isHorizontal() {
            return Boolean(this.configuration.horizontal);
        },
    },
    methods: {
        onChange(event) {
            this.$emit('update:modelValue', event.target.value);
        },
        getOptionId(value) {
            return this.id ? `${this.id}-${value}` : `${this.name}-${value}`;
        },
    },
};
</script>

<style scoped>
.radio-buttons-field {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.radio-buttons-field-horizontal {
    flex-direction: row;
    flex-wrap: wrap;
}

.radio-option-horizontal {
    margin-right: 1rem;
}
</style>
