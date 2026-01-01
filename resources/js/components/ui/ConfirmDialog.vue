<script setup lang="ts">
import Button from './Button.vue';
import Modal from './Modal.vue';

interface Props {
    modelValue: boolean;
    title?: string;
    message?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'warning' | 'info';
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Confirm Action',
    message: 'Are you sure you want to continue? This action cannot be undone.',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'danger',
    loading: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
    confirm: [];
    cancel: [];
}>();

const iconColors = {
    danger: 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
    warning: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
    info: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
};

const buttonVariants = {
    danger: 'danger',
    warning: 'warning',
    info: 'primary',
} as const;

const icons = {
    danger: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />`,
    warning: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />`,
    info: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />`,
};

const close = () => {
    emit('update:modelValue', false);
};

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    emit('cancel');
    close();
};
</script>

<template>
    <Modal
        :model-value="modelValue"
        size="sm"
        :closable="!loading"
        :close-on-overlay="!loading"
        :close-on-escape="!loading"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <div class="text-center sm:text-left">
            <!-- Icon -->
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full sm:mx-0" :class="iconColors[variant]">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="icons[variant]" />
            </div>

            <!-- Content -->
            <div class="mt-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ title }}
                </h3>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ message }}
                </p>
                <slot />
            </div>
        </div>

        <template #footer>
            <div class="flex w-full flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <Button variant="ghost" :disabled="loading" @click="handleCancel">
                    {{ cancelText }}
                </Button>
                <Button :variant="buttonVariants[variant]" :loading="loading" @click="handleConfirm">
                    {{ confirmText }}
                </Button>
            </div>
        </template>
    </Modal>
</template>
