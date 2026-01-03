<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import Modal from '@/components/ui/layout/Modal.vue';
import Button from '@/components/ui/buttons/Button.vue';

interface Message {
    id: number;
    thread_id: number;
    role: 'user' | 'assistant' | 'system';
    content: string;
    model_used: string | null;
    tokens_input: number | null;
    tokens_output: number | null;
    created_at: string;
}

interface Scene {
    id: number;
    title: string;
    chapter: {
        id: number;
        title: string;
    };
}

interface Chapter {
    id: number;
    title: string;
    scenes: Scene[];
}

const props = defineProps<{
    show: boolean;
    message: Message | null;
    novelId: number;
    currentSceneId?: number | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'transferred', data: { sceneId: number; sceneName: string }): void;
}>();

// State
const loading = ref(false);
const loadingChapters = ref(false);
const error = ref<string | null>(null);
const success = ref(false);
const chapters = ref<Chapter[]>([]);

// Form state
const transferMode = ref<'existing' | 'new'>('existing');
const selectedSceneId = ref<number | null>(null);
const selectedChapterId = ref<number | null>(null);
const insertPosition = ref<'end' | 'cursor'>('end');
const newSceneTitle = ref('');

// Computed
const canSubmit = computed(() => {
    if (transferMode.value === 'existing') {
        return selectedSceneId.value !== null;
    } else {
        return selectedChapterId.value !== null && newSceneTitle.value.trim().length > 0;
    }
});

// Fetch chapters and scenes
const fetchChapters = async () => {
    loadingChapters.value = true;
    try {
        const response = await axios.get(`/api/novels/${props.novelId}/chapters`);
        chapters.value = response.data.chapters || [];

        // Pre-select current scene if available
        if (props.currentSceneId) {
            selectedSceneId.value = props.currentSceneId;
        }
    } catch (e) {
        console.error('Failed to fetch chapters:', e);
        error.value = 'Failed to load chapters';
    } finally {
        loadingChapters.value = false;
    }
};

// Handle transfer
const handleTransfer = async () => {
    if (!props.message || !canSubmit.value) return;

    loading.value = true;
    error.value = null;

    try {
        const payload: Record<string, unknown> = {
            content: props.message.content,
            position: insertPosition.value,
        };

        if (transferMode.value === 'existing') {
            payload.target_type = 'scene';
            payload.scene_id = selectedSceneId.value;
        } else {
            payload.target_type = 'new_scene';
            payload.chapter_id = selectedChapterId.value;
            payload.title = newSceneTitle.value.trim();
        }

        const response = await axios.post(
            `/api/chat/messages/${props.message.id}/transfer`,
            payload
        );

        success.value = true;

        setTimeout(() => {
            emit('transferred', {
                sceneId: response.data.scene_id,
                sceneName: response.data.scene_title,
            });
            emit('close');
        }, 1000);
    } catch (e: unknown) {
        const axiosError = e as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to transfer content';
    } finally {
        loading.value = false;
    }
};

// Reset form when modal opens
watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            error.value = null;
            success.value = false;
            transferMode.value = 'existing';
            newSceneTitle.value = '';
            fetchChapters();
        }
    }
);

// Auto-select current scene
watch(
    () => props.currentSceneId,
    (sceneId) => {
        if (sceneId && props.show) {
            selectedSceneId.value = sceneId;
        }
    }
);
</script>

