<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import ActGroup from './ActGroup.vue';
import ChapterGroup from './ChapterGroup.vue';

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

interface CardSettings {
    size: 'compact' | 'normal' | 'large';
    showSummary: boolean;
    showLabels: boolean;
    showWordCount: boolean;
    showPov: boolean;
}

const props = defineProps<{
    novelId: number;
    chapters: Chapter[];
    acts: Act[];
    isSearching: boolean;
    hasFilters: boolean;
    cardSettings?: CardSettings;
}>();

const emit = defineEmits<{
    (e: 'sceneClick', scene: Scene): void;
    (e: 'sceneContextMenu', event: MouseEvent, scene: Scene): void;
    (e: 'scenesReorder', chapterId: number, scenes: { id: number; position: number }[]): void;
    (e: 'chapterContextMenu', event: MouseEvent, chapter: Chapter): void;
    (e: 'sceneMoveToChapter', sceneId: number, targetChapterId: number, position: number): void;
}>();

// Group chapters by act
const getChaptersGroupedByAct = () => {
    const grouped: { act: Act | null; chapters: Chapter[] }[] = [];
    const chaptersWithAct = new Set<number>();

    // First, group chapters with acts
    if (props.acts.length > 0) {
        props.acts.forEach((act) => {
            const actChapters = props.chapters.filter((c) => c.act_id === act.id);
            if (actChapters.length > 0) {
                grouped.push({ act, chapters: actChapters });
                actChapters.forEach((c) => chaptersWithAct.add(c.id));
            }
        });
    }

    // Then add chapters without acts
    const unassigned = props.chapters.filter((c) => !chaptersWithAct.has(c.id));
    if (unassigned.length > 0) {
        grouped.push({ act: null, chapters: unassigned });
    }

    return grouped;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Loading State -->
        <div v-if="isSearching" class="py-12 text-center">
            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-violet-600 border-r-transparent" />
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Searching...</p>
        </div>

        <!-- Content -->
        <template v-else>
            <!-- Group by Acts if available -->
            <template v-if="acts.length > 0">
                <template v-for="group in getChaptersGroupedByAct()" :key="group.act?.id ?? 'unassigned'">
                    <ActGroup v-if="group.act" :act="group.act" :chapter-count="group.chapters.length">
                        <ChapterGroup
                            v-for="chapter in group.chapters"
                            :key="chapter.id"
                            :chapter="chapter"
                            :card-settings="cardSettings"
                            @scene-click="emit('sceneClick', $event)"
                            @scene-context-menu="(e, s) => emit('sceneContextMenu', e, s)"
                            @scenes-reorder="(cId, scenes) => emit('scenesReorder', cId, scenes)"
                            @chapter-context-menu="(e, c) => emit('chapterContextMenu', e, c)"
                            @scene-move-to-chapter="(sId, cId, pos) => emit('sceneMoveToChapter', sId, cId, pos)"
                        />
                    </ActGroup>

                    <template v-else>
                        <ChapterGroup
                            v-for="chapter in group.chapters"
                            :key="chapter.id"
                            :chapter="chapter"
                            :card-settings="cardSettings"
                            @scene-click="emit('sceneClick', $event)"
                            @scene-context-menu="(e, s) => emit('sceneContextMenu', e, s)"
                            @scenes-reorder="(cId, scenes) => emit('scenesReorder', cId, scenes)"
                            @chapter-context-menu="(e, c) => emit('chapterContextMenu', e, c)"
                            @scene-move-to-chapter="(sId, cId, pos) => emit('sceneMoveToChapter', sId, cId, pos)"
                        />
                    </template>
                </template>
            </template>

            <!-- No Acts: Direct chapter list -->
            <template v-else>
                <ChapterGroup
                    v-for="chapter in chapters"
                    :key="chapter.id"
                    :chapter="chapter"
                    :card-settings="cardSettings"
                    @scene-click="emit('sceneClick', $event)"
                    @scene-context-menu="(e, s) => emit('sceneContextMenu', e, s)"
                    @scenes-reorder="(cId, scenes) => emit('scenesReorder', cId, scenes)"
                    @chapter-context-menu="(e, c) => emit('chapterContextMenu', e, c)"
                    @scene-move-to-chapter="(sId, cId, pos) => emit('sceneMoveToChapter', sId, cId, pos)"
                />
            </template>

            <!-- Empty State -->
            <div v-if="chapters.length === 0" class="rounded-lg border-2 border-dashed border-zinc-200 py-12 text-center dark:border-zinc-700">
                <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">No scenes found</h3>
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
        </template>
    </div>
</template>
