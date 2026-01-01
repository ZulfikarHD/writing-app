<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue?: string;
    label?: string;
    error?: string;
    placeholder?: string;
    disabled?: boolean;
    required?: boolean;
    id?: string;
    rows?: number;
    maxLength?: number;
    variant?: 'default' | 'subtle';
    resize?: 'none' | 'vertical' | 'horizontal' | 'both';
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    disabled: false,
    required: false,
    rows: 4,
    variant: 'default',
    resize: 'vertical',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const inputId = computed(() => props.id || `textarea-${Math.random().toString(36).substring(7)}`);

const resizeClasses = {
    none: 'resize-none',
    vertical: 'resize-y',
    horizontal: 'resize-x',
    both: 'resize',
};

const textareaClasses = computed(() => {
    const base =
        'block w-full rounded-lg border px-3.5 py-2.5 text-sm text-zinc-900 transition-all placeholder:text-zinc-400 focus:outline-none disabled:cursor-not-allowed disabled:bg-zinc-50 disabled:text-zinc-500 dark:text-white dark:disabled:bg-zinc-800';

    const variants = {
        default: props.error
            ? 'border-red-300 bg-white focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-red-600 dark:bg-zinc-900'
            : 'border-zinc-200 bg-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:focus:border-violet-500',
        subtle: props.error
            ? 'border-red-200 bg-zinc-50 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-red-800 dark:bg-zinc-800/50'
            : 'border-zinc-100 bg-zinc-50 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:bg-white dark:border-zinc-800 dark:bg-zinc-800/50 dark:focus:bg-zinc-900',
    };

    return `${base} ${variants[props.variant]} ${resizeClasses[props.resize]}`;
});

const characterCount = computed(() => props.modelValue?.length || 0);
</script>

<template>
    <div class="space-y-2">
        <div v-if="label || maxLength" class="flex items-center justify-between">
            <label v-if="label" :for="inputId" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ label }}
                <span v-if="required" class="ml-0.5 text-red-500">*</span>
            </label>
            <span v-if="maxLength" class="text-xs text-zinc-400 dark:text-zinc-500">
                {{ characterCount }}/{{ maxLength }}
            </span>
        </div>
        <textarea
            :id="inputId"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :rows="rows"
            :maxlength="maxLength"
            :class="textareaClasses"
            @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        />
        <p v-if="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
</template>
