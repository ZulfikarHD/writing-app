<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    variant?: 'default' | 'primary' | 'secondary' | 'success' | 'warning' | 'danger' | 'info';
    size?: 'sm' | 'md' | 'lg';
    rounded?: 'full' | 'md';
    removable?: boolean;
    dot?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
    size: 'md',
    rounded: 'full',
    removable: false,
    dot: false,
});

const emit = defineEmits<{
    remove: [];
}>();

const variantClasses = {
    default: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
    primary: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
    secondary: 'bg-zinc-200 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200',
    success: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    warning: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    danger: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    info: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
};

const dotColors = {
    default: 'bg-zinc-500',
    primary: 'bg-violet-500',
    secondary: 'bg-zinc-600 dark:bg-zinc-400',
    success: 'bg-emerald-500',
    warning: 'bg-amber-500',
    danger: 'bg-red-500',
    info: 'bg-blue-500',
};

const sizeClasses = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-0.5 text-xs',
    lg: 'px-3 py-1 text-sm',
};

const roundedClasses = {
    full: 'rounded-full',
    md: 'rounded-md',
};

const classes = computed(() => {
    return [
        'inline-flex items-center font-medium',
        variantClasses[props.variant],
        sizeClasses[props.size],
        roundedClasses[props.rounded],
    ].join(' ');
});
</script>

<template>
    <span :class="classes">
        <span v-if="dot" :class="['mr-1.5 h-1.5 w-1.5 rounded-full', dotColors[variant]]" />
        <slot />
        <button
            v-if="removable"
            type="button"
            class="-mr-0.5 ml-1.5 inline-flex h-4 w-4 items-center justify-center rounded-full hover:bg-black/10 focus:outline-none dark:hover:bg-white/10"
            @click="emit('remove')"
        >
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </span>
</template>
