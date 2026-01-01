<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Modal from '@/components/ui/layout/Modal.vue';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

interface ChapterPreview {
    title: string;
    summary: string | null;
}

interface ActPreview {
    title: string;
    chapters: ChapterPreview[];
}

interface ParsedStructure {
    acts: ActPreview[];
}

interface Template {
    id: number;
    name: string;
    description: string;
    structure: ActPreview[];
    is_system: boolean;
}

const props = defineProps<{
    modelValue: boolean;
    novelId: number;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
    (e: 'created'): void;
}>();

const activeTab = ref<'write' | 'preview'>('write');
const outlineText = ref('');
const parsedStructure = ref<ParsedStructure | null>(null);
const templates = ref<Template[]>([]);
const isLoading = ref(false);
const isParsing = ref(false);
const isCreating = ref(false);
const error = ref<string | null>(null);

// Fetch templates on mount
const fetchTemplates = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/templates');
        templates.value = response.data.templates;
    } catch (err) {
        console.error('Failed to fetch templates:', err);
    } finally {
        isLoading.value = false;
    }
};

// Parse outline text
const parseOutline = async () => {
    if (!outlineText.value.trim()) {
        error.value = 'Please enter an outline';
        return;
    }

    isParsing.value = true;
    error.value = null;

    try {
        const response = await axios.post('/api/plan/parse-outline', {
            outline: outlineText.value,
        });
        parsedStructure.value = response.data.preview;
        activeTab.value = 'preview';
    } catch (err: unknown) {
        if (axios.isAxiosError(err) && err.response?.data?.error) {
            error.value = err.response.data.error;
        } else {
            error.value = 'Failed to parse outline. Please check your formatting.';
        }
    } finally {
        isParsing.value = false;
    }
};

// Apply template
const applyTemplate = (template: Template) => {
    parsedStructure.value = { acts: template.structure };
    outlineText.value = generateOutlineText(template.structure);
    activeTab.value = 'preview';
};

// Generate outline text from structure
const generateOutlineText = (acts: ActPreview[]): string => {
    let text = '';
    acts.forEach((act) => {
        text += `${act.title}\n`;
        act.chapters.forEach((chapter) => {
            text += `  ${chapter.title}\n`;
            if (chapter.summary) {
                text += `    ${chapter.summary}\n`;
            }
        });
        text += '\n';
    });
    return text.trim();
};

// Create structure
const createStructure = async () => {
    if (!parsedStructure.value) return;

    isCreating.value = true;
    error.value = null;

    try {
        await axios.post(`/api/novels/${props.novelId}/plan/from-outline`, {
            structure: parsedStructure.value,
        });
        emit('created');
        emit('update:modelValue', false);
        resetState();
    } catch (err: unknown) {
        if (axios.isAxiosError(err) && err.response?.data?.error) {
            error.value = err.response.data.error;
        } else {
            error.value = 'Failed to create structure. Please try again.';
        }
    } finally {
        isCreating.value = false;
    }
};

// Reset state
const resetState = () => {
    outlineText.value = '';
    parsedStructure.value = null;
    activeTab.value = 'write';
    error.value = null;
};

// Total chapters count
const totalChapters = computed(() => {
    if (!parsedStructure.value) return 0;
    return parsedStructure.value.acts.reduce((sum, act) => sum + act.chapters.length, 0);
});

// Watch for modal open
watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            fetchTemplates();
        } else {
            resetState();
        }
    }
);
</script>

<template>
    <Modal :model-value="modelValue" title="Create from Outline" size="xl" @update:model-value="emit('update:modelValue', $event)">
        <div class="space-y-4">
            <!-- Instructions -->
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Paste in your outline in the textbox below to create a new storyboard. In order to properly parse the outline, take the following structure
                into account when writing your outline:
            </p>

            <!-- Tabs -->
            <div class="flex border-b border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    :class="[
                        'border-b-2 px-4 py-2 text-sm font-medium transition-colors',
                        activeTab === 'write'
                            ? 'border-violet-500 text-violet-600 dark:text-violet-400'
                            : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300',
                    ]"
                    @click="activeTab = 'write'"
                >
                    Write
                </button>
                <button
                    type="button"
                    :class="[
                        'border-b-2 px-4 py-2 text-sm font-medium transition-colors',
                        activeTab === 'preview'
                            ? 'border-violet-500 text-violet-600 dark:text-violet-400'
                            : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300',
                    ]"
                    :disabled="!parsedStructure"
                    @click="activeTab = 'preview'"
                >
                    Preview
                </button>
            </div>

            <!-- Write Tab -->
            <div v-if="activeTab === 'write'" class="space-y-4">
                <textarea
                    v-model="outlineText"
                    class="min-h-[300px] w-full rounded-lg border border-zinc-200 bg-white p-4 font-mono text-sm text-zinc-900 placeholder-zinc-400 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                    placeholder="Act 1 - Introduction:
Chapter 1:
  Chapter prologue: A 6-year old Christine is being driven by her mother to...

Act 1 - Ordinary World:
Chapter 1 - A curious letter:
  Christine has a rough day at work, her coworkers ..."
                />

                <!-- Error Message -->
                <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                    {{ error }}
                </div>

                <!-- Parse Button -->
                <div class="flex justify-end">
                    <Button :loading="isParsing" :disabled="!outlineText.trim()" @click="parseOutline">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        Parse & Preview
                    </Button>
                </div>
            </div>

            <!-- Preview Tab -->
            <div v-else-if="activeTab === 'preview' && parsedStructure" class="space-y-4">
                <div class="max-h-[400px] overflow-y-auto rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div v-for="(act, actIndex) in parsedStructure.acts" :key="actIndex" class="mb-4 last:mb-0">
                        <h3 class="mb-2 text-lg font-semibold text-zinc-900 dark:text-white">{{ act.title }}</h3>
                        <div v-for="(chapter, chapterIndex) in act.chapters" :key="chapterIndex" class="mb-2 ml-4">
                            <h4 class="font-medium text-zinc-800 dark:text-zinc-200">{{ chapter.title }}</h4>
                            <p v-if="chapter.summary" class="ml-4 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ chapter.summary }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="flex items-center justify-between text-sm text-zinc-500 dark:text-zinc-400">
                    <span>{{ parsedStructure.acts.length }} acts, {{ totalChapters }} chapters</span>
                    <button type="button" class="text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300" @click="activeTab = 'write'">
                        Edit Outline
                    </button>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                    {{ error }}
                </div>
            </div>

            <!-- Templates Section -->
            <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <p class="mb-3 text-sm text-zinc-600 dark:text-zinc-400">You can also choose from few common templates instead:</p>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="template in templates"
                        :key="template.id"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-700 transition-colors hover:border-violet-300 hover:bg-violet-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-violet-600 dark:hover:bg-violet-900/20"
                        :title="template.description"
                        @click="applyTemplate(template)"
                    >
                        <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ template.name }}
                    </button>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button variant="secondary" @click="emit('update:modelValue', false)">Cancel</Button>
                <Button :loading="isCreating" :disabled="!parsedStructure || isCreating" @click="createStructure">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Create
                </Button>
            </div>
        </template>
    </Modal>
</template>
