<template>
    <div class="checkboxes-field">
        <label class="checkbox-label">
            <input
                :id="id"
                :name="name"
                :checked="localValue"
                type="checkbox"
                @change="onChange"
            />
            <span></span>
        </label>
    </div>
</template>

<script>
export default {
    name: 'Checkboxes',
    props: {
        modelValue: {
            type: [Boolean, Number, String, null],
            default: false,
        },
        name: {
            type: String,
            default: '',
        },
        id: {
            type: String,
            default: '',
        },
    },
    emits: ['update:modelValue'],
    computed: {
        localValue() {
            return this.modelValue === true || this.modelValue === 1 || this.modelValue === '1' || this.modelValue === 'true';
        },
    },
    methods: {
        onChange(event) {
            this.$emit('update:modelValue', event.target.checked ? 1 : 0);
        },
    },
};
</script>

<style scoped>
.checkboxes-field {
    width: 100%;
}

.checkbox-label {
    display: inline-flex;
    align-items: center;
}
</style>
