<template>
    <div class="rich-content-editor" :style="editorStyle">
        <div :id="editorId" class="quill-editor"></div>
    </div>
</template>

<script>
import { getCmsAssetUrl } from '../../utils/assetUrls';

export default {
    name: 'RichContentEditor',
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
        configuration: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            editorId: this.id || `quill-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
            editor: null,
            localValue: '',
        };
    },
    computed: {
        editorHeight() {
            const height = Number(this.configuration?.height);

            if (!Number.isFinite(height) || height <= 0) {
                return 400;
            }

            return height;
        },
        editorStyle() {
            return {
                '--rich-content-editor-height': `${this.editorHeight}px`,
            };
        },
    },
    watch: {
        modelValue: {
            immediate: true,
            handler(newValue) {
                this.localValue = newValue || '';
                
                if (this.editor) {
                    const currentContent = this.editor.root.innerHTML;
                    const normalizedNewValue = this.localValue || '';
                    
                    // Обновляем только если содержимое действительно изменилось
                    if (currentContent !== normalizedNewValue) {
                        this.editor.root.innerHTML = normalizedNewValue;
                    }
                }
            },
        },
    },
    mounted() {
        // Убеждаемся, что ID установлен
        if (this.id && !this.editorId) {
            this.editorId = this.id;
        }
        
        // Ждем следующего тика для гарантии готовности DOM
        this.$nextTick(() => {
            // Проверяем наличие элемента в DOM
            const container = document.getElementById(this.editorId);
            if (!container) {
                console.error(`Quill container #${this.editorId} not found in DOM`);
                // Повторяем попытку через небольшую задержку
                setTimeout(() => {
                    this.loadQuill();
                }, 100);
                return;
            }
            
            // Загружаем Quill
            this.loadQuill();
        });
    },
    beforeUnmount() {
        if (this.editor) {
            this.editor = null;
        }
    },
    methods: {
        loadQuill() {
            // Проверяем, загружен ли уже Quill
            if (window.Quill) {
                this.initQuill();
                return;
            }

            // Определяем пути к Quill
            const cssPath = `${this.getQuillBasePath()}/quill.snow.css`;
            const jsPath = this.getQuillJsPath();

            // Загружаем CSS
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = cssPath;
            link.id = 'quill-snow-css';
            // Проверяем, не загружен ли уже CSS
            if (!document.getElementById('quill-snow-css')) {
                document.head.appendChild(link);
            }

            // Загружаем JS
            const script = document.createElement('script');
            script.src = jsPath;
            script.id = 'quill-js';
            script.onload = () => {
                this.initQuill();
            };
            script.onerror = () => {
                console.error('Failed to load Quill from local path, trying CDN fallback');
                // Fallback на CDN, если локальный файл не найден
                this.loadQuillFromCDN();
            };
            // Проверяем, не загружен ли уже скрипт
            if (!document.getElementById('quill-js')) {
                document.head.appendChild(script);
            } else {
                // Если скрипт уже загружен, просто инициализируем
                this.initQuill();
            }
        },
        getQuillBasePath() {
            return getCmsAssetUrl('quill');
        },
        getQuillJsPath() {
            return getCmsAssetUrl('quill/quill.js');
        },
        loadQuillFromCDN() {
            // Fallback на CDN, если локальный файл не найден
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdn.quilljs.com/1.3.6/quill.snow.css';
            link.id = 'quill-snow-css-cdn';
            if (!document.getElementById('quill-snow-css-cdn')) {
                document.head.appendChild(link);
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.quilljs.com/1.3.6/quill.js';
            script.id = 'quill-js-cdn';
            script.onload = () => {
                this.initQuill();
            };
            script.onerror = () => {
                console.error('Failed to load Quill from CDN');
            };
            if (!document.getElementById('quill-js-cdn')) {
                document.head.appendChild(script);
            }
        },
        initQuill() {
            if (!window.Quill) {
                console.error('Quill is not available');
                // Повторяем попытку через небольшую задержку, если Quill еще не загружен
                setTimeout(() => {
                    if (window.Quill) {
                        this.initQuill();
                    } else {
                        console.error('Quill failed to load after retry');
                    }
                }, 200);
                return;
            }

            // Проверяем наличие элемента в DOM
            const container = document.getElementById(this.editorId);
            if (!container) {
                console.error(`Quill container #${this.editorId} not found`);
                // Повторяем попытку через небольшую задержку
                setTimeout(() => {
                    this.initQuill();
                }, 100);
                return;
            }

            // Проверяем, не инициализирован ли уже Quill для этого элемента
            if (container.classList.contains('ql-container')) {
                return; // Quill уже инициализирован
            }

            const Quill = window.Quill;
            
            try {
                this.editor = new Quill(`#${this.editorId}`, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'align': [] }],
                            ['link', 'image'],
                            ['clean'],
                            [{ 'color': [] }, { 'background': [] }],
                            ['code-block'],
                        ],
                    },
                    placeholder: this.$t('rich_content_placeholder'),
                });

                // Устанавливаем начальное содержимое
                if (this.localValue) {
                    this.editor.root.innerHTML = this.localValue;
                }

                // Слушаем изменения
                this.editor.on('text-change', () => {
                    const content = this.editor.root.innerHTML;
                    if (content !== this.localValue) {
                        this.localValue = content;
                        this.$emit('update:modelValue', content);
                    }
                });
                
                // Помечаем, что редактор инициализирован
                this.isInitialized = true;
            } catch (error) {
                console.error('Failed to initialize Quill:', error);
            }
        },
    },
};
</script>

<style scoped>
.rich-content-editor {
    width: 100%;
}

.quill-editor {
    min-height: var(--rich-content-editor-height, 400px);
}

:deep(.ql-editor) {
    min-height: var(--rich-content-editor-height, 400px);
    font-family: Helvetica, Arial, sans-serif;
    font-size: 14px;
}

:deep(.ql-container) {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 14px;
}
</style>

