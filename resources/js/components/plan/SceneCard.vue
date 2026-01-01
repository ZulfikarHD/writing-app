<script setup lang="ts">
import { computed } from 'vue';

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
    subtitle: string | null;
    labels: Label[];
}

const props = defineProps<{
    scene: Scene;
    draggable?: boolean;
}>();

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

const handleContextMenu = (e: MouseEvent) => {
    e.preventDefault();
    emit('contextmenu', e, props.scene);
};
</script>

<template>
    <div
        :class="[
            'group relative cursor-pointer rounded-lg border border-zinc-200 bg-white p-3 shadow-sm transition-all',
            'hover:border-violet-300 hover:shadow-md active:scale-[0.98]',
            'dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-violet-600',
            { 'cursor-grab': draggable },
        ]"
        @click="emit('click', scene)"
        @contextmenu="handleContextMenu"
    >
        <!-- Status indicator -->
        <div class="mb-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span :class="['h-2 w-2 shrink-0 rounded-full', statusColor]" :title="statusLabel" />
                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ statusLabel }}</span>
            </div>
            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ formattedWordCount }}</span>
        </div>

        <!-- Title -->
        <h4 class="line-clamp-1 text-sm font-medium text-zinc-900 dark:text-white">
            {{ scene.title || 'Untitled Scene' }}
        </h4>

        <!-- Subtitle -->
        <p v-if="scene.subtitle" class="mt-0.5 line-clamp-1 text-xs text-zinc-500 dark:text-zinc-400">
            {{ scene.subtitle }}
        </p>

        <!-- Summary -->
        <p v-if="scene.summary" class="mt-2 line-clamp-2 text-xs text-zinc-600 dark:text-zinc-400">
            {{ scene.summary }}
        </p>

        <!-- Labels -->
        <div v-if="scene.labels.length > 0" class="mt-2 flex flex-wrap gap-1">
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
    </div>
</template>
