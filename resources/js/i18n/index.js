import ru from './ru.js';
import en from './en.js';

const translations = {
    ru,
    en,
};

// Получить текущий язык из конфига или localStorage
function getCurrentLanguage() {
    // Можно получать из window.CMS_CONFIG или localStorage
    if (window.CMS_CONFIG?.locale) {
        return window.CMS_CONFIG.locale;
    }
    
    const saved = localStorage.getItem('cms_locale');
    if (saved && translations[saved]) {
        return saved;
    }
    
    // По умолчанию английский
    return 'en';
}

// Установить язык
export function setLocale(locale) {
    if (translations[locale]) {
        localStorage.setItem('cms_locale', locale);
        return true;
    }
    return false;
}

// Получить перевод
export function t(key, params = {}) {
    const locale = getCurrentLanguage();
    const translation = translations[locale];
    
    if (!translation) {
        console.warn(`Translation not found for locale: ${locale}`);
        return key;
    }
    
    let text = translation[key];
    
    if (text === undefined) {
        // Fallback на английский, если ключ не найден
        text = translations.en[key] || key;
    }
    
    // Замена параметров в тексте
    if (params && typeof text === 'string') {
        Object.keys(params).forEach(param => {
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

