<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Mention {
    id: number;
    mention_count: number;
    scene: {
        id: number;
        title: string | null;
        chapter: { id: number; title: string } | null;
    };
}

const props = defineProps<{
    mentions: Mention[];
    novelId: number;
}>();

// Tooltip state
const hoveredSegment = ref<number | null>(null);
const tooltipPosition = ref({ x: 0, y: 0 });

// Calculate total mentions
const totalMentions = computed(() => props.mentions.reduce((sum, m) => sum + m.mention_count, 0));

// Get max mention count for scaling
const maxMentions = computed(() => Math.max(...props.mentions.map((m) => m.mention_count), 1));

// Generate heatmap segments (simplified view showing relative density)
const heatmapSegments = computed(() => {
    if (props.mentions.length === 0) return [];

    return props.mentions.map((mention) => ({
        ...mention,
        // Intensity from 0.2 to 1 based on mention count
        intensity: 0.2 + (mention.mention_count / maxMentions.value) * 0.8,
    }));
});

// Get intensity color class
const getIntensityColor = (intensity: number): string => {
    if (intensity >= 0.8) return 'bg-violet-600 dark:bg-violet-500';
    if (intensity >= 0.6) return 'bg-violet-500 dark:bg-violet-400';
    if (intensity >= 0.4) return 'bg-violet-400 dark:bg-violet-500/70';
    return 'bg-violet-300 dark:bg-violet-600/50';
};

// Navigate to scene when segment is clicked
const navigateToScene = (sceneId: number) => {
    router.visit(`/novels/${props.novelId}/write/${sceneId}`);
};

// Handle hover for tooltip
const handleMouseEnter = (segmentId: number, event: MouseEvent) => {
    hoveredSegment.value = segmentId;
    const target = event.target as HTMLElement;
    const rect = target.getBoundingClientRect();
    tooltipPosition.value = {
        x: rect.left + rect.width / 2,
        y: rect.top,
    };
};

const handleMouseLeave = () => {
    hoveredSegment.value = null;
};

// Get hovered segment data
const hoveredSegmentData = computed(() => {
    if (hoveredSegment.value === null) return null;
    return heatmapSegments.value.find((s) => s.id === hoveredSegment.value) || null;
});
</script>

<template>
    <div v-if="mentions.length > 0" class="space-y-2">
        <!-- Summary -->
        <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
                />
            </svg>
            <span>
                <strong class="text-zinc-900 dark:text-white">{{ totalMentions }}</strong>
                {{ totalMentions === 1 ? 'mention' : 'mentions' }} across
                <strong class="text-zinc-900 dark:text-white">{{ mentions.length }}</strong>
                {{ mentions.length === 1 ? 'scene' : 'scenes' }}
            </span>
        </div>

        <!-- Heatmap Bar (Clickable) -->
        <div class="relative">
            <div class="flex h-3 w-full gap-0.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                <button
                    v-for="segment in heatmapSegments"
                    :key="segment.id"
                    type="button"
                    :class="[
                        'h-full min-w-1.5 flex-1 cursor-pointer transition-all',
                        'hover:scale-y-125 hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-1',
                        getIntensityColor(segment.intensity),
                    ]"
                    :aria-label="`Go to ${segment.scene.title || 'Untitled'} (${segment.mention_count} mentions)`"
                    @click="navigateToScene(segment.scene.id)"
                    @mouseenter="handleMouseEnter(segment.id, $event)"
                    @mouseleave="handleMouseLeave"
                />
            </div>

            <!-- Tooltip -->
            <Teleport to="body">
                <Transition
                    enter-active-class="transition-opacity duration-150"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-opacity duration-100"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="hoveredSegmentData"
                        class="pointer-events-none fixed z-50 -translate-x-1/2 -translate-y-full rounded-lg bg-zinc-900 px-3 py-2 text-sm text-white shadow-lg dark:bg-zinc-700"
                        :style="{ left: `${tooltipPosition.x}px`, top: `${tooltipPosition.y - 8}px` }"
                    >
                        <p class="font-medium">
                            {{ hoveredSegmentData.scene.chapter?.title ? `${hoveredSegmentData.scene.chapter.title} -` : '' }}
                            {{ hoveredSegmentData.scene.title || 'Untitled' }}
                        </p>
                        <p class="text-violet-300">
                            {{ hoveredSegmentData.mention_count }} {{ hoveredSegmentData.mention_count === 1 ? 'mention' : 'mentions' }}
                        </p>
                        <p class="mt-1 text-xs text-zinc-400">Click to open in editor</p>
                        <!-- Arrow -->
                        <div class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-zinc-900 dark:border-t-zinc-700" />
                    </div>
                </Transition>
            </Teleport>
        </div>

        <!-- Legend -->
        <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
            <span>Fewer mentions</span>
            <div class="flex items-center gap-1">
                <div class="h-2 w-4 rounded bg-violet-300 dark:bg-violet-600/50" />
                <div class="h-2 w-4 rounded bg-violet-400 dark:bg-violet-500/70" />
                <div class="h-2 w-4 rounded bg-violet-500 dark:bg-violet-400" />
                <div class="h-2 w-4 rounded bg-violet-600 dark:bg-violet-500" />
            </div>
            <span>More mentions</span>
        </div>

        <!-- Click hint -->
        <p class="text-xs text-zinc-400 dark:text-zinc-500">
            Click on any segment to open the scene in the editor
        </p>
    </div>

    <!-- Empty State -->
    <div v-else class="text-sm text-zinc-400 italic dark:text-zinc-500">
        Not mentioned in any scenes yet
    </div>
</template>
