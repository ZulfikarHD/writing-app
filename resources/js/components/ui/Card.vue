<script setup lang="ts">
interface Props {
    padding?: boolean;
    variant?: 'default' | 'bordered' | 'flat';
}

const props = withDefaults(defineProps<Props>(), {
    padding: true,
    variant: 'default',
});

const variantClasses = {
    default: 'border border-zinc-100 bg-white shadow-sm dark:border-zinc-800/50 dark:bg-zinc-900',
    bordered: 'border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900',
    flat: 'bg-zinc-50 dark:bg-zinc-900/50',
};
</script>

<template>
    <div class="rounded-xl" :class="[variantClasses[props.variant], { 'p-6': props.padding }]">
        <slot />
    </div>
</template>
