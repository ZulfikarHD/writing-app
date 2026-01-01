<script setup lang="ts">
import { Motion } from 'motion-v';
import { computed, ref } from 'vue';

interface Label {
    id: number;
    name: string;
    color: string;
}

interface Scene {
    id: number;
    chapter_id: number;
    title: string | null;
    summary: string | null;
    position: number;
    status: string;
    word_count: number;
    pov_character_id: number | null;
    pov_character?: { id: number; name: string } | null;
    subtitle: string | null;
    labels: Label[];
    codex_mentions_count?: number;
    codex_entries_count?: number;
}

interface CardSettings {
    size: 'compact' | 'normal' | 'large';
    showSummary: boolean;
    showLabels: boolean;
    showWordCount: boolean;
    showPov: boolean;
    showCodexMentions?: boolean;
}

const props = withDefaults(
    defineProps<{
        scene: Scene;
        draggable?: boolean;
        cardSettings?: CardSettings;
    }>(),
    {
        draggable: false,
        cardSettings: () => ({
            size: 'normal',
            showSummary: true,
            showLabels: true,
            showWordCount: true,
            showPov: true,
            showCodexMentions: true,
        }),
    }
);

const emit = defineEmits<{
    (e: 'click', scene: Scene): void;
    (e: 'contextmenu', event: MouseEvent, scene: Scene): void;
}>();

const statusColors: Record<string, string> = {
    draft: 'bg-zinc-400',
    in_progress: 'bg-amber-400',
    completed: 'bg-green-400',
    needs_revision: 'bg-red-400',
};

const statusLabels: Record<string, string> = {
    draft: 'Draft',
    in_progress: 'In Progress',
    completed: 'Done',
    needs_revision: 'Needs Revision',
};

const statusColor = computed(() => statusColors[props.scene.status] || 'bg-zinc-400');
const statusLabel = computed(() => statusLabels[props.scene.status] || 'Draft');

const formattedWordCount = computed(() => {
    if (props.scene.word_count >= 1000) {
        return `${(props.scene.word_count / 1000).toFixed(1)}k`;
    }
    return props.scene.word_count.toString();
});

// Card size classes
const cardSizeClasses = computed(() => {
    switch (props.cardSettings?.size) {
        case 'compact':
            return 'p-2';
        case 'large':
            return 'p-4';
        default:
            return 'p-3';
    }
});

const summaryLineClamp = computed(() => {
    switch (props.cardSettings?.size) {
        case 'compact':
            return 'line-clamp-1';
        case 'large':
            return 'line-clamp-4';
        default:
            return 'line-clamp-2';
    }
});

const handleContextMenu = (e: MouseEvent) => {
    e.preventDefault();
    emit('contextmenu', e, props.scene);
};

// Press feedback state
const isPressed = ref(false);
</script>

<template>
    <Motion
        :animate="{ scale: isPressed ? 0.97 : 1 }"
        :transition="{ type: 'spring', stiffness: 400, damping: 30 }"
    >
        <div
            :class="[
                'group relative cursor-pointer rounded-lg border border-zinc-200 bg-white shadow-sm transition-all',
                'hover:border-violet-300 hover:shadow-md',
                'dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-violet-600',
                cardSizeClasses,
                { 'cursor-grab': draggable },
            ]"
            @click="emit('click', scene)"
            @contextmenu="handleContextMenu"
            @mousedown="isPressed = true"
            @mouseup="isPressed = false"
            @mouseleave="isPressed = false"
            @touchstart="isPressed = true"
            @touchend="isPressed = false"
        >
            <!-- Status indicator -->
            <div class="mb-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span :class="['h-2 w-2 shrink-0 rounded-full', statusColor]" :title="statusLabel" />
                    <span v-if="cardSettings?.size !== 'compact'" class="text-xs text-zinc-500 dark:text-zinc-400">{{ statusLabel }}</span>
                </div>
                <span v-if="cardSettings?.showWordCount !== false" class="text-xs text-zinc-400 dark:text-zinc-500">{{ formattedWordCount }}</span>
            </div>

            <!-- Title -->
            <h4 class="line-clamp-1 text-sm font-medium text-zinc-900 dark:text-white">
                {{ scene.title || 'Untitled Scene' }}
            </h4>

            <!-- POV Character Badge -->
            <div
                v-if="cardSettings?.showPov !== false && scene.pov_character"
                class="mt-1 inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
            >
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ scene.pov_character.name }}
            </div>

            <!-- Subtitle -->
            <p v-if="scene.subtitle && cardSettings?.size !== 'compact'" class="mt-0.5 line-clamp-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ scene.subtitle }}
            </p>

            <!-- Summary -->
            <p v-if="cardSettings?.showSummary !== false && scene.summary" :class="['mt-2 text-xs text-zinc-600 dark:text-zinc-400', summaryLineClamp]">
                {{ scene.summary }}
            </p>

            <!-- Labels -->
            <div v-if="cardSettings?.showLabels !== false && scene.labels.length > 0" class="mt-2 flex flex-wrap gap-1">
                <span
                    v-for="label in scene.labels.slice(0, 3)"
                    :key="label.id"
                    :style="{ backgroundColor: label.color + '20', color: label.color }"
                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                >
                    {{ label.name }}
                </span>
                <span v-if="scene.labels.length > 3" class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">
                    +{{ scene.labels.length - 3 }}
                </span>
            </div>

            <!-- Codex Mentions Badge -->
            <div
                v-if="cardSettings?.showCodexMentions !== false && scene.codex_mentions_count && scene.codex_mentions_count > 0"
                class="absolute right-2 top-2 flex items-center gap-1 rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-900/40 dark:text-violet-300"
                :title="`${scene.codex_mentions_count} codex mentions from ${scene.codex_entries_count} entries`"
            >
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                    />
                </svg>
                {{ scene.codex_mentions_count }}
            </div>
        </div>
    </Motion>
</template>
