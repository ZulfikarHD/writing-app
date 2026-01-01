<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';

import ConfirmDialog from './ConfirmDialog.vue';

const { state, isLoading, handleConfirm, handleCancel } = useConfirm();
</script>

<template>
    <ConfirmDialog
        :model-value="state.isOpen"
        :title="state.title"
        :message="state.message"
        :confirm-text="state.confirmText"
        :cancel-text="state.cancelText"
        :variant="state.variant"
        :loading="isLoading"
        @confirm="handleConfirm"
        @cancel="handleCancel"
        @update:model-value="handleCancel"
    />
</template>
