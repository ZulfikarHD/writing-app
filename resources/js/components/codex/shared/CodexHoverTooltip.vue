<script setup lang="ts">
/**
 * CodexHoverTooltip - Preview codex entries on hover/tap
 *
 * Sprint 15 (US-12.10): Shows a quick preview of codex entries when hovering
 * over highlighted mentions in the editor. Supports both desktop (hover) and
 * mobile (tap) interactions.
 *
 * Key features:
 * - Lazy loading (only fetches when shown)
 * - Session caching to reduce API calls
 * - Auto-positioning to avoid viewport overflow
 * - Mobile support with tap-to-show, tap-away-to-close
 *
 * @see https://www.novelcrafter.com/help/docs/codex/anatomy-codex-entry
 */
import { Motion } from 'motion-v';
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import CodexTypeIcon from './CodexTypeIcon.vue';

interface Detail {
    key: string;
    value: string;
}

interface EntryData {
    id: number;
    type: string;
    name: string;
    description: string | null;
    thumbnail_url: string | null;
    aliases: string[];
    details: Detail[];
}

const props = defineProps<{
    show: boolean;
    entryId: number | null;
    position: { x: number; y: number };
    targetElement?: HTMLElement | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'open-entry', entryId: number): void;
}>();

// State
const loading = ref(false);
const error = ref<string | null>(null);
const entryData = ref<EntryData | null>(null);
const tooltipEl = ref<HTMLElement | null>(null);
const adjustedPosition = ref({ x: 0, y: 0 });

// Cache for session (avoid repeated API calls)
const cache = new Map<number, EntryData>();

// Computed
const hasData = computed(() => entryData.value !== null);
const truncatedDescription = computed(() => {
    if (!entryData.value?.description) return null;
    const maxLength = 150;
    if (entryData.value.description.length <= maxLength) {
        return entryData.value.description;
    }
    return entryData.value.description.substring(0, maxLength) + '...';
});

const visibleDetails = computed(() => {
    if (!entryData.value?.details) return [];
    // Show first 3 details
    return entryData.value.details.slice(0, 3);
});

// Watch for visibility and fetch data
watch(() => props.show, async (isVisible) => {
    if (isVisible && props.entryId) {
        await fetchEntryData(props.entryId);
        await nextTick();
        calculatePosition();
    } else {
        // Don't clear entryData immediately - allows fade out
    }
});

// Watch for position changes
watch(() => props.position, () => {
    if (props.show) {
        calculatePosition();
    }
}, { deep: true });

const fetchEntryData = async (entryId: number) => {
    // Check cache first
    if (cache.has(entryId)) {
        entryData.value = cache.get(entryId)!;
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(`/api/codex/${entryId}`);
        const data: EntryData = {
            id: response.data.entry.id,
            type: response.data.entry.type,
            name: response.data.entry.name,
            description: response.data.entry.description,
            thumbnail_url: response.data.entry.thumbnail_url,
            aliases: response.data.entry.aliases?.map((a: { alias: string }) => a.alias) || [],
            details: response.data.entry.details?.map((d: { key_name: string; value: string }) => ({
                key: d.key_name,
                value: d.value,
            })) || [],
        };

        // Cache the result
        cache.set(entryId, data);
        entryData.value = data;
    } catch {
        error.value = 'Failed to load entry';
    } finally {
        loading.value = false;
    }
};

const calculatePosition = () => {
    if (!tooltipEl.value) return;

    const tooltip = tooltipEl.value;
    const tooltipRect = tooltip.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    const padding = 10;

    let x = props.position.x;
    let y = props.position.y;

    // Horizontal positioning - prefer right, fallback to left
    if (x + tooltipRect.width + padding > viewportWidth) {
        x = Math.max(padding, x - tooltipRect.width - padding);
    }

    // Vertical positioning - prefer below, fallback to above
    if (y + tooltipRect.height + padding > viewportHeight) {
        y = Math.max(padding, y - tooltipRect.height - padding);
    }

    // Ensure within bounds
    x = Math.max(padding, Math.min(x, viewportWidth - tooltipRect.width - padding));
    y = Math.max(padding, Math.min(y, viewportHeight - tooltipRect.height - padding));

    adjustedPosition.value = { x, y };
};

