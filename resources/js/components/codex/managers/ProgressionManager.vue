<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Modal from '@/components/ui/Modal.vue';
import Textarea from '@/components/ui/Textarea.vue';
import ProgressionTimeline from '../shared/ProgressionTimeline.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

interface Scene {
    id: number;
    title: string | null;
    chapter: { id: number; title: string } | null;
}

interface Detail {
    id: number;
    key_name: string;
}

interface Progression {
    id: number;
    story_timestamp: string | null;
    note: string;
    new_value: string | null;
    mode: 'addition' | 'replace';
    scene: Scene | null;
    detail: Detail | null;
}

interface SceneOption {
    id: number;
    title: string | null;
    chapter: { id: number; title: string } | null;
}

const props = defineProps<{
    entryId: number;
    novelId: number;
    progressions: Progression[];
    details?: Detail[];
    scenes?: SceneOption[];
}>();

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

const localProgressions = ref<Progression[]>([...props.progressions]);
const showAddModal = ref(false);
const error = ref<string | null>(null);
const loading = ref(false);
const viewMode = ref<'list' | 'timeline'>('list');

// Form state
const formNote = ref('');
const formTimestamp = ref('');
const formNewValue = ref('');
const formMode = ref<'addition' | 'replace'>('addition');
const formDetailId = ref<number | null>(null);
const formSceneId = ref<number | null>(null);

const openAddModal = () => {
    showAddModal.value = true;
    formNote.value = '';
    formTimestamp.value = '';
    formNewValue.value = '';
    formMode.value = 'addition';
    formDetailId.value = null;
    formSceneId.value = null;
};

const closeModal = () => {
    showAddModal.value = false;
    error.value = null;
};

const addProgression = async () => {
    if (!formNote.value.trim()) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post(`/api/codex/${props.entryId}/progressions`, {
            note: formNote.value.trim(),
            story_timestamp: formTimestamp.value.trim() || null,
            new_value: formNewValue.value.trim() || null,
            mode: formMode.value,
            codex_detail_id: formDetailId.value,
            scene_id: formSceneId.value,
        });

        localProgressions.value.push(response.data.progression);
        closeModal();
        emit('updated');
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to add progression';
    } finally {
        loading.value = false;
    }
};

