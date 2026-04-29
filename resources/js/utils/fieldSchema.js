/**
 * Значение по умолчанию из схемы поля (PHP -> configuration.default).
 */
export function fieldSchemaHasDefault(configuration) {
    return configuration != null && Object.prototype.hasOwnProperty.call(configuration, 'default');
}

export function normalizeCheckboxFormValue(value) {
    return value === '1' || value === 1 || value === true || value === 'true' ? '1' : '0';
}

/**
 * Значение checkbox_group из API или БД: массив строк либо JSON-строка.
 *
 * @param {unknown} value
 * @returns {string[]}
 */
export function normalizeCheckboxGroupFormValue(value) {
    if (value == null) {
        return [];
    }

    if (Array.isArray(value)) {
        return value.map((v) => String(v));
    }

    if (typeof value === 'string') {
        try {
            const parsed = JSON.parse(value);
            if (Array.isArray(parsed)) {
                return parsed.map((v) => String(v));
            }
        } catch {
            return [];
        }
    }

    return [];
}

/**
 * Пустое значение формы, если в БД нет сохранённого значения.
 *
 * @param {object} field — элемент схемы (layout / settings / repeater nested)
 */
export function getEmptyFormValueForField(field) {
    const cfg = field.configuration || {};
    const type = field.type;

    if (type === 'checkbox') {
        if (fieldSchemaHasDefault(cfg)) {
            return normalizeCheckboxFormValue(cfg.default);
        }

        return '0';
    }

    if (type === 'checkbox_group') {
        if (fieldSchemaHasDefault(cfg)) {
            const d = cfg.default;
            return Array.isArray(d) ? [...d] : [];
        }

        return [];
    }

    if (fieldSchemaHasDefault(cfg)) {
        return cfg.default;
    }

    if (cfg.use_null_default_when_unset) {
        return null;
    }

    if (type === 'repeater' || type === 'gallery') {
        return [];
    }

    if (type === 'boolean') {
        return false;
    }

    return '';
}