<template>
    <Modal :show="show" title="Insert to Scene" size="md" @close="emit('close')">
        <!-- Success State -->
        <div v-if="success" class="flex flex-col items-center py-8">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">Content transferred!</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">The content has been added to your scene.</p>
        </div>

        <!-- Form -->
        <div v-else class="space-y-4">
            <!-- Error message -->
            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                {{ error }}
            </div>

            <!-- Content Preview -->
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                <p class="mb-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">Content to insert:</p>
                <p class="line-clamp-3 text-sm text-zinc-700 dark:text-zinc-300">
                    {{ message?.content }}
                </p>
            </div>

            <!-- Transfer Mode -->
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Insert into</label>
                <div class="flex gap-2">
                    <button
                        type="button"
                        :class="[
                            'flex-1 rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                            transferMode === 'existing'
                                ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-800',
                        ]"
                        @click="transferMode = 'existing'"
                    >
                        Existing Scene
                    </button>
                    <button
                        type="button"
                        :class="[
                            'flex-1 rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                            transferMode === 'new'
                                ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-800',
                        ]"
                        @click="transferMode = 'new'"
                    >
                        New Scene
                    </button>
                </div>
            </div>

            <!-- Existing Scene Selection -->
            <div v-if="transferMode === 'existing'">
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Select Scene</label>
                <div v-if="loadingChapters" class="flex items-center justify-center py-4">
                    <div class="h-5 w-5 animate-spin rounded-full border-2 border-violet-600 border-t-transparent"></div>
                </div>
                <div v-else-if="chapters.length === 0" class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-400">
                    No chapters found. Create a chapter first.
                </div>
                <div v-else class="max-h-48 space-y-2 overflow-y-auto rounded-lg border border-zinc-200 p-2 dark:border-zinc-700">
                    <div v-for="chapter in chapters" :key="chapter.id" class="space-y-1">
                        <p class="px-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ chapter.title }}</p>
                        <button
                            v-for="scene in chapter.scenes"
                            :key="scene.id"
                            type="button"
                            :class="[
                                'w-full rounded-md px-3 py-2 text-left text-sm transition-colors',
                                selectedSceneId === scene.id
                                    ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'
                                    : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800',
                            ]"
                            @click="selectedSceneId = scene.id"
                        >
                            {{ scene.title }}
                        </button>
                    </div>
                </div>

                <!-- Insert Position -->
                <div v-if="selectedSceneId" class="mt-3">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Insert Position</label>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            :class="[
                                'flex-1 rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                                insertPosition === 'end'
                                    ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                    : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-800',
                            ]"
                            @click="insertPosition = 'end'"
                        >
                            At End
                        </button>
                        <button
                            type="button"
                            :class="[
                                'flex-1 rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                                insertPosition === 'cursor'
                                    ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                    : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-800',
                            ]"
                            @click="insertPosition = 'cursor'"
                            :disabled="currentSceneId !== selectedSceneId"
                            :title="currentSceneId !== selectedSceneId ? 'Only available for currently open scene' : ''"
                        >
                            At Cursor
                        </button>
                    </div>
                </div>
            </div>

            <!-- New Scene Creation -->
            <div v-else>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Select Chapter</label>
                <div v-if="loadingChapters" class="flex items-center justify-center py-4">
                    <div class="h-5 w-5 animate-spin rounded-full border-2 border-violet-600 border-t-transparent"></div>
                </div>
                <div v-else-if="chapters.length === 0" class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-400">
                    No chapters found. Create a chapter first.
                </div>
                <select
                    v-else
                    v-model="selectedChapterId"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                >
                    <option :value="null" disabled>Select a chapter...</option>
                    <option v-for="chapter in chapters" :key="chapter.id" :value="chapter.id">
                        {{ chapter.title }}
                    </option>
                </select>

                <div class="mt-3">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Scene Title</label>
                    <input
                        v-model="newSceneTitle"
                        type="text"
                        placeholder="Enter scene title..."
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                    />
                </div>
            </div>
        </div>

        <template v-if="!success" #footer>
            <div class="flex justify-end gap-2">
                <Button variant="ghost" @click="emit('close')">Cancel</Button>
                <Button
                    :loading="loading"
                    :disabled="!canSubmit || loading"
                    @click="handleTransfer"
                >
                    {{ transferMode === 'existing' ? 'Insert Content' : 'Create Scene' }}
                </Button>
            </div>
        </template>
    </Modal>
</template>
