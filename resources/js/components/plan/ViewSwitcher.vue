<script setup lang="ts">
import { Motion } from 'motion-v';
import { computed } from 'vue';

type ViewType = 'grid' | 'matrix' | 'outline';

const props = defineProps<{
    modelValue: ViewType;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: ViewType): void;
}>();

const views = [
    {
        id: 'grid' as const,
        label: 'Grid',
        icon: `<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
        </svg>`,
    },
    {
        id: 'matrix' as const,
        label: 'Matrix',
        icon: `<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>`,
    },
    {
        id: 'outline' as const,
        label: 'Outline',
        icon: `<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>`,
    },
];

const activeIndex = computed(() => views.findIndex((v) => v.id === props.modelValue));
</script>

<template>
    <div class="relative inline-flex items-center rounded-lg border border-zinc-200 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-800">
        <!-- Background slider -->
        <Motion
            :animate="{ x: `${activeIndex * 100}%` }"
            :transition="{ type: 'spring', stiffness: 500, damping: 40 }"
            class="absolute left-1 h-[calc(100%-0.5rem)] w-[calc(33.333%-0.25rem)] rounded-md bg-violet-100 dark:bg-violet-900/40"
        />

        <!-- Buttons -->
        <button
            v-for="view in views"
            :key="view.id"
            type="button"
            :class="[
                'relative z-10 flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                modelValue === view.id
                    ? 'text-violet-700 dark:text-violet-300'
                    : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200',
            ]"
            :title="view.label"
            @click="emit('update:modelValue', view.id)"
        >
            <!-- eslint-disable-next-line vue/no-v-html -->
            <span v-html="view.icon" />
            <span class="hidden sm:inline">{{ view.label }}</span>
        </button>
    </div>
</template>
