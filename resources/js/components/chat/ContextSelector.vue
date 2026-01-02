<script setup lang="ts">
import { animate, stagger } from 'motion';
import { computed, nextTick, ref } from 'vue';
import type { ContextItem, ContextSources } from '@/composables/useChatContext';

interface Props {
    threadId: number | null;
    novelId: number;
    sources: ContextSources | null;
    contextItems: ContextItem[];
    isLoadingSources: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    addContext: [type: ContextItem['context_type'], referenceId?: number, customContent?: string];
    fetchSources: [];
    close: [];
}>();

const isOpen = ref(false);
const searchQuery = ref('');
const activeTab = ref<'scenes' | 'codex' | 'custom'>('scenes');
const customContent = ref('');

// Open dropdown and fetch sources
const openSelector = () => {
    isOpen.value = true;
    if (!props.sources) {
        emit('fetchSources');
    }
};

// Filter scenes based on search
const filteredScenes = computed(() => {
    if (!props.sources?.chapters) return [];

    const query = searchQuery.value.toLowerCase();
    if (!query) return props.sources.chapters;

    return props.sources.chapters
        .map((chapter) => ({
            ...chapter,
            scenes: chapter.scenes.filter(
                (scene) =>
                    scene.title?.toLowerCase().includes(query) || chapter.title.toLowerCase().includes(query)
            ),
        }))
        .filter((chapter) => chapter.scenes.length > 0);
});

// Filter codex entries based on search
const filteredCodex = computed(() => {
    if (!props.sources?.codex) return [];

    const query = searchQuery.value.toLowerCase();
    if (!query) return props.sources.codex;

    return props.sources.codex
        .map((group) => ({
            ...group,
            entries: group.entries.filter((entry) => entry.name?.toLowerCase().includes(query)),
        }))
        .filter((group) => group.entries.length > 0);
});

// Check if a scene is already added
const isSceneAdded = (sceneId: number): boolean => {
    return props.contextItems.some((item) => item.context_type === 'scene' && item.reference_id === sceneId);
};

// Check if a codex entry is already added
const isCodexAdded = (entryId: number): boolean => {
    return props.contextItems.some((item) => item.context_type === 'codex' && item.reference_id === entryId);
};

// Add scene context
const addScene = (sceneId: number) => {
    if (isSceneAdded(sceneId)) return;
    emit('addContext', 'scene', sceneId);
};

// Add codex context
const addCodex = (entryId: number) => {
    if (isCodexAdded(entryId)) return;
    emit('addContext', 'codex', entryId);
};

// Add custom context
const addCustom = () => {
    if (!customContent.value.trim()) return;
    emit('addContext', 'custom', undefined, customContent.value.trim());
    customContent.value = '';
};

// Format token count
const formatTokens = (tokens: number): string => {
    if (tokens >= 1000) {
        return `${(tokens / 1000).toFixed(1)}k`;
    }
    return tokens.toString();
};

// Get codex type icon
const getCodexTypeIcon = (type: string): string => {
    const icons: Record<string, string> = {
        character: '👤',
        location: '📍',
        item: '🎒',
        event: '📅',
        faction: '🏛️',
        creature: '🐉',
        concept: '💡',
        other: '📝',
    };
    return icons[type.toLowerCase()] || '📝';
};

// Dropdown animation
const onDropdownEnter = async (el: Element) => {
    await nextTick();

    animate(
        el,
        { opacity: [0, 1], transform: ['scale(0.95) translateY(-10px)', 'scale(1) translateY(0)'] },
        { duration: 0.2, easing: [0.16, 1, 0.3, 1] }
    );

    const items = el.querySelectorAll('.context-item');
    if (items.length > 0) {
        animate(
            items,
            { opacity: [0, 1], transform: ['translateY(10px)', 'translateY(0)'] },
            { duration: 0.25, delay: stagger(0.02), easing: [0.16, 1, 0.3, 1] }
        );
    }
};

const onDropdownLeave = (el: Element, done: () => void) => {
    animate(el, { opacity: [1, 0], transform: ['scale(1)', 'scale(0.95)'] }, { duration: 0.15 }).finished.then(done);
};

// Close when clicking outside
const closeSelector = () => {
    isOpen.value = false;
    searchQuery.value = '';
    emit('close');
};
</script>

