<script setup lang="ts">
import { ref, watch, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { Motion, AnimatePresence } from 'motion-v';

interface Label {
    id: number;
    name: string;
    color: string;
}

interface Scene {
    id: number;
    chapter_id: number;
    title: string | null;
    content: object | null;
    summary: string | null;
    status: string;
    word_count: number;
    subtitle: string | null;
    notes: string | null;
    pov_character_id: number | null;
    exclude_from_ai: boolean;
    labels?: Label[];
}

const props = defineProps<{
    open: boolean;
    scene: Scene | null;
    novelId: number;
    availableLabels?: Label[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'updated', scene: Partial<Scene>): void;
}>();

const form = useForm({
    title: '',
    subtitle: '',
    summary: '',
    notes: '',
    status: 'draft',
    pov_character_id: null as number | null,
    exclude_from_ai: false,
});

const selectedLabelIds = ref<number[]>([]);
const isSaving = ref(false);
const saveTimeout = ref<ReturnType<typeof setTimeout> | null>(null);

const statusOptions = [
    { value: 'draft', label: 'Draft', color: 'bg-zinc-400' },
    { value: 'in_progress', label: 'In Progress', color: 'bg-amber-400' },
    { value: 'completed', label: 'Completed', color: 'bg-green-400' },
    { value: 'needs_revision', label: 'Needs Revision', color: 'bg-red-400' },
];

const springConfig = {
    type: 'spring' as const,
    stiffness: 400,
    damping: 30,
};

// Sync form with scene data
watch(
    () => props.scene,
    (newScene) => {
        if (newScene) {
            form.title = newScene.title || '';
            form.subtitle = newScene.subtitle || '';
            form.summary = newScene.summary || '';
            form.notes = newScene.notes || '';
            form.status = newScene.status;
            form.pov_character_id = newScene.pov_character_id;
            form.exclude_from_ai = newScene.exclude_from_ai;
            selectedLabelIds.value = newScene.labels?.map((l) => l.id) || [];
        }
    },
    { immediate: true }
);

// Debounced auto-save
const autoSave = () => {
    if (!props.scene) return;

    if (saveTimeout.value) {
        clearTimeout(saveTimeout.value);
    }

    saveTimeout.value = setTimeout(async () => {
        isSaving.value = true;
        try {
            await axios.patch(`/api/scenes/${props.scene!.id}`, {
                title: form.title || null,
                subtitle: form.subtitle || null,
                summary: form.summary || null,
                notes: form.notes || null,
                status: form.status,
                pov_character_id: form.pov_character_id,
                exclude_from_ai: form.exclude_from_ai,
            });
            emit('updated', {
                title: form.title || null,
                status: form.status,
            });
        } catch (error) {
            console.error('Failed to save metadata:', error);
        } finally {
            isSaving.value = false;
        }
    }, 500);
};

// Watch form changes for auto-save
watch(
    () => [form.title, form.subtitle, form.summary, form.notes, form.status, form.pov_character_id, form.exclude_from_ai],
    autoSave,
    { deep: true }
);

// Save labels
const saveLabels = async () => {
    if (!props.scene) return;
    try {
        await axios.post(`/api/scenes/${props.scene.id}/labels`, {
            label_ids: selectedLabelIds.value,
        });
    } catch (error) {
        console.error('Failed to save labels:', error);
    }
};

const toggleLabel = (labelId: number) => {
    const index = selectedLabelIds.value.indexOf(labelId);
    if (index > -1) {
        selectedLabelIds.value.splice(index, 1);
    } else {
        selectedLabelIds.value.push(labelId);
    }
    saveLabels();
};

// Close on escape
let escapeHandler: ((e: KeyboardEvent) => void) | null = null;

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            escapeHandler = (e: KeyboardEvent) => {
                if (e.key === 'Escape') {
                    emit('close');
                }
            };
            document.addEventListener('keydown', escapeHandler);
        } else if (escapeHandler) {
            document.removeEventListener('keydown', escapeHandler);
            escapeHandler = null;
        }
    },
    { immediate: true }
);

onUnmounted(() => {
    if (escapeHandler) {
        document.removeEventListener('keydown', escapeHandler);
    }
    if (saveTimeout.value) {
        clearTimeout(saveTimeout.value);
    }
});
</script>

