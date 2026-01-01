<script setup lang="ts">
import { ref, computed } from 'vue';
import draggable from 'vuedraggable';
import SceneCard from './SceneCard.vue';

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

interface Chapter {
    id: number;
    title: string;
    position: number;
    act_id: number | null;
    word_count: number;
    scenes: Scene[];
}

const props = defineProps<{
    chapter: Chapter;
}>();

const emit = defineEmits<{
    (e: 'sceneClick', scene: Scene): void;
    (e: 'sceneContextMenu', event: MouseEvent, scene: Scene): void;
    (e: 'scenesReorder', chapterId: number, scenes: { id: number; position: number }[]): void;
    (e: 'chapterContextMenu', event: MouseEvent, chapter: Chapter): void;
}>();

const isExpanded = ref(true);
const localScenes = ref<Scene[]>([...props.chapter.scenes]);
const isDragging = ref(false);

const formattedWordCount = computed(() => {
    const total = localScenes.value.reduce((sum, s) => sum + s.word_count, 0);
    if (total >= 1000) {
        return `${(total / 1000).toFixed(1)}k`;
    }
    return total.toString();
});

const sceneDragOptions = computed(() => ({
    animation: 200,
    group: 'scenes',
    ghostClass: 'opacity-50',
    chosenClass: 'ring-2 ring-violet-500',
}));

const onSceneDragEnd = () => {
    isDragging.value = false;
    const scenes = localScenes.value.map((s, index) => ({
        id: s.id,
        position: index,
    }));
    emit('scenesReorder', props.chapter.id, scenes);
};

const handleChapterContextMenu = (e: MouseEvent) => {
    e.preventDefault();
    emit('chapterContextMenu', e, props.chapter);
};
</script>

<template>
    <div class="rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
        <!-- Chapter Header -->
        <div
            class="flex cursor-pointer items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-700/50"
            @click="isExpanded = !isExpanded"
            @contextmenu="handleChapterContextMenu"
        >
            <div class="flex items-center gap-2">
                <svg
                    :class="['h-4 w-4 text-zinc-400 transition-transform', isExpanded ? 'rotate-90' : '']"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                <h3 class="font-medium text-zinc-900 dark:text-white">{{ chapter.title }}</h3>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">({{ localScenes.length }} scenes)</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ formattedWordCount }} words</span>
            </div>
        </div>

        <!-- Scenes Grid -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-[2000px]"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 max-h-[2000px]"
            leave-to-class="opacity-0 max-h-0"
        >
            <div v-if="isExpanded" class="overflow-hidden border-t border-zinc-200 p-4 dark:border-zinc-700">
                <draggable
                    v-model="localScenes"
                    item-key="id"
                    v-bind="sceneDragOptions"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                    @start="isDragging = true"
                    @end="onSceneDragEnd"
                >
                    <template #item="{ element: scene }">
                        <SceneCard
                            :scene="scene"
                            :draggable="true"
                            @click="emit('sceneClick', scene)"
                            @contextmenu="(e, s) => emit('sceneContextMenu', e, s)"
                        />
                    </template>
                </draggable>

                <!-- Empty state -->
                <div v-if="localScenes.length === 0" class="py-8 text-center">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">No scenes in this chapter</p>
                </div>
            </div>
        </Transition>
    </div>
</template>