<template>
    <div class="relative">
        <!-- Trigger Button -->
        <button
            type="button"
            class="flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-600 transition-all hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700 active:scale-95 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-violet-600 dark:hover:bg-violet-900/20 dark:hover:text-violet-300"
            @click="openSelector"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Context
        </button>

        <!-- Dropdown -->
        <Transition @enter="onDropdownEnter" @leave="onDropdownLeave" :css="false">
            <div
                v-if="isOpen"
                class="absolute bottom-full left-0 z-50 mb-2 w-80 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-800"
            >
                <!-- Header -->
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Add Context</h3>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Include story content in AI context</p>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-zinc-200 dark:border-zinc-700">
                    <button
                        type="button"
                        :class="[
                            'flex-1 px-4 py-2 text-sm font-medium transition-all',
                            activeTab === 'scenes'
                                ? 'border-b-2 border-violet-600 text-violet-600 dark:text-violet-400'
                                : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300',
                        ]"
                        @click="activeTab = 'scenes'"
                    >
                        Scenes
                    </button>
                    <button
                        type="button"
                        :class="[
                            'flex-1 px-4 py-2 text-sm font-medium transition-all',
                            activeTab === 'codex'
                                ? 'border-b-2 border-violet-600 text-violet-600 dark:text-violet-400'
                                : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300',
                        ]"
                        @click="activeTab = 'codex'"
                    >
                        Codex
                    </button>
                    <button
                        type="button"
                        :class="[
                            'flex-1 px-4 py-2 text-sm font-medium transition-all',
                            activeTab === 'custom'
                                ? 'border-b-2 border-violet-600 text-violet-600 dark:text-violet-400'
                                : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300',
                        ]"
                        @click="activeTab = 'custom'"
                    >
                        Custom
                    </button>
                </div>

                <!-- Search (for scenes/codex) -->
                <div v-if="activeTab !== 'custom'" class="border-b border-zinc-200 p-2 dark:border-zinc-700">
                    <input
                        v-model="searchQuery"
                        type="text"
                        :placeholder="activeTab === 'scenes' ? 'Search scenes...' : 'Search codex entries...'"
                        class="w-full rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm placeholder-zinc-400 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-700 dark:placeholder-zinc-500"
                    />
                </div>

                <!-- Content -->
                <div class="max-h-64 overflow-y-auto">
                    <!-- Loading State -->
                    <div v-if="isLoadingSources" class="flex items-center justify-center py-12">
                        <div class="h-6 w-6 animate-spin rounded-full border-2 border-violet-600 border-t-transparent"></div>
                    </div>

                    <!-- Scenes Tab -->
                    <div v-else-if="activeTab === 'scenes'">
                        <div v-if="filteredScenes.length === 0" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No scenes found
                        </div>
                        <div v-else>
                            <div v-for="chapter in filteredScenes" :key="chapter.id" class="border-b border-zinc-100 last:border-0 dark:border-zinc-700">
                                <div class="bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                                    {{ chapter.title }}
                                </div>
                                <button
                                    v-for="scene in chapter.scenes"
                                    :key="scene.id"
                                    type="button"
                                    :disabled="isSceneAdded(scene.id)"
                                    class="context-item flex w-full items-center justify-between px-3 py-2 text-left transition-all hover:bg-zinc-100 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-zinc-700"
                                    @click="addScene(scene.id)"
                                >
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-sm text-zinc-700 dark:text-zinc-200">{{ scene.title || 'Untitled' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-zinc-400">~{{ formatTokens(scene.tokens) }} tokens</span>
                                        <svg v-if="isSceneAdded(scene.id)" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Codex Tab -->
                    <div v-else-if="activeTab === 'codex'">
                        <div v-if="filteredCodex.length === 0" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No codex entries found
                        </div>
                        <div v-else>
                            <div v-for="group in filteredCodex" :key="group.type" class="border-b border-zinc-100 last:border-0 dark:border-zinc-700">
                                <div class="flex items-center gap-1.5 bg-zinc-50 px-3 py-1.5 text-xs font-medium capitalize text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                                    <span>{{ getCodexTypeIcon(group.type) }}</span>
                                    <span>{{ group.type }}s</span>
                                </div>
                                <button
                                    v-for="entry in group.entries"
                                    :key="entry.id"
                                    type="button"
                                    :disabled="isCodexAdded(entry.id)"
                                    class="context-item flex w-full items-center justify-between px-3 py-2 text-left transition-all hover:bg-zinc-100 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-zinc-700"
                                    @click="addCodex(entry.id)"
                                >
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">{{ getCodexTypeIcon(entry.type || group.type) }}</span>
                                        <span class="text-sm text-zinc-700 dark:text-zinc-200">{{ entry.name }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-zinc-400">~{{ formatTokens(entry.tokens) }} tokens</span>
                                        <svg v-if="isCodexAdded(entry.id)" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Tab -->
                    <div v-else-if="activeTab === 'custom'" class="p-3">
                        <textarea
                            v-model="customContent"
                            rows="4"
                            placeholder="Enter custom context text..."
                            class="w-full rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm placeholder-zinc-400 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-700 dark:placeholder-zinc-500"
                        ></textarea>
                        <button
                            type="button"
                            :disabled="!customContent.trim()"
                            class="mt-2 w-full rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-50 active:scale-95"
                            @click="addCustom"
                        >
                            Add Custom Context
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-zinc-200 px-4 py-2 dark:border-zinc-700">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ contextItems.length }} item{{ contextItems.length !== 1 ? 's' : '' }} added
                    </p>
                </div>
            </div>
        </Transition>

        <!-- Backdrop -->
        <div v-if="isOpen" class="fixed inset-0 z-40" @click="closeSelector"></div>
    </div>
</template>
