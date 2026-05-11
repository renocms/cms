<template>
    <transition name="fade">
        <div v-if="show" class="loading-overlay">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p class="loading-text">{{ message || $t('loading') }}</p>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: 'LoadingOverlay',
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        message: {
            type: String,
            default: null,
        },
    },
};
</script>

<style scoped>
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.4);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

.loading-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 0 160px rgba(0, 0, 0, 0.4);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    min-width: 200px;
}

.loading-spinner {
    width: 30px;
    height: 30px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #2c3e50;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.loading-text {
    margin: 0;
    color: #333;
    font-size: 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>

