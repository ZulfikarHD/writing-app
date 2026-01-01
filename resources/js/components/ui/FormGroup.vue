<script setup lang="ts">
interface Props {
    label?: string;
    error?: string;
    hint?: string;
    required?: boolean;
    htmlFor?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-2">
        <label
            v-if="label"
            :for="htmlFor"
            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
        >
            {{ label }}
            <span v-if="required" class="ml-0.5 text-red-500">*</span>
        </label>
        <slot />
        <p v-if="hint && !error" class="text-xs text-zinc-500 dark:text-zinc-400">
            {{ hint }}
        </p>
        <p v-if="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
</template>
