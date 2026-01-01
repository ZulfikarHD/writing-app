<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    variant?: 'info' | 'success' | 'warning' | 'danger';
    title?: string;
    dismissible?: boolean;
    icon?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'info',
    dismissible: false,
    icon: true,
});

const emit = defineEmits<{
    dismiss: [];
}>();

const variantClasses = {
    info: 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    success: 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400',
    warning: 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-400',
    danger: 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400',
};

const iconColors = {
    info: 'text-blue-500 dark:text-blue-400',
    success: 'text-emerald-500 dark:text-emerald-400',
    warning: 'text-amber-500 dark:text-amber-400',
    danger: 'text-red-500 dark:text-red-400',
};

const dismissColors = {
    info: 'text-blue-500 hover:bg-blue-100 focus:ring-blue-500 dark:text-blue-400 dark:hover:bg-blue-800/30',
    success: 'text-emerald-500 hover:bg-emerald-100 focus:ring-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-800/30',
    warning: 'text-amber-500 hover:bg-amber-100 focus:ring-amber-500 dark:text-amber-400 dark:hover:bg-amber-800/30',
    danger: 'text-red-500 hover:bg-red-100 focus:ring-red-500 dark:text-red-400 dark:hover:bg-red-800/30',
};

const icons = {
    info: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />`,
    success: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />`,
    warning: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />`,
    danger: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />`,
};

const alertClasses = computed(() => {
    return ['rounded-lg border p-4', variantClasses[props.variant]].join(' ');
});
</script>

<template>
    <div :class="alertClasses" role="alert">
        <div class="flex">
            <div v-if="icon" class="shrink-0">
                <svg
                    :class="['h-5 w-5', iconColors[variant]]"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    v-html="icons[variant]"
                />
            </div>
            <div class="ml-3 flex-1">
                <h4 v-if="title" class="text-sm font-medium">{{ title }}</h4>
                <div :class="['text-sm', title ? 'mt-1' : '']">
                    <slot />
                </div>
            </div>
            <div v-if="dismissible" class="ml-auto pl-3">
                <button
                    type="button"
                    :class="[
                        '-m-1.5 inline-flex h-8 w-8 items-center justify-center rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2',
                        dismissColors[variant],
                    ]"
                    @click="emit('dismiss')"
                >
                    <span class="sr-only">Dismiss</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