const deleteProgression = async (id: number) => {
    loading.value = true;
    error.value = null;

    try {
        await axios.delete(`/api/codex/progressions/${id}`);
        localProgressions.value = localProgressions.value.filter((p) => p.id !== id);
        emit('updated');
    } catch {
        error.value = 'Failed to delete progression';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div>
        <!-- Error Message -->
        <div v-if="error" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
            {{ error }}
            <button type="button" class="ml-2 text-red-500 hover:text-red-700" @click="error = null">×</button>
        </div>

        <!-- View Toggle (only show if there are progressions) -->
        <div v-if="localProgressions.length > 0" class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-1 rounded-lg bg-zinc-100 p-0.5 dark:bg-zinc-800">
                <button
                    type="button"
                    :class="[
                        'flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-colors',
                        viewMode === 'list'
                            ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white'
                            : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white',
                    ]"
                    @click="viewMode = 'list'"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    List
                </button>
                <button
                    type="button"
                    :class="[
                        'flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-colors',
                        viewMode === 'timeline'
                            ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white'
                            : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white',
                    ]"
                    @click="viewMode = 'timeline'"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Timeline
                </button>
            </div>
            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                {{ localProgressions.length }} {{ localProgressions.length === 1 ? 'progression' : 'progressions' }}
            </span>
        </div>

        <!-- Timeline View -->
        <ProgressionTimeline
            v-if="viewMode === 'timeline' && localProgressions.length > 0"
            :progressions="localProgressions"
            :novel-id="novelId"
        />

        <!-- List View (Original) -->
        <div v-if="viewMode === 'list' && localProgressions.length > 0" class="space-y-4">
            <div
                v-for="prog in localProgressions"
                :key="prog.id"
                class="relative border-l-2 border-violet-300 pl-4 dark:border-violet-700"
            >
                <div class="absolute -left-1.5 top-1 h-3 w-3 rounded-full bg-violet-500" />
                <div class="group">
                    <!-- Header -->
                    <div class="flex items-start justify-between">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            <span v-if="prog.story_timestamp" class="font-medium">{{ prog.story_timestamp }}</span>
                            <span v-if="prog.story_timestamp && prog.scene"> • </span>
                            <Link
                                v-if="prog.scene"
                                :href="`/novels/${novelId}/write/${prog.scene.id}`"
                                class="hover:text-violet-600 dark:hover:text-violet-400"
                            >
                                {{ prog.scene.chapter?.title || '' }} {{ prog.scene.chapter ? '-' : '' }} {{ prog.scene.title || 'Untitled' }}
                            </Link>
                        </div>
                        <button
                            type="button"
                            class="rounded p-1 text-zinc-400 opacity-0 transition-all hover:bg-red-100 hover:text-red-600 group-hover:opacity-100 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                            :disabled="loading"
                            @click="deleteProgression(prog.id)"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Note -->
                    <p class="mt-1 text-sm text-zinc-900 dark:text-white">{{ prog.note }}</p>

                    <!-- Mode Badge -->
                    <span
                        v-if="prog.mode === 'replace'"
                        class="mt-1 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
                    >
                        Replaces
                    </span>

                    <!-- Detail Change -->
                    <p v-if="prog.new_value && prog.detail" class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        <span class="font-medium">{{ prog.detail.key_name }}:</span> {{ prog.new_value }}
                    </p>
                </div>
            </div>
        </div>

        <Button size="sm" variant="ghost" class="mt-3" @click="openAddModal">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Progression
        </Button>

        <!-- Empty State -->
        <p v-if="localProgressions.length === 0" class="mt-2 text-sm text-zinc-400 italic dark:text-zinc-500">
            No progressions tracked yet. Track how this entry changes over the course of your story.
        </p>

        <!-- Add Progression Modal -->
        <Modal v-model="showAddModal" title="Add Progression" @close="closeModal">
            <div class="space-y-4">
                <Textarea
                    v-model="formNote"
                    label="What changed?"
                    placeholder="Describe the progression or change..."
                    rows="3"
                    required
                />

                <Input
                    v-model="formTimestamp"
                    label="Story Timestamp (optional)"
                    placeholder="e.g., Chapter 5, Year 3, Day 45"
                />

                <!-- Scene Selection -->
                <div v-if="scenes && scenes.length > 0">
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Link to Scene (optional)
                    </label>
                    <select
                        v-model="formSceneId"
                        class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >
                        <option :value="null">No scene link</option>
                        <option v-for="scene in scenes" :key="scene.id" :value="scene.id">
                            {{ scene.chapter?.title ? `${scene.chapter.title} - ` : '' }}{{ scene.title || 'Untitled Scene' }}
                        </option>
                    </select>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Link this progression to where it happens in your story
                    </p>
                </div>

                <!-- Detail Selection -->
                <div v-if="details && details.length > 0">
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Related Detail (optional)
                    </label>
                    <select
                        v-model="formDetailId"
                        class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >
                        <option :value="null">None</option>
                        <option v-for="detail in details" :key="detail.id" :value="detail.id">
                            {{ detail.key_name }}
                        </option>
                    </select>
                </div>

                <Input
                    v-if="formDetailId"
                    v-model="formNewValue"
                    label="New Value"
                    placeholder="New value for the selected detail"
                />

                <!-- Mode Toggle -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Progression Mode
                    </label>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            :class="[
                                'flex-1 rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                                formMode === 'addition'
                                    ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                    : 'border-zinc-300 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-400 dark:hover:bg-zinc-800',
                            ]"
                            @click="formMode = 'addition'"
                        >
                            <span class="block font-medium">Addition</span>
                            <span class="block text-xs opacity-75">Appends to description</span>
                        </button>
                        <button
                            type="button"
                            :class="[
                                'flex-1 rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                                formMode === 'replace'
                                    ? 'border-amber-500 bg-amber-50 text-amber-700 dark:border-amber-400 dark:bg-amber-900/30 dark:text-amber-300'
                                    : 'border-zinc-300 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-400 dark:hover:bg-zinc-800',
                            ]"
                            @click="formMode = 'replace'"
                        >
                            <span class="block font-medium">Replace</span>
                            <span class="block text-xs opacity-75">Overwrites detail value</span>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Addition mode appends info to the AI context. Replace mode overwrites the linked detail's value.
                    </p>
                </div>

                <!-- Error in modal -->
                <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                    {{ error }}
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button variant="ghost" @click="closeModal">Cancel</Button>
                    <Button :loading="loading" :disabled="!formNote.trim() || loading" @click="addProgression">
                        Add Progression
                    </Button>
                </div>
            </template>
        </Modal>
    </div>
</template>
