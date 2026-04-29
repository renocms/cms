<template>
    <div class="number-field">
        <input
            :id="id"
            :name="name"
            :value="localValue"
            type="number"
            class="form-control"
            @input="onInput"
        />
    </div>
</template>

<script>
export default {
    name: 'NumberField',
    props: {
        modelValue: {
            type: [Number, String, null],
            default: null,
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
            const value = event.target.value;
            this.$emit('update:modelValue', value === '' ? null : Number(value));
        },
    },
};
</script>

<style scoped>
.number-field {
    width: 100%;
}
</style>