const handleOpenEntry = () => {
    if (entryData.value) {
        emit('open-entry', entryData.value.id);
        router.visit(`/codex/${entryData.value.id}`);
    }
};

const handleClose = () => {
    emit('close');
};

// Handle click outside for mobile
const handleClickOutside = (event: MouseEvent) => {
    if (props.show && tooltipEl.value && !tooltipEl.value.contains(event.target as Node)) {
        // Check if click was on the target element (mention)
        if (props.targetElement && props.targetElement.contains(event.target as Node)) {
            return;
        }
        handleClose();
    }
};

// Handle escape key
const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && props.show) {
        handleClose();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Motion
            v-if="show && entryId"
            ref="tooltipEl"
            :initial="{ opacity: 0, scale: 0.95 }"
            :animate="{ opacity: 1, scale: 1 }"
            :exit="{ opacity: 0, scale: 0.95 }"
            :transition="{ type: 'spring', stiffness: 500, damping: 35, duration: 0.2 }"
            class="fixed z-50 w-72 rounded-lg border border-zinc-200 bg-white p-4 shadow-xl dark:border-zinc-700 dark:bg-zinc-800"
            :style="{
                left: `${adjustedPosition.x}px`,
                top: `${adjustedPosition.y}px`,
            }"
            @click.stop
        >
                <!-- Loading state -->
                <div v-if="loading" class="flex items-center justify-center py-6">
                    <div class="h-5 w-5 animate-spin rounded-full border-2 border-violet-500 border-t-transparent" />
                </div>

                <!-- Error state -->
                <div v-else-if="error" class="text-center text-sm text-red-500 dark:text-red-400">
                    {{ error }}
                </div>

                <!-- Entry data -->
                <div v-else-if="hasData">
                    <!-- Header -->
                    <div class="flex items-start gap-3">
                        <CodexTypeIcon :type="entryData!.type" size="lg" />
                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ entryData!.name }}
                            </h4>
                            <p class="text-xs text-zinc-500 capitalize dark:text-zinc-400">
                                {{ entryData!.type }}
                            </p>
                        </div>
                    </div>

                    <!-- Aliases -->
                    <div v-if="entryData!.aliases.length > 0" class="mt-2">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="font-medium">Also known as:</span>
                            {{ entryData!.aliases.slice(0, 3).join(', ') }}
                            <span v-if="entryData!.aliases.length > 3">
                                +{{ entryData!.aliases.length - 3 }} more
                            </span>
                        </p>
                    </div>

                    <!-- Description -->
                    <p v-if="truncatedDescription" class="mt-2 text-xs leading-relaxed text-zinc-600 dark:text-zinc-300">
                        {{ truncatedDescription }}
                    </p>

                    <!-- Key Details -->
                    <div v-if="visibleDetails.length > 0" class="mt-3 space-y-1 border-t border-zinc-100 pt-2 dark:border-zinc-700">
                        <div v-for="detail in visibleDetails" :key="detail.key" class="flex gap-2 text-xs">
                            <span class="shrink-0 font-medium text-zinc-500 dark:text-zinc-400">
                                {{ detail.key }}:
                            </span>
                            <span class="truncate text-zinc-700 dark:text-zinc-300">
                                {{ detail.value }}
                            </span>
                        </div>
                    </div>

                    <!-- Open Full Entry -->
                    <button
                        type="button"
                        class="mt-3 flex w-full items-center justify-center gap-1 rounded-md bg-violet-50 px-3 py-1.5 text-xs font-medium text-violet-700 transition-colors hover:bg-violet-100 dark:bg-violet-900/30 dark:text-violet-300 dark:hover:bg-violet-900/50"
                        @click="handleOpenEntry"
                    >
                        Open Full Entry
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </button>
                </div>
        </Motion>
    </Teleport>
</template>
