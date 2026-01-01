<script setup lang="ts">
import { Motion } from 'motion-v';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

interface EntryInfo {
    id: number;
    type: string;
    name: string;
    description: string | null;
}

const props = defineProps<{
    entry: EntryInfo | null;
    targetRect: DOMRect | null;
    containerRef: HTMLElement | null;
}>();

const emit = defineEmits<{
    (e: 'click', entryId: number): void;
    (e: 'close'): void;
}>();

const tooltipRef = ref<HTMLElement | null>(null);
const position = ref({ top: 0, left: 0, placement: 'bottom' as 'top' | 'bottom' });

// Type configuration
const typeConfig: Record<string, { label: string; icon: string; color: string; borderColor: string }> = {
    character: {
        label: 'Character',
        icon: '👤',
        color: 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
        borderColor: 'border-purple-200 dark:border-purple-800',
    },
    location: {
        label: 'Location',
        icon: '📍',
        color: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        borderColor: 'border-blue-200 dark:border-blue-800',
    },
    item: {
        label: 'Item',
        icon: '⚔️',
        color: 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        borderColor: 'border-amber-200 dark:border-amber-800',
    },
    lore: {
        label: 'Lore',
        icon: '📜',
        color: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        borderColor: 'border-emerald-200 dark:border-emerald-800',
    },
    organization: {
        label: 'Organization',
        icon: '🏛️',
        color: 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
        borderColor: 'border-rose-200 dark:border-rose-800',
    },
    subplot: {
        label: 'Subplot',
        icon: '📖',
        color: 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
        borderColor: 'border-cyan-200 dark:border-cyan-800',
    },
};

const getTypeConfig = (type: string) => typeConfig[type] || {
    label: type,
    icon: '📄',
    color: 'bg-zinc-50 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
    borderColor: 'border-zinc-200 dark:border-zinc-700',
};

const typeInfo = computed(() => props.entry ? getTypeConfig(props.entry.type) : null);

const truncatedDescription = computed(() => {
    if (!props.entry?.description) return null;
    const desc = props.entry.description;
    if (desc.length <= 150) return desc;
    return desc.substring(0, 150).trim() + '...';
});

// Calculate position relative to target element
const calculatePosition = () => {
    if (!props.targetRect || !tooltipRef.value || !props.containerRef) return;

    const tooltip = tooltipRef.value;
    const rect = props.targetRect;
    const containerRect = props.containerRef.getBoundingClientRect();
    
    const tooltipWidth = 280;
    const tooltipHeight = tooltip.offsetHeight || 120;
    const margin = 8;

    // Calculate horizontal position (centered on target, but within container bounds)
    let left = rect.left + (rect.width / 2) - (tooltipWidth / 2);
    
    // Keep within container bounds
    if (left < containerRect.left + margin) {
        left = containerRect.left + margin;
    } else if (left + tooltipWidth > containerRect.right - margin) {
        left = containerRect.right - tooltipWidth - margin;
    }

    // Calculate vertical position (prefer below, fall back to above)
    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;

    let top: number;
    let placement: 'top' | 'bottom';

    if (spaceBelow >= tooltipHeight + margin || spaceBelow >= spaceAbove) {
        // Position below
        top = rect.bottom + margin;
        placement = 'bottom';
    } else {
        // Position above
        top = rect.top - tooltipHeight - margin;
        placement = 'top';
    }

    position.value = { top, left, placement };
};

// Watch for changes and recalculate position
watch(() => [props.targetRect, props.entry], () => {
    if (props.entry && props.targetRect) {
        requestAnimationFrame(calculatePosition);
    }
}, { immediate: true });

// Handle click on tooltip
const handleClick = () => {
    if (props.entry) {
        emit('click', props.entry.id);
    }
};

// Close on escape key
const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        emit('close');
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
    calculatePosition();
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <Teleport to="body">
        <Motion
            v-if="entry && typeInfo"
            ref="tooltipRef"
            :initial="{ opacity: 0, scale: 0.95 }"
            :animate="{ opacity: 1, scale: 1 }"
            :exit="{ opacity: 0, scale: 0.95 }"
            :transition="{ type: 'spring', stiffness: 500, damping: 35, duration: 0.2 }"
            class="fixed z-50 w-70 cursor-pointer rounded-lg border bg-white p-3 shadow-lg dark:bg-zinc-800"
            :class="typeInfo.borderColor"
            :style="{
                top: `${position.top}px`,
                left: `${position.left}px`,
            }"
            @click="handleClick"
        >
                <!-- Arrow -->
                <div
                    class="absolute h-2 w-2 rotate-45 border bg-white dark:bg-zinc-800"
                    :class="[
                        typeInfo.borderColor,
                        position.placement === 'bottom'
                            ? '-top-1 left-1/2 -translate-x-1/2 border-r-0 border-b-0'
                            : '-bottom-1 left-1/2 -translate-x-1/2 border-l-0 border-t-0',
                    ]"
                />

                <!-- Header -->
                <div class="flex items-start gap-2">
                    <span
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-sm"
                        :class="typeInfo.color"
                    >
                        {{ typeInfo.icon }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate font-medium text-zinc-900 dark:text-white">
                            {{ entry.name }}
                        </h3>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ typeInfo.label }}
                        </span>
                    </div>
                </div>

                <!-- Description Preview -->
                <p
                    v-if="truncatedDescription"
                    class="mt-2 line-clamp-3 text-sm text-zinc-600 dark:text-zinc-300"
                >
                    {{ truncatedDescription }}
                </p>
                <p
                    v-else
                    class="mt-2 text-sm text-zinc-400 italic dark:text-zinc-500"
                >
                    No description
                </p>

                <!-- Click hint -->
                <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">
                    Click to view details
                </p>
        </Motion>
    </Teleport>
</template>
