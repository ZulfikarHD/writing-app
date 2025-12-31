<script setup lang="ts">
defineProps<{
    padding?: boolean;
}>();
</script>

<template>
    <div
        class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
        :class="{ 'p-6': padding !== false }"
    >
        <slot />
    </div>
</template>
