<script setup lang="ts">
import { computed } from 'vue';

interface Option {
    value: string | number;
    label: string;
    disabled?: boolean;
}

interface Props {
    modelValue?: string | number;
    options: Option[];
    label?: string;
    error?: string;
    placeholder?: string;
    disabled?: boolean;
    required?: boolean;
    id?: string;
    variant?: 'default' | 'subtle';
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    disabled: false,
    required: false,
    variant: 'default',
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
}>();

const inputId = computed(() => props.id || `select-${Math.random().toString(36).substring(7)}`);

const selectClasses = computed(() => {
    const base =
        'block w-full appearance-none rounded-lg border px-3.5 py-2.5 pr-10 text-sm text-zinc-900 transition-all focus:outline-none disabled:cursor-not-allowed disabled:bg-zinc-50 disabled:text-zinc-500 dark:text-white dark:disabled:bg-zinc-800';

    const variants = {
        default: props.error
            ? 'border-red-300 bg-white focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-red-600 dark:bg-zinc-900'
            : 'border-zinc-200 bg-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:focus:border-violet-500',
        subtle: props.error
            ? 'border-red-200 bg-zinc-50 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-red-800 dark:bg-zinc-800/50'
            : 'border-zinc-100 bg-zinc-50 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:bg-white dark:border-zinc-800 dark:bg-zinc-800/50 dark:focus:bg-zinc-900',
    };

    return `${base} ${variants[props.variant]}`;
});
</script>

<template>
    <div class="space-y-2">
        <label v-if="label" :for="inputId" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
            {{ label }}
            <span v-if="required" class="ml-0.5 text-red-500">*</span>
        </label>
        <div class="relative">
            <select
                :id="inputId"
                :value="modelValue"
                :disabled="disabled"
                :required="required"
                :class="selectClasses"
                @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
            >
                <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
                <option
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                    :disabled="option.disabled"
                >
                    {{ option.label }}
                </option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
        <p v-if="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
</template>