<template>
    <AnimatePresence>
        <!-- Backdrop -->
        <Motion
            v-if="open"
            :initial="{ opacity: 0 }"
            :animate="{ opacity: 1 }"
            :exit="{ opacity: 0 }"
            :transition="{ duration: 0.2 }"
            class="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm dark:bg-black/40"
            @click="emit('close')"
        />
    </AnimatePresence>

    <AnimatePresence>
        <!-- Panel -->
        <Motion
            v-if="open && scene"
            :initial="{ x: '100%' }"
            :animate="{ x: 0 }"
            :exit="{ x: '100%' }"
            :transition="springConfig"
            class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto border-l border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900 sm:max-w-md"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Scene Info</h2>
                    <Transition
                        enter-active-class="transition-opacity"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition-opacity"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <span v-if="isSaving" class="text-xs text-zinc-500">Saving...</span>
                    </Transition>
                </div>
                <button
                    type="button"
                    class="rounded-md p-2 text-zinc-500 transition-all hover:bg-zinc-100 active:scale-95 dark:text-zinc-400 dark:hover:bg-zinc-800"
                    @click="emit('close')"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="space-y-6 p-4">
                <!-- Word Count (Read-only) -->
                <div class="rounded-lg bg-violet-50 px-4 py-3 dark:bg-violet-900/20">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-violet-700 dark:text-violet-300">
                            {{ scene.word_count.toLocaleString() }}
                        </div>
                        <div class="text-xs text-violet-600 dark:text-violet-400">words</div>
                    </div>
                </div>

                <!-- Title -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</label>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="Scene title..."
                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    />
                </div>

                <!-- Subtitle -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Subtitle</label>
                    <input
                        v-model="form.subtitle"
                        type="text"
                        placeholder="Optional subtitle..."
                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    />
                </div>

                <!-- Status -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            v-for="option in statusOptions"
                            :key="option.value"
                            type="button"
                            :class="[
                                'flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition-all active:scale-95',
                                form.status === option.value
                                    ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
                            ]"
                            @click="form.status = option.value"
                        >
                            <span :class="['h-2 w-2 rounded-full', option.color]" />
                            {{ option.label }}
                        </button>
                    </div>
                </div>

                <!-- Summary -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Summary</label>
                    <textarea
                        v-model="form.summary"
                        rows="3"
                        placeholder="Brief scene summary..."
                        class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    />
                </div>

                <!-- Notes -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Notes
                        <span class="font-normal text-zinc-500">(private)</span>
                    </label>
                    <textarea
                        v-model="form.notes"
                        rows="4"
                        placeholder="Internal notes, reminders..."
                        class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    />
                </div>

                <!-- Labels -->
                <div v-if="availableLabels && availableLabels.length > 0">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Labels</label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="label in availableLabels"
                            :key="label.id"
                            type="button"
                            :class="[
                                'rounded-full px-3 py-1 text-xs font-medium transition-all active:scale-95',
                                selectedLabelIds.includes(label.id) ? 'ring-2 ring-offset-1' : 'opacity-60 hover:opacity-100',
                            ]"
                            :style="{
                                backgroundColor: label.color + '20',
                                color: label.color,
                                '--tw-ring-color': label.color,
                            }"
                            @click="toggleLabel(label.id)"
                        >
                            {{ label.name }}
                        </button>
                    </div>
                </div>

                <!-- AI Settings -->
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <label class="flex cursor-pointer items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">Exclude from AI</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Hide this scene from AI context</div>
                        </div>
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="form.exclude_from_ai"
                            :class="[
                                'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2',
                                form.exclude_from_ai ? 'bg-violet-600' : 'bg-zinc-200 dark:bg-zinc-700',
                            ]"
                            @click="form.exclude_from_ai = !form.exclude_from_ai"
                        >
                            <span
                                :class="[
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition',
                                    form.exclude_from_ai ? 'translate-x-5' : 'translate-x-0',
                                ]"
                            />
                        </button>
                    </label>
                </div>
            </div>
        </Motion>
    </AnimatePresence>
</template>
