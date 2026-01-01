<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
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
}

interface Chapter {
    id: number;
    title: string;
    position: number;
    act_id: number | null;
    word_count: number;
    scenes: Scene[];
}

interface Act {
    id: number;
    title: string;
    position: number;
}

const props = defineProps<{
    novelId: number;
    chapters: Chapter[];
    acts: Act[];
    isSearching: boolean;
    hasFilters: boolean;
}>();

const emit = defineEmits<{
    (e: 'sceneClick', scene: Scene): void;
}>();

// Track expanded state for acts and chapters
const expandedActs = ref<Set<number>>(new Set(props.acts.map((a) => a.id)));
const expandedChapters = ref<Set<number>>(new Set(props.chapters.map((c) => c.id)));

const toggleAct = (actId: number) => {
    if (expandedActs.value.has(actId)) {
        expandedActs.value.delete(actId);
    } else {
        expandedActs.value.add(actId);
    }
};

const toggleChapter = (chapterId: number) => {
    if (expandedChapters.value.has(chapterId)) {
        expandedChapters.value.delete(chapterId);
    } else {
        expandedChapters.value.add(chapterId);
    }
};

// Group chapters by act
const groupedChapters = computed(() => {
    const groups: { act: Act | null; chapters: Chapter[] }[] = [];
    const chaptersWithAct = new Set<number>();

    if (props.acts.length > 0) {
        props.acts.forEach((act) => {
            const actChapters = props.chapters.filter((c) => c.act_id === act.id);
            if (actChapters.length > 0) {
                groups.push({ act, chapters: actChapters });
                actChapters.forEach((c) => chaptersWithAct.add(c.id));
            }
        });
    }

    const unassigned = props.chapters.filter((c) => !chaptersWithAct.has(c.id));
    if (unassigned.length > 0) {
        groups.push({ act: null, chapters: unassigned });
    }

    return groups;
});

const statusColors: Record<string, string> = {
    draft: 'bg-zinc-400',
    in_progress: 'bg-amber-400',
    completed: 'bg-green-400',
    needs_revision: 'bg-red-400',
};

const getStatusColor = (status: string) => statusColors[status] || 'bg-zinc-400';

const formatWordCount = (count: number) => {
    if (count >= 1000) {
        return `${(count / 1000).toFixed(1)}k`;
    }
    return count.toString();
};
</script>

<template>
    <div class="space-y-2">
        <!-- Loading State -->
        <div v-if="isSearching" class="py-12 text-center">
            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-violet-600 border-r-transparent" />
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Searching...</p>
        </div>

        <!-- Content -->
        <template v-else-if="chapters.length > 0">
            <div
                class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 bg-white dark:divide-zinc-700 dark:border-zinc-700 dark:bg-zinc-800"
            >
                <template v-for="group in groupedChapters" :key="group.act?.id ?? 'unassigned'">
                    <!-- Act Header (if exists) -->
                    <div v-if="group.act" class="bg-violet-50 dark:bg-violet-900/20">
                        <button
                            type="button"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-violet-100 dark:hover:bg-violet-900/30"
                            @click="toggleAct(group.act.id)"
                        >
                            <svg
                                :class="['h-4 w-4 text-violet-500 transition-transform', expandedActs.has(group.act.id) ? 'rotate-90' : '']"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="text-xs font-semibold uppercase tracking-wider text-violet-600 dark:text-violet-400">
                                Act {{ group.act.position + 1 }}:
                            </span>
                            <span class="flex-1 font-semibold text-violet-900 dark:text-violet-100">{{ group.act.title }}</span>
                        </button>
                    </div>

                    <!-- Chapters in this group -->
                    <template v-if="!group.act || expandedActs.has(group.act.id)">
                        <div v-for="chapter in group.chapters" :key="chapter.id">
                            <!-- Chapter Header -->
                            <button
                                type="button"
                                class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
                                :class="{ 'pl-8': group.act }"
                                @click="toggleChapter(chapter.id)"
                            >
                                <svg
                                    :class="['h-4 w-4 text-zinc-400 transition-transform', expandedChapters.has(chapter.id) ? 'rotate-90' : '']"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                <span class="flex-1 font-medium text-zinc-900 dark:text-white">{{ chapter.title }}</span>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ chapter.scenes.length }} {{ chapter.scenes.length === 1 ? 'scene' : 'scenes' }}
                                </span>
                                <span class="text-sm text-zinc-400 dark:text-zinc-500">{{ formatWordCount(chapter.word_count) }}</span>
                            </button>

                            <!-- Scenes in this chapter -->
                            <Transition
                                enter-active-class="transition-all duration-200 ease-out"
                                enter-from-class="opacity-0 max-h-0"
                                enter-to-class="opacity-100 max-h-[5000px]"
                                leave-active-class="transition-all duration-200 ease-in"
                                leave-from-class="opacity-100 max-h-[5000px]"
                                leave-to-class="opacity-0 max-h-0"
                            >
                                <div v-if="expandedChapters.has(chapter.id)" class="overflow-hidden">
                                    <div
                                        v-for="scene in chapter.scenes"
                                        :key="scene.id"
                                        class="cursor-pointer border-l-2 border-zinc-200 py-3 pl-8 pr-4 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:hover:bg-zinc-700/30"
                                        :class="{ 'ml-4': group.act }"
                                        @click="emit('sceneClick', scene)"
                                    >
                                        <div class="flex items-start gap-3">
                                            <!-- Status indicator -->
                                            <span :class="['mt-1.5 h-2 w-2 shrink-0 rounded-full', getStatusColor(scene.status)]" />

                                            <div class="min-w-0 flex-1">
                                                <!-- Scene title -->
                                                <div class="flex items-center gap-2">
                                                    <h4 class="font-medium text-zinc-900 dark:text-white">
                                                        {{ scene.title || 'Untitled Scene' }}
                                                    </h4>
                                                    <!-- POV badge -->
                                                    <span
                                                        v-if="scene.pov_character"
                                                        class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                                    >
                                                        POV: {{ scene.pov_character.name }}
                                                    </span>
                                                </div>

                                                <!-- Subtitle -->
                                                <p v-if="scene.subtitle" class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ scene.subtitle }}
                                                </p>

                                                <!-- Summary - the main focus of outline view -->
                                                <p v-if="scene.summary" class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                                                    {{ scene.summary }}
                                                </p>

                                                <!-- Labels -->
                                                <div v-if="scene.labels.length > 0" class="mt-2 flex flex-wrap gap-1">
                                                    <span
                                                        v-for="label in scene.labels"
                                                        :key="label.id"
                                                        :style="{ backgroundColor: label.color + '20', color: label.color }"
                                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                                    >
                                                        {{ label.name }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Word count -->
                                            <span class="shrink-0 text-sm text-zinc-400 dark:text-zinc-500">
                                                {{ formatWordCount(scene.word_count) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Empty scenes state -->
                                    <div v-if="chapter.scenes.length === 0" class="py-4 pl-12 text-sm text-zinc-500 dark:text-zinc-400">
                                        No scenes in this chapter
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </template>
                </template>
            </div>
        </template>

        <!-- Empty State -->
        <div v-else class="rounded-lg border-2 border-dashed border-zinc-200 py-12 text-center dark:border-zinc-700">
            <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">No outline yet</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ hasFilters ? 'Try adjusting your filters' : 'Create your first chapter and scene in the editor' }}
            </p>
            <Button :href="`/novels/${novelId}/workspace`" as="a" class="mt-4">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Start Writing
            </Button>
        </div>
    </div>
</template>
