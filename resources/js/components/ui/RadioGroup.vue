<script setup lang="ts">
import { computed, provide } from 'vue';

interface Option {
    value: string | number;
    label: string;
    description?: string;
    disabled?: boolean;
}

interface Props {
    modelValue?: string | number;
    options: Option[];
    name: string;
    label?: string;
    error?: string;
    disabled?: boolean;
    direction?: 'horizontal' | 'vertical';
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    direction: 'vertical',
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
}>();

provide('radioGroupName', props.name);
provide('radioGroupValue', computed(() => props.modelValue));

const directionClasses = {
    horizontal: 'flex flex-wrap gap-4',
    vertical: 'flex flex-col gap-3',
};
</script>

<template>
    <fieldset :disabled="disabled">
        <legend v-if="label" class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">
            {{ label }}
        </legend>
        <div :class="directionClasses[direction]">
            <div v-for="option in options" :key="option.value" class="flex items-start gap-3">
                <div class="flex h-5 items-center">
                    <input
                        :id="`${name}-${option.value}`"
                        type="radio"
                        :name="name"
                        :value="option.value"
                        :checked="modelValue === option.value"
                        :disabled="disabled || option.disabled"
                        class="h-4 w-4 border-zinc-300 text-violet-600 transition-colors focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-600 dark:bg-zinc-800 dark:focus:ring-offset-zinc-900"
                        @change="emit('update:modelValue', option.value)"
                    />
                </div>
                <div class="flex flex-col">
                    <label
                        :for="`${name}-${option.value}`"
                        class="text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        :class="{ 'cursor-not-allowed opacity-50': disabled || option.disabled }"
                    >
                        {{ option.label }}
                    </label>
                    <p v-if="option.description" class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ option.description }}
                    </p>
                </div>
            </div>
        </div>
        <p v-if="error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </fieldset>
</template>
