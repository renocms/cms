<template>
    <div class="color-field">
        <input
            :id="id"
            :name="name"
            :value="pickerValue"
            type="color"
            class="color-input"
            @input="onInput"
        />
        <input
            :value="textValue"
            type="text"
            class="form-control color-value"
            @input="onTextInput"
            @blur="onTextBlur"
        />
    </div>
</template>

<script>
export default {
    name: 'ColorField',
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
    data() {
        return {
            textValue: '',
        };
    },
    computed: {
        pickerValue() {
            return this.normalizeColor(this.textValue) || '#000000';
        },
    },
    watch: {
        modelValue: {
            immediate: true,
            handler(value) {
                const normalizedColor = this.normalizeColor(value);
                this.textValue = normalizedColor || this.normalizeTextValue(value);
            },
        },
    },
    methods: {
        onInput(event) {
            const normalizedColor = this.normalizeColor(event.target.value) || '#000000';

            this.textValue = normalizedColor;
            this.$emit('update:modelValue', normalizedColor);
        },
        onTextInput(event) {
            this.textValue = event.target.value;
        },
        onTextBlur() {
            const normalizedColor = this.normalizeColor(this.textValue);

            if (normalizedColor) {
                this.textValue = normalizedColor;
                this.$emit('update:modelValue', normalizedColor);

                return;
            }

            const normalizedTextValue = this.normalizeTextValue(this.textValue);
            this.textValue = normalizedTextValue;
            this.$emit('update:modelValue', normalizedTextValue || null);
        },
        normalizeColor(value) {
            if (typeof value !== 'string') {
                return null;
            }

            const normalizedValue = value.trim().replace(/^#/, '');

            if (!normalizedValue) {
                return null;
            }

            if (!/^[0-9a-fA-F]{3}$|^[0-9a-fA-F]{6}$/.test(normalizedValue)) {
                return null;
            }

            if (normalizedValue.length === 3) {
                return `#${normalizedValue
                    .split('')
                    .map((character) => character.repeat(2))
                    .join('')
                    .toLowerCase()}`;
            }

            return `#${normalizedValue.toLowerCase()}`;
        },
        normalizeTextValue(value) {
            if (typeof value !== 'string') {
                return '';
            }

            return value.trim();
        },
    },
};
</script>

<style scoped>
.color-field {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
}

.color-input {
    width: 3rem;
    min-width: 3rem;
    height: 2.5rem;
    padding: 0.125rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background: #fff;
    cursor: pointer;
}

.color-value {
    flex: 1;
}
</style>
