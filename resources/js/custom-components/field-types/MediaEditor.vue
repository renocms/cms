<template>
    <div class="media-editor">
        <div v-if="selectedMedia" class="selected-media">
            <div class="media-preview">
                <img v-if="isImage" :src="selectedMediaPreviewUrl" :alt="selectedMedia.alt_text || selectedMedia.name" />
                <div v-else class="media-icon">
                    <span>{{ getFileIcon(selectedMedia.mime_type) }}</span>
                </div>
            </div>
            <div class="media-info">
                <p class="media-name">{{ selectedMedia.name }}</p>
                <div class="media-actions">
                    <button type="button" @click="openMediaPicker" class="btn btn-sm btn-primary">
                        {{ $t('change') }}
                    </button>
                    <button type="button" @click="clearMedia" class="btn btn-sm btn-secondary">
                        {{ $t('remove') }}
                    </button>
                </div>
            </div>
        </div>
        <div v-else class="no-media">
            <button type="button" @click="openMediaPicker" class="btn btn-primary">
                {{ $t('select_media') }}
            </button>
        </div>
        <input
            :id="id"
            :name="name"
            type="hidden"
            :value="localValue || ''"
        />
        <MediaPicker
            :show="showMediaPicker"
            @close="showMediaPicker = false"
            @select="onMediaSelected"
        />
    </div>
</template>

<script>
import { getMedia, getMediaThumbnails } from '../../api/media';
import MediaPicker from '../../components/media/MediaPicker.vue';

export default {
    name: 'MediaEditor',
    components: {
        MediaPicker,
    },
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
        // Медиа может быть передано напрямую из ResourceValueResource
        media: {
            type: Object,
            default: null,
        },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            selectedMedia: null,
            selectedMediaPreviewUrl: '',
            loading: false,
            showMediaPicker: false,
        };
    },
    computed: {
        localValue: {
            get() {
                return this.modelValue || null;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            },
        },
        isImage() {
            if (!this.selectedMedia || !this.selectedMedia.mime_type) {
                return false;
            }
            return this.selectedMedia.mime_type.startsWith('image/');
        },
    },
    watch: {
        modelValue: {
            immediate: true,
            async handler(newValue) {
                if (newValue) {
                    // Если медиа уже передано через prop, используем его
                    if (this.media) {
                        this.selectedMedia = this.media;
                        await this.loadMediaPreview(this.media);
                    } else {
                        await this.loadMedia(newValue);
                    }
                } else {
                    this.selectedMedia = null;
                    this.selectedMediaPreviewUrl = '';
                }
            },
        },
        media: {
            immediate: true,
            handler(newMedia) {
                if (newMedia && this.modelValue) {
                    this.selectedMedia = newMedia;
                    this.loadMediaPreview(newMedia);
                }
            },
        },
    },
    methods: {
        async loadMedia(mediaId) {
            if (!mediaId) {
                this.selectedMedia = null;
                return;
            }
            
            this.loading = true;
            try {
                const response = await getMedia(mediaId);
                this.selectedMedia = response.data || response;
                await this.loadMediaPreview(this.selectedMedia);
            } catch (error) {
                console.error('Error loading media:', error);
                this.selectedMedia = null;
                this.selectedMediaPreviewUrl = '';
            } finally {
                this.loading = false;
            }
        },
        async loadMediaPreview(media) {
            if (!media || !this.isImageObject(media)) {
                this.selectedMediaPreviewUrl = media?.url || '';
                return;
            }

            const mediaId = Number(media.id);
            if (!Number.isFinite(mediaId)) {
                this.selectedMediaPreviewUrl = media.url;
                return;
            }

            try {
                const response = await getMediaThumbnails([mediaId], {
                    width: 80,
                    height: 80,
                    options: 'zc=1',
                });
                const previewUrl = response?.data?.[String(mediaId)];
                this.selectedMediaPreviewUrl = (typeof previewUrl === 'string' && previewUrl.trim() !== '')
                    ? previewUrl
                    : media.url;
            } catch (error) {
                console.error('Error loading media preview:', error);
                this.selectedMediaPreviewUrl = media.url;
            }
        },
        isImageObject(media) {
            if (!media || typeof media !== 'object') {
                return false;
            }

            return media.mime_type?.startsWith('image/');
        },
        openMediaPicker() {
            this.showMediaPicker = true;
        },
        onMediaSelected(media) {
            this.selectedMedia = media;
            this.localValue = media.id;
        },
        clearMedia() {
            this.localValue = null;
            this.selectedMedia = null;
            this.selectedMediaPreviewUrl = '';
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
.media-editor {
    width: 100%;
}

.selected-media {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f9f9f9;
}

.media-preview {
    flex-shrink: 0;
    width: 100px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.media-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-icon {
    font-size: 2rem;
}

.media-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.media-name {
    margin: 0;
}

.media-actions {
    display: flex;
    gap: 0.5rem;
}

.no-media {
    padding: 1rem;
    border: 1px dashed #ddd;
    border-radius: 4px;
    text-align: center;
}
</style>
