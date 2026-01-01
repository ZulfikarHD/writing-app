<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue?: boolean;
    label?: string;
    description?: string;
    disabled?: boolean;
    size?: 'sm' | 'md' | 'lg';
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    disabled: false,
    size: 'md',
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

const inputId = computed(() => props.id || `toggle-${Math.random().toString(36).substring(7)}`);

const sizeClasses = {
    sm: {
        toggle: 'h-5 w-9',
        dot: 'h-4 w-4',
        translate: 'translate-x-4',
    },
    md: {
        toggle: 'h-6 w-11',
        dot: 'h-5 w-5',
        translate: 'translate-x-5',
    },
    lg: {
        toggle: 'h-7 w-14',
        dot: 'h-6 w-6',
        translate: 'translate-x-7',
    },
};

const toggle = () => {
    if (!props.disabled) {
        emit('update:modelValue', !props.modelValue);
    }
};
</script>

<template>
    <div class="flex items-start gap-3">
        <button
            :id="inputId"
            type="button"
            role="switch"
            :aria-checked="modelValue"
            :disabled="disabled"
            :class="[
                sizeClasses[size].toggle,
                'relative inline-flex shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900',
                modelValue ? 'bg-violet-600' : 'bg-zinc-200 dark:bg-zinc-700',
                disabled ? 'cursor-not-allowed opacity-50' : '',
            ]"
            @click="toggle"
        >
            <span
                :class="[
                    sizeClasses[size].dot,
                    'pointer-events-none inline-block transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                    modelValue ? sizeClasses[size].translate : 'translate-x-0',
                ]"
            />
        </button>
        <div v-if="label || description" class="flex flex-col">
            <label
                :for="inputId"
                class="text-sm font-medium text-zinc-700 dark:text-zinc-300"
                :class="{ 'cursor-not-allowed opacity-50': disabled }"
                @click="toggle"
            >
                {{ label }}
            </label>
            <p v-if="description" class="text-xs text-zinc-500 dark:text-zinc-400">
                {{ description }}
            </p>
        </div>
    </div>
</template>
