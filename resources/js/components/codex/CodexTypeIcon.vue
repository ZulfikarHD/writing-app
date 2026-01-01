<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        type: string;
        size?: 'sm' | 'md' | 'lg' | 'xl';
    }>(),
    {
        size: 'md',
    },
);

const typeConfig: Record<string, { icon: string }> = {
    character: { icon: '👤' },
    location: { icon: '📍' },
    item: { icon: '⚔️' },
    lore: { icon: '📜' },
    organization: { icon: '🏛️' },
    subplot: { icon: '📖' },
};

const icon = computed(() => typeConfig[props.type]?.icon || '📄');

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'text-sm';
        case 'lg':
            return 'text-2xl';
        case 'xl':
            return 'text-4xl';
        default:
            return 'text-lg';
    }
});
</script>

<template>
    <span :class="sizeClasses" role="img" :aria-label="type">{{ icon }}</span>
</template>
