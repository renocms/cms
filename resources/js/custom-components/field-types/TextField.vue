<template>
    <div class="text-field">
        <input
            :id="id"
            :name="name"
            v-model="localValue"
            type="text"
            class="form-control"
        />
    </div>
</template>

<script>
export default {
    name: 'TextField',
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
    },
};
</script>

<style scoped>
.text-field {
    width: 100%;
}
</style>
