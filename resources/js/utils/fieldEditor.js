/**
 * URL модуля редактора поля приходит с API (js_module из FieldTypeInterface::getJsModule()).
 */
export function getFieldEditorModulePath(field) {
    if (!field || typeof field !== 'object') {
        return null;
    }

    return field.js_module || field.vue_component || null;
}
