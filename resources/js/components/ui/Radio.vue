<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue?: string | number;
    value: string | number;
    label?: string;
    description?: string;
    error?: string;
    disabled?: boolean;
    name: string;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
}>();

const inputId = computed(() => props.id || `radio-${Math.random().toString(36).substring(7)}`);

const isChecked = computed(() => props.modelValue === props.value);
</script>

<template>
    <div class="relative">
        <div class="flex items-start gap-3">
            <div class="flex h-5 items-center">
                <input
                    :id="inputId"
                    type="radio"
                    :name="name"
                    :value="value"
                    :checked="isChecked"
                    :disabled="disabled"
                    class="h-4 w-4 border-zinc-300 text-violet-600 transition-colors focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-600 dark:bg-zinc-800 dark:focus:ring-offset-zinc-900"
                    @change="emit('update:modelValue', value)"
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
