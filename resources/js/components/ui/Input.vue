<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue?: string;
    type?: string;
    label?: string;
    error?: string;
    placeholder?: string;
    disabled?: boolean;
    required?: boolean;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    type: 'text',
    disabled: false,
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const inputId = computed(() => props.id || `input-${Math.random().toString(36).substring(7)}`);

const inputClasses = computed(() => {
    const base = 'block w-full rounded-lg border px-3 py-2 text-sm transition-colors placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-50';
    const normal = 'border-zinc-300 bg-white focus:border-violet-500 focus:ring-violet-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-violet-500';
    const hasError = 'border-red-500 focus:border-red-500 focus:ring-red-500/20';

    return `${base} ${props.error ? hasError : normal}`;
});
</script>

<template>
    <div class="space-y-1.5">
        <label v-if="label" :for="inputId" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <input
            :id="inputId"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :class="inputClasses"
            @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />
        <p v-if="error" class="text-sm text-red-500">{{ error }}</p>
    </div>
</template>
