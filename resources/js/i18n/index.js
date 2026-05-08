function getTranslations() {
    if (window.CMS_TRANSLATIONS && typeof window.CMS_TRANSLATIONS === 'object') {
        return window.CMS_TRANSLATIONS;
    }

    return {};
}

// Получить текущий язык из конфига или localStorage
function getCurrentLanguage() {
    // Можно получать из window.CMS_CONFIG или localStorage
    if (window.CMS_CONFIG?.locale) {
        return window.CMS_CONFIG.locale;
    }

    const saved = localStorage.getItem('cms_locale');
    if (saved) {
        return saved;
    }

    // По умолчанию английский
    return 'en';
}

// Установить язык
export function setLocale(locale) {
    if (typeof locale !== 'string' || !locale.trim()) {
        return false;
    }

    localStorage.setItem('cms_locale', locale);
    return true;
}

// Получить перевод
export function t(key, params = {}) {
    const translations = getTranslations();
    let text = translations[key];

    if (text === undefined) {
        text = key;
    }

    // Замена параметров в тексте
    if (params && typeof text === 'string') {
        Object.keys(params).forEach((param) => {
            text = text.replace(new RegExp(`:${param}`, 'g'), params[param]);
        });
    }

    return text;
}

// Получить текущий язык
export function getLocale() {
    return getCurrentLanguage();
}

// Экспорт для использования в компонентах
export default {
    t,
    setLocale,
    getLocale,
};

