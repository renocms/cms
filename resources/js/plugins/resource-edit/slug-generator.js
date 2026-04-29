export default {
    init({ component, config }) {
        const fieldKey = config?.fieldKey || 'title';
        
        let initialSlug = component.form.slug || '';
        let lastGeneratedSlug = '';
        let isManualEdit = false;
        
        const unwatch = component.$watch(
            () => component.form.values[fieldKey],
            (newValue, oldValue) => {
                if (newValue && typeof newValue === 'string' && newValue.trim() !== '') {
                    const generatedSlug = generateSlug(newValue);
                    
                    const currentSlug = component.form.slug || '';
                    const shouldUpdate = !currentSlug || 
                        currentSlug === lastGeneratedSlug || 
                        currentSlug === initialSlug ||
                        !isManualEdit;
                    
                    if (shouldUpdate && generatedSlug) {
                        component.form.slug = generatedSlug;
                        lastGeneratedSlug = generatedSlug;
                        isManualEdit = false;
                    }
                }
            },
            { immediate: false }
        );
        
        const unwatchSlug = component.$watch(
            () => component.form.slug,
            (newSlug, oldSlug) => {
                if (newSlug && newSlug !== lastGeneratedSlug && oldSlug !== undefined) {
                    isManualEdit = true;
                    initialSlug = newSlug;
                }
            },
            { immediate: false }
        );
        
        return {
            name: 'slug-generator',
            destroy() {
                if (unwatch) {
                    unwatch();
                }
                if (unwatchSlug) {
                    unwatchSlug();
                }
            },
        };
    },
};

function generateSlug(str) {
    // Транслитерация русских букв
    const transliterationMap = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
        'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
        'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
        'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
        'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya',
        'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Ё': 'Yo',
        'Ж': 'Zh', 'З': 'Z', 'И': 'I', 'Й': 'Y', 'К': 'K', 'Л': 'L', 'М': 'M',
        'Н': 'N', 'О': 'O', 'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T', 'У': 'U',
        'Ф': 'F', 'Х': 'H', 'Ц': 'Ts', 'Ч': 'Ch', 'Ш': 'Sh', 'Щ': 'Sch',
        'Ъ': '', 'Ы': 'Y', 'Ь': '', 'Э': 'E', 'Ю': 'Yu', 'Я': 'Ya'
    };
    
    let result = str;
    
    // Применяем транслитерацию
    for (const [russian, latin] of Object.entries(transliterationMap)) {
        result = result.replace(new RegExp(russian, 'g'), latin);
    }
    
    return result
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}
