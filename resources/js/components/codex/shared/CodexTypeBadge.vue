<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        type: string;
        size?: 'sm' | 'md' | 'lg';
        showIcon?: boolean;
    }>(),
    {
        size: 'md',
        showIcon: true,
    },
);

const typeConfig: Record<string, { label: string; icon: string; color: string }> = {
    character: { label: 'Character', icon: '👤', color: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' },
    location: { label: 'Location', icon: '📍', color: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' },
    item: { label: 'Item', icon: '⚔️', color: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' },
    lore: { label: 'Lore', icon: '📜', color: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' },
    organization: { label: 'Organization', icon: '🏛️', color: 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' },
    subplot: { label: 'Subplot', icon: '📖', color: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' },
};

const config = computed(() => typeConfig[props.type] || { label: props.type, icon: '📄', color: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' });

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-1.5 py-0.5 text-xs';
        case 'lg':
            return 'px-3 py-1.5 text-sm';
        default:
            return 'px-2 py-1 text-xs';
    }
});
</script>

<template>
    <span class="inline-flex items-center gap-1 rounded-full font-medium" :class="[config.color, sizeClasses]">
        <span v-if="showIcon">{{ config.icon }}</span>
        <span>{{ config.label }}</span>
    </span>
</template>
