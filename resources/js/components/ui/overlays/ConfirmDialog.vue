<script setup lang="ts">
import Button from '../buttons/Button.vue';
import Modal from '../layout/Modal.vue';

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

// Enhanced icon backgrounds with subtle glow effect
const iconColors: Record<string, string> = {
    danger: 'bg-gradient-to-br from-red-100 to-red-50 text-red-600 ring-4 ring-red-100/50 dark:from-red-500/20 dark:to-red-500/10 dark:text-red-400 dark:ring-red-500/10',
    warning: 'bg-gradient-to-br from-amber-100 to-amber-50 text-amber-600 ring-4 ring-amber-100/50 dark:from-amber-500/20 dark:to-amber-500/10 dark:text-amber-400 dark:ring-amber-500/10',
    info: 'bg-gradient-to-br from-blue-100 to-blue-50 text-blue-600 ring-4 ring-blue-100/50 dark:from-blue-500/20 dark:to-blue-500/10 dark:text-blue-400 dark:ring-blue-500/10',
};

// Accent bar colors at top of dialog
const accentColors: Record<string, string> = {
    danger: 'bg-gradient-to-r from-red-500 to-red-400',
    warning: 'bg-gradient-to-r from-amber-500 to-amber-400',
    info: 'bg-gradient-to-r from-blue-500 to-blue-400',
};

const buttonVariants: Record<string, 'danger' | 'warning' | 'primary'> = {
    danger: 'danger',
    warning: 'warning',
    info: 'primary',
};

const icons: Record<string, string> = {
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
        :hide-header="true"
        :seamless-footer="true"
        z-index="z-[100]"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <!-- Colored accent bar at top -->
        <div
            class="absolute left-0 right-0 top-0 h-1 rounded-t-2xl"
            :class="accentColors[variant]"
        />

        <!-- Content with centered layout -->
        <div class="pt-3 text-center sm:text-left">
            <!-- Icon with enhanced glow effect -->
            <div
                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl sm:mx-0"
                :class="iconColors[variant]"
            >
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="icons[variant]" />
            </div>

            <!-- Text Content -->
            <div class="mt-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ title }}
                </h3>
                <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                    {{ message }}
                </p>
                <!-- Slot for additional content -->
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
