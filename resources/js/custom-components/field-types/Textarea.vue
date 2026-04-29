<template>
    <div class="textarea-field">
        <textarea
            :id="id"
            :name="name"
            v-model="localValue"
            class="form-control"
            :rows="textareaRows"
            :style="textareaStyle"
        ></textarea>
    </div>
</template>

<script>
export default {
    name: 'Textarea',
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
        localValue: {
            get() {
                return this.modelValue ?? '';
            },
            set(value) {
                this.$emit('update:modelValue', value);
            },
        },
        textareaRows() {
            return Number(this.configuration?.rows) || 5;
        },
        textareaStyle() {
            const height = Number(this.configuration?.height);

            if (!Number.isFinite(height) || height <= 0) {
                return {};
            }

            return {
                minHeight: `${height}px`,
            };
        },
    },
};
</script>

<style scoped>
.textarea-field {
    width: 100%;
}
</style>
