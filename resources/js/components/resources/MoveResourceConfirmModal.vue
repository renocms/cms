<template>
    <div v-if="show" class="modal-overlay" @click="handleOverlayClick">
        <div class="modal-content" @click.stop>
            <div class="modal-header">
                <h3>{{ $t('move_resource_confirm_title') }}</h3>
                <button type="button" class="modal-close" :disabled="confirming" @click="handleClose">×</button>
            </div>
            <div class="modal-body">
                <p class="label-muted">{{ $t('move_resource_confirm_intro') }}</p>
                <p class="value-strong">{{ sourceTitle }}</p>
                <p class="label-muted spaced">{{ $t('move_resource_confirm_where') }}</p>
                <p class="value-strong">{{ destinationLine }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" :disabled="confirming" @click="handleClose">
                    {{ $t('cancel') }}
                </button>
                <button type="button" class="btn btn-primary" :disabled="confirming" @click="handleConfirm">
                    {{ confirming ? $t('saving') : $t('confirm_move') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'MoveResourceConfirmModal',
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        sourceTitle: {
            type: String,
            default: '',
        },
        destinationLine: {
            type: String,
            default: '',
        },
        confirming: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['close', 'confirm'],
    methods: {
        handleClose() {
            if (this.confirming) {
                return;
            }
            this.$emit('close');
        },
        handleConfirm() {
            if (this.confirming) {
                return;
            }
            this.$emit('confirm');
        },
        handleOverlayClick(event) {
            if (event.target === event.currentTarget) {
                this.handleClose();
            }
        },
    },
};
</script>

<style scoped>
.label-muted {
    margin: 0;
    font-size: 0.8125rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.label-muted.spaced {
    margin-top: 1rem;
}

.value-strong {
    margin: 0.35rem 0 0;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    word-break: break-word;
}
</style>
