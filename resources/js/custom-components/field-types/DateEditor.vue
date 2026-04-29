<template>
    <div class="date-editor">
        <input
            :id="id"
            :name="name"
            v-model="localValue"
            type="date"
            class="form-control"
        />
    </div>
</template>

<script>
export default {
    name: 'DateEditor',
    props: {
        modelValue: {
            type: String,
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
    },
    emits: ['update:modelValue'],
    computed: {
        localValue: {
            get() {
                return this.modelValue || '';
            },
            set(value) {
                this.$emit('update:modelValue', value);
            },
        },
    },
};
</script>

<style scoped>
.date-editor {
    width: 100%;
}
</style>
