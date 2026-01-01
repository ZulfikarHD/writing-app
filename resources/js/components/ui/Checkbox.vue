<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue?: boolean;
    label?: string;
    description?: string;
    error?: string;
    disabled?: boolean;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    disabled: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

const inputId = computed(() => props.id || `checkbox-${Math.random().toString(36).substring(7)}`);
</script>

<template>
    <div class="relative">
        <div class="flex items-start gap-3">
            <div class="flex h-5 items-center">
                <input
                    :id="inputId"
                    type="checkbox"
                    :checked="modelValue"
                    :disabled="disabled"
                    class="h-4 w-4 rounded border-zinc-300 text-violet-600 transition-colors focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-600 dark:bg-zinc-800 dark:focus:ring-offset-zinc-900"
                    @change="emit('update:modelValue', ($event.target as HTMLInputElement).checked)"
                />
            </div>
            <div v-if="label || description" class="flex flex-col">
                <label
                    v-if="label"
                    :for="inputId"
                    class="text-sm font-medium text-zinc-700 dark:text-zinc-300"
                    :class="{ 'cursor-not-allowed opacity-50': disabled }"
                >
                    {{ label }}
                </label>
                <p v-if="description" class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ description }}
                </p>
            </div>
        </div>
        <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
</template>
