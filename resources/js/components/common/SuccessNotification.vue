<template>
    <transition name="fade">
        <div v-if="message" class="notification notification-success">
            <div class="notification-content">
                <span class="notification-message">{{ message }}</span>
                <button @click="handleClose" class="notification-close">&times;</button>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: 'SuccessNotification',
    props: {
        message: {
            type: String,
            default: null,
        },
    },
    methods: {
        handleClose() {
            this.$emit('close');
        },
    },
};
</script>

<style scoped>
/* Стили вынесены в общий файл forms.css */
</style>

