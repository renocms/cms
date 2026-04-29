<template>
    <div class="repeater-editor">
        <div class="repeater-toolbar">
            <div class="repeater-hint">
                {{ rows.length }} {{ $t('repeater_items_count') }}
            </div>
            <button type="button" class="btn btn-primary btn-sm" @click="addRow">
                {{ $t('repeater_add_item') }}
            </button>
        </div>

        <div v-if="rows.length === 0" class="repeater-empty">
            {{ $t('repeater_empty') }}
        </div>

        <div v-else-if="isGallery" class="repeater-gallery">
            <div
                v-for="(row, rowIndex) in rows"
                :key="galleryRowKey(row, rowIndex)"
                class="repeater-card"
            >
                <div v-if="firstMediaField" class="repeater-card-preview">
                    <component
                        :is="getEditorComponent(firstMediaField)"
                        v-if="getEditorComponent(firstMediaField)"
                        :key="`g-${rowIndex}-media`"
                        v-model="row[firstMediaField.key]"
                        v-bind="getNestedFieldProps(firstMediaField, rowIndex)"
                    />
                    <p v-if="firstMediaField.configuration?.note" class="field-note">
                        {{ firstMediaField.configuration.note }}
                    </p>
                </div>
                <div class="repeater-card-body">
                    <div
                        v-for="field in fieldsWithoutHero(firstMediaField, row)"
                        :key="`g-${rowIndex}-${field.key}`"
                        class="form-group repeater-nested-field"
                    >
                        <label v-if="field.name" :for="nestedFieldId(field, rowIndex)">
                            {{ field.name }}
                            <span v-if="field.is_required" class="required">*</span>
                        </label>
                        <component
                            :is="getEditorComponent(field)"
                            v-if="getEditorComponent(field)"
                            v-model="row[field.key]"
                            v-bind="getNestedFieldProps(field, rowIndex)"
                        />
                        <p v-if="field.configuration?.note" class="field-note">
                            {{ field.configuration.note }}
                        </p>
                    </div>
                </div>
                <div class="repeater-card-actions">
                    <button type="button" class="btn btn-secondary btn-sm" @click="removeRow(rowIndex)">
                        {{ $t('repeater_remove_item') }}
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="repeater-list">
            <div
                v-for="(row, rowIndex) in rows"
                :key="listRowKey(row, rowIndex)"
                class="repeater-item"
            >
                <div class="repeater-item-fields">
                    <div
                        v-for="field in schemaFields"
                        :key="`l-${rowIndex}-${field.key}`"
                        class="form-group repeater-nested-field"
                    >
                        <label :for="nestedFieldId(field, rowIndex)">
                            {{ field.name }}
                            <span v-if="field.is_required" class="required">*</span>
                        </label>
                        <component
                            :is="getEditorComponent(field)"
                            v-if="getEditorComponent(field)"
                            v-model="row[field.key]"
                            v-bind="getNestedFieldProps(field, rowIndex)"
                        />
                        <p v-if="field.configuration?.note" class="field-note">
                            {{ field.configuration.note }}
                        </p>
                    </div>
                </div>
                <div class="repeater-item-actions">
                    <button type="button" class="btn btn-secondary btn-sm" @click="removeRow(rowIndex)">
                        {{ $t('repeater_remove_item') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { loadComponent } from '../../utils/componentLoader';
import { getFieldEditorModulePath } from '../../utils/fieldEditor';
import { getEmptyFormValueForField } from '../../utils/fieldSchema';

export default {
    name: 'RepeaterEditor',
    props: {
        modelValue: {
            type: Array,
            default: () => [],
        },
        configuration: {
            type: Object,
            default: () => ({}),
        },
        id: {
            type: String,
            default: '',
        },
        name: {
            type: String,
            default: '',
        },
        resourceId: {
            type: [Number, null],
            default: null,
        },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            rows: [],
            syncingFromParent: false,
        };
    },
    computed: {
        schemaFields() {
            const schema = this.configuration?.schema;
            return Array.isArray(schema) ? schema : [];
        },
        isGallery() {
            return this.configuration?.display === 'gallery';
        },
        firstMediaField() {
            return this.schemaFields.find((f) => f.type === 'media') || null;
        },
    },
    watch: {
        modelValue: {
            immediate: true,
            deep: true,
            handler(value) {
                this.syncingFromParent = true;
                this.rows = this.normalizeRows(value);
                this.$nextTick(() => {
                    this.syncingFromParent = false;
                });
            },
        },
        rows: {
            deep: true,
            handler() {
                if (this.syncingFromParent) {
                    return;
                }

                this.emitUpdate();
            },
        },
    },
    methods: {
        normalizeRows(value) {
            if (!Array.isArray(value)) {
                return [];
            }

            return value.map((row) => {
                const defaults = this.buildEmptyRow();
                const data = row && typeof row === 'object' ? row : {};

                return { ...defaults, ...data };
            });
        },
        listRowKey(row, index) {
            return row.__repeater_uid || `row-${index}`;
        },
        galleryRowKey(row, index) {
            return row.__repeater_uid || `g-${index}`;
        },
        fieldsWithoutHero(heroField, row) {
            if (!heroField || !this.isGallery) {
                return this.schemaFields;
            }

            return this.schemaFields.filter((f) => f.key !== heroField.key);
        },
        addRow() {
            const row = this.buildEmptyRow();
            row.__repeater_uid = `new-${Date.now()}-${Math.random().toString(16).slice(2)}`;
            this.rows.push(row);
        },
        removeRow(index) {
            this.rows.splice(index, 1);
        },
        buildEmptyRow() {
            const row = {};

            this.schemaFields.forEach((field) => {
                row[field.key] = getEmptyFormValueForField(field);
            });

            return row;
        },
        emitUpdate() {
            const payload = this.rows.map((row) => {
                const copy = { ...row };
                delete copy.__repeater_uid;

                return copy;
            });

            this.$emit('update:modelValue', payload);
        },
        getEditorComponent(field) {
            const jsModule = getFieldEditorModulePath(field);

            if (!jsModule) {
                return null;
            }

            return loadComponent(jsModule, {
                errorMessage: this.$t('editor_component_not_found'),
                loadingMessage: this.$t('editor_component_loading'),
            });
        },
        nestedFieldId(field, rowIndex) {
            return `${this.id}-row${rowIndex}-${field.key}`;
        },
        getNestedFieldProps(schemaField, rowIndex) {
            const props = {
                id: this.nestedFieldId(schemaField, rowIndex),
                name: `${this.name}[${rowIndex}].${schemaField.key}`,
                configuration: schemaField.configuration || {},
                resourceId: this.resourceId,
                resourceFieldId: null,
            }

            return props;
        },
    },
};
</script>

<style scoped>
.repeater-editor {
    border: 1px solid #d9d9d9;
    border-radius: 6px;
    padding: 1rem;
    background: #fff;
}

.repeater-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.repeater-hint {
    color: #666;
    font-size: 0.9rem;
}

.repeater-empty {
    margin-top: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    background: #f8f8f8;
    color: #666;
}

.repeater-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.repeater-item {
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    padding: 0.75rem 1rem;
}

.repeater-item-fields {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.repeater-item-actions,
.repeater-card-actions {
    margin-top: 0.75rem;
    display: flex;
    justify-content: flex-end;
}

.repeater-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1rem;
}

.repeater-card {
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    padding: 0.75rem;
    display: flex;
    flex-direction: column;
    background: #fafafa;
}

.repeater-card-preview {
    border-radius: 6px;
    overflow: hidden;
    margin: -0.25rem -0.25rem 0.75rem;
    background: #fff;
    min-height: 140px;
}

.repeater-card-preview :deep(.media-editor) {
    border: none;
}

.repeater-card-preview :deep(.selected-media) {
    flex-direction: column;
}

.repeater-card-preview :deep(.media-preview) {
    width: 100%;
    height: 180px;
}

.repeater-card-preview :deep(.media-preview img) {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.repeater-card-body {
    flex: 1;
}

.repeater-nested-field label {
    display: block;
    margin-bottom: 0.35rem;
}
</style>
