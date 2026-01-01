<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    variant?: 'primary' | 'secondary' | 'danger' | 'ghost' | 'outline' | 'success' | 'warning';
    size?: 'xs' | 'sm' | 'md' | 'lg';
    disabled?: boolean;
    loading?: boolean;
    type?: 'button' | 'submit' | 'reset';
    fullWidth?: boolean;
    iconOnly?: boolean;
    rounded?: 'default' | 'full';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'md',
    disabled: false,
    loading: false,
    type: 'button',
    fullWidth: false,
    iconOnly: false,
    rounded: 'default',
});

const classes = computed(() => {
    const base =
        'inline-flex items-center justify-center font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-[0.97] disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 dark:focus:ring-offset-zinc-900';

    const variants = {
        primary:
            'bg-violet-600 text-white shadow-sm hover:bg-violet-700 focus:ring-violet-500 hover:shadow-md',
        secondary:
            'bg-zinc-100 text-zinc-900 hover:bg-zinc-200 focus:ring-zinc-400 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700',
        danger: 'bg-red-600 text-white shadow-sm hover:bg-red-700 focus:ring-red-500 hover:shadow-md',
        ghost: 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 focus:ring-zinc-400 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-800',
        outline:
            'border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-300 dark:hover:bg-zinc-800',
        success: 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 focus:ring-emerald-500 hover:shadow-md',
        warning: 'bg-amber-500 text-white shadow-sm hover:bg-amber-600 focus:ring-amber-500 hover:shadow-md',
    };

    const sizes = props.iconOnly
        ? {
              xs: 'p-1',
              sm: 'p-1.5',
              md: 'p-2',
              lg: 'p-2.5',
          }
        : {
              xs: 'px-2 py-1 text-xs gap-1',
              sm: 'px-3 py-1.5 text-xs gap-1.5',
              md: 'px-4 py-2.5 text-sm gap-2',
              lg: 'px-6 py-3 text-base gap-2',
          };

    const roundedClasses = {
        default: 'rounded-lg',
        full: 'rounded-full',
    };

    const widthClass = props.fullWidth ? 'w-full' : '';

    return `${base} ${variants[props.variant]} ${sizes[props.size]} ${roundedClasses[props.rounded]} ${widthClass}`;
});
</script>

<template>
    <button :type="type" :class="classes" :disabled="disabled || loading">
        <svg v-if="loading" class="-ml-0.5 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
        </svg>
        <slot />
    </button>
</template>
