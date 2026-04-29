<template>
    <div class="file-field">
        <input
            :id="id"
            :name="name"
            :value="localValue"
            type="text"
            class="form-control"
            placeholder="File path"
            @input="onInput"
        />
    </div>
</template>

<script>
export default {
    name: 'File',
    props: {
        modelValue: {
            type: [String, null],
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
        localValue() {
            return this.modelValue ?? '';
        },
    },
    methods: {
        onInput(event) {
            this.$emit('update:modelValue', event.target.value);
        },
    },
};
</script>

<style scoped>
.file-field {
    width: 100%;
}
</style>
