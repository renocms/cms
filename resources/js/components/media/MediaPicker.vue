<template>
    <div v-if="show" class="media-picker-modal" @click.self="close">
        <div class="media-picker-content">
            <div class="media-picker-header">
                <h2>{{ $t('select_media') }}</h2>
                <button type="button" @click="close" class="close-btn">×</button>
            </div>
            
            <div class="media-picker-body" @scroll.passive="onBodyScroll">
                <!-- Загрузка нового файла -->
                <div class="upload-section">
                    <input 
                        type="file" 
                        ref="fileInput" 
                        @change="handleFileUpload"
                        style="display: none"
                        accept="image/*,video/*,audio/*,.pdf,.doc,.docx"
                    />
                    <button type="button" @click="$refs.fileInput.click()" class="btn btn-primary" :disabled="uploading">
                        {{ uploading ? $t('uploading') : $t('upload_new_file') }}
                    </button>
                </div>

                <!-- Поиск -->
                <div class="search-section">
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="handleSearch"
                        :placeholder="$t('search_media')"
                        class="form-control"
                    />
                </div>

                <!-- Список медиа: полная загрузка / поиск — без подгрузки страниц -->
                <div v-if="loading && !loadingMore" class="loading-section">
                    {{ $t('loading') }}...
                </div>
                <div v-else-if="mediaList.length === 0" class="empty-section">
                    {{ $t('no_media_found') }}
                </div>
                <template v-else>
                    <div class="media-grid">
                        <div
                            v-for="media in mediaList"
                            :key="media.id"
                            class="media-item"
                            :class="{ selected: selectedMediaId === media.id }"
                            @click="selectMedia(media)"
                        >
                            <div class="media-thumbnail">
                                <img v-if="isImage(media)" :src="getMediaPreviewUrl(media)" :alt="media.name" />
                                <div v-else class="media-icon">{{ getFileIcon(media.mime_type) }}</div>
                            </div>
                            <div class="media-name">{{ media.name }}</div>
                        </div>
                    </div>
                    <div v-if="loadingMore" class="loading-more">
                        {{ $t('loading') }}...
                    </div>
                </template>
            </div>
            
            <div class="media-picker-footer">
                <button type="button" @click="close" class="btn btn-secondary">{{ $t('cancel') }}</button>
                <button 
                    type="button"
                    @click="confirmSelection" 
                    class="btn btn-primary"
                    :disabled="!selectedMediaId"
                >
                    {{ $t('select') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { getMediaList, getMediaThumbnails, uploadMedia } from '../../api/media';

export default {
    name: 'MediaPicker',
    props: {
        show: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['close', 'select'],
    data() {
        return {
            mediaList: [],
            selectedMediaId: null,
            loading: false,
            loadingMore: false,
            uploading: false,
            searchQuery: '',
            searchTimeout: null,
            currentPage: 1,
            lastPage: 1,
            mediaPreviewById: {},
        };
    },
    computed: {
        hasMorePages() {
            return this.lastPage > this.currentPage;
        },
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.selectedMediaId = null;
                this.searchQuery = '';
                this.currentPage = 1;
                this.lastPage = 1;
                this.mediaPreviewById = {};
                this.loadMedia({ append: false });
            }
        },
    },
    methods: {
        extractItems(body) {
            if (!body) {
                return [];
            }
            if (Array.isArray(body.data)) {
                return body.data;
            }
            if (body.data && Array.isArray(body.data.data)) {
                return body.data.data;
            }
            if (Array.isArray(body)) {
                return body;
            }
            return [];
        },
        applyPaginationMeta(body) {
            const meta = body && body.meta;
            if (meta && typeof meta.current_page === 'number' && typeof meta.last_page === 'number') {
                this.currentPage = meta.current_page;
                this.lastPage = meta.last_page;
            } else {
                this.currentPage = 1;
                this.lastPage = 1;
            }
        },
        async loadMedia({ append = false } = {}) {
            if (append) {
                if (!this.hasMorePages || this.loadingMore || this.loading) {
                    return;
                }
                this.loadingMore = true;
            } else {
                this.loading = true;
            }

            const page = append ? this.currentPage + 1 : 1;
            const params = { page };
            if (this.searchQuery) {
                params.search = this.searchQuery;
            }

            try {
                const body = await getMediaList(params);
                const items = this.extractItems(body);
                this.applyPaginationMeta(body);

                if (append) {
                    this.mediaList = [...this.mediaList, ...items];
                } else {
                    this.mediaList = items;
                }

                await this.preloadMediaPreviews(items);
            } catch (error) {
                console.error('Error loading media:', error);
                if (!append) {
                    this.mediaList = [];
                }
            } finally {
                this.loading = false;
                this.loadingMore = false;
            }
        },
        onBodyScroll(event) {
            const el = event.target;
            if (this.loading || this.loadingMore || !this.hasMorePages || this.mediaList.length === 0) {
                return;
            }
            const thresholdPx = 120;
            if (el.scrollHeight - el.scrollTop - el.clientHeight < thresholdPx) {
                this.loadMedia({ append: true });
            }
        },
        handleSearch() {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
            this.searchTimeout = setTimeout(() => {
                this.currentPage = 1;
                this.lastPage = 1;
                this.loadMedia({ append: false });
            }, 300);
        },
        async handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            this.uploading = true;
            try {
                const response = await uploadMedia(file);
                // Добавляем новый файл в начало списка
                const newMedia = response.data || response;
                this.mediaList.unshift(newMedia);
                this.selectedMediaId = newMedia.id;
                await this.preloadMediaPreviews([newMedia]);
            } catch (error) {
                console.error('Error uploading media:', error);
                alert(this.$t('error_uploading_file'));
            } finally {
                this.uploading = false;
                // Сбрасываем input
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            }
        },
        selectMedia(media) {
            this.selectedMediaId = media.id;
        },
        confirmSelection() {
            const selected = this.mediaList.find(m => m.id === this.selectedMediaId);
            if (selected) {
                this.$emit('select', selected);
                this.close();
            }
        },
        close() {
            this.$emit('close');
        },
        isImage(media) {
            return media.mime_type?.startsWith('image/');
        },
        getMediaPreviewUrl(media) {
            if (!media || !media.id) {
                return media?.url || '';
            }

            return this.mediaPreviewById[media.id] || media.url;
        },
        async preloadMediaPreviews(items) {
            const imageIds = (items || [])
                .filter((media) => this.isImage(media))
                .map((media) => Number(media.id))
                .filter((id) => Number.isFinite(id) && !this.mediaPreviewById[id]);

            if (imageIds.length === 0) {
                return;
            }

            try {
                const response = await getMediaThumbnails([...new Set(imageIds)], {
                    width: 80,
                    height: 80,
                    options: 'zc=1',
                });
                const previews = response?.data && typeof response.data === 'object' ? response.data : {};

                Object.entries(previews).forEach(([id, url]) => {
                    const mediaId = Number(id);
                    if (Number.isFinite(mediaId) && typeof url === 'string' && url.trim() !== '') {
                        this.mediaPreviewById[mediaId] = url;
                    }
                });
            } catch (error) {
                console.error('Error loading media thumbnails:', error);
            }
        },
        getFileIcon(mimeType) {
            if (!mimeType) {
                return '📄';
            }
            
            if (mimeType.startsWith('image/')) {
                return '🖼️';
            } else if (mimeType.startsWith('video/')) {
                return '🎥';
            } else if (mimeType.startsWith('audio/')) {
                return '🎵';
            } else if (mimeType.includes('pdf')) {
                return '📕';
            } else {
                return '📄';
            }
        },
    },
};
</script>

<style scoped>
.media-picker-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.media-picker-content {
    background: #fff;
    border-radius: 8px;
    width: 90%;
    max-width: 900px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.media-picker-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #ddd;
}

.media-picker-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.close-btn {
    background: none;
    border: none;
    font-size: 2rem;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.close-btn:hover {
    color: #000;
}

.media-picker-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
}

.upload-section {
    margin-bottom: 1rem;
}

.search-section {
    margin-bottom: 1rem;
}

.loading-section,
.empty-section {
    text-align: center;
    padding: 2rem;
    color: #666;
}

.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
}

.media-item {
    border: 2px solid #ddd;
    border-radius: 4px;
    padding: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    background: #fff;
}

.media-item:hover {
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
}

.media-item.selected {
    border-color: #007bff;
    background: #e7f3ff;
}

.media-thumbnail {
    width: 100%;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.media-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-icon {
    font-size: 2.5rem;
}

.media-name {
    font-size: 0.875rem;
    text-align: center;
    word-break: break-word;
    color: #333;
}

.media-picker-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1.5rem;
    border-top: 1px solid #ddd;
}

.loading-more {
    text-align: center;
    padding: 1rem;
    color: #666;
    font-size: 0.875rem;
}
</style>
