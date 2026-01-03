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

interface ParsedItem {
    id: string;
    title: string;
    content: string;
    selected: boolean;
}

interface Chapter {
    id: number;
    title: string;
}

const props = defineProps<{
    show: boolean;
    message: Message | null;
    novelId: number;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'extracted', data: { type: string; count: number }): void;
}>();

// State
const loading = ref(false);
const loadingChapters = ref(false);
const error = ref<string | null>(null);
const success = ref(false);

// Extract type selection
const extractType = ref<'scenes' | 'codex'>('scenes');
const parsedItems = ref<ParsedItem[]>([]);
const chapters = ref<Chapter[]>([]);
const selectedChapterId = ref<number | null>(null);
const codexType = ref('lore');

// Codex types
const codexTypes = [
    { value: 'character', label: 'Characters', icon: '👤' },
    { value: 'location', label: 'Locations', icon: '📍' },
    { value: 'item', label: 'Items', icon: '⚔️' },
    { value: 'lore', label: 'Lore', icon: '📜' },
];

// Parse content into items (scene beats or codex entries)
const parseContent = (content: string): ParsedItem[] => {
    const items: ParsedItem[] = [];
    
    // Try to parse numbered list (1. Item, 2. Item, etc.)
    const numberedPattern = /^\d+\.\s*(.+?)(?=\n\d+\.|$)/gms;
    let match;
    let index = 0;
    
    while ((match = numberedPattern.exec(content)) !== null) {
        const itemContent = match[1].trim();
        if (itemContent) {
            // Extract title from first line or sentence
            const lines = itemContent.split('\n');
            const firstLine = lines[0].trim();
            const title = firstLine.length > 60 
                ? firstLine.substring(0, 57) + '...' 
                : firstLine;
            
            items.push({
                id: `item-${index++}`,
                title: title.replace(/^\*\*(.+?)\*\*/, '$1').replace(/^#+\s*/, ''),
                content: itemContent,
                selected: true,
            });
        }
    }

    // If no numbered items found, try bullet points
    if (items.length === 0) {
        const bulletPattern = /^[\-\*]\s*(.+?)(?=\n[\-\*]|$)/gms;
        while ((match = bulletPattern.exec(content)) !== null) {
            const itemContent = match[1].trim();
            if (itemContent) {
                const title = itemContent.length > 60 
                    ? itemContent.substring(0, 57) + '...' 
                    : itemContent;
                    
                items.push({
                    id: `item-${index++}`,
                    title: title.replace(/^\*\*(.+?)\*\*/, '$1'),
                    content: itemContent,
                    selected: true,
                });
            }
        }
    }

    // If still no items, split by double newlines (paragraphs)
    if (items.length === 0) {
        const paragraphs = content.split(/\n{2,}/).filter(p => p.trim());
        paragraphs.forEach((para, i) => {
            const firstLine = para.split('\n')[0].trim();
            const title = firstLine.length > 60 
                ? firstLine.substring(0, 57) + '...' 
                : firstLine;
                
            items.push({
                id: `item-${i}`,
                title: title.replace(/^#+\s*/, ''),
                content: para.trim(),
                selected: true,
            });
        });
    }

    return items;
};

// Fetch chapters
const fetchChapters = async () => {
    loadingChapters.value = true;
    try {
        const response = await axios.get(`/api/novels/${props.novelId}/chapters`);
        chapters.value = response.data.chapters || [];
        if (chapters.value.length > 0) {
            selectedChapterId.value = chapters.value[0].id;
        }
    } catch (e) {
        console.error('Failed to fetch chapters:', e);
    } finally {
        loadingChapters.value = false;
    }
};

// Toggle item selection
const toggleItem = (itemId: string) => {
    const item = parsedItems.value.find(i => i.id === itemId);
    if (item) {
        item.selected = !item.selected;
    }
};

// Select/deselect all
const toggleAll = (selected: boolean) => {
    parsedItems.value.forEach(item => {
        item.selected = selected;
    });
};

// Computed
const selectedItems = computed(() => parsedItems.value.filter(i => i.selected));
const canSubmit = computed(() => {
    if (selectedItems.value.length === 0) return false;
    if (extractType.value === 'scenes' && !selectedChapterId.value) return false;
    return true;
});

// Handle extraction
const handleExtract = async () => {
    if (!props.message || !canSubmit.value) return;

    loading.value = true;
    error.value = null;

    try {
        if (extractType.value === 'scenes') {
            // Create scenes from selected items
            for (const item of selectedItems.value) {
                await axios.post(`/api/chapters/${selectedChapterId.value}/scenes`, {
                    title: item.title,
                    content: item.content,
                });
            }
        } else {
            // Create codex entries
            await axios.post(`/api/chat/messages/${props.message.id}/extract`, {
                entries: selectedItems.value.map(item => ({
                    type: codexType.value,
                    name: item.title,
                    description: item.content,
                })),
            });
        }

        success.value = true;

        setTimeout(() => {
            emit('extracted', {
                type: extractType.value,
                count: selectedItems.value.length,
            });
            emit('close');
        }, 1500);
    } catch (e: unknown) {
        const axiosError = e as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to create items';
    } finally {
        loading.value = false;
    }
};

// Initialize when modal opens
watch(
    () => props.show,
    (isOpen) => {
        if (isOpen && props.message) {
            error.value = null;
            success.value = false;
            parsedItems.value = parseContent(props.message.content);
            fetchChapters();
        }
    }
);
</script>

<template>
    <Modal :show="show" title="Extract Structured Data" size="2xl" @close="emit('close')">
        <!-- Success State -->
        <div v-if="success" class="flex flex-col items-center py-8">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">
                {{ selectedItems.length }} {{ extractType === 'scenes' ? 'scenes' : 'entries' }} created!
            </p>
        </div>

        <!-- Form -->
        <div v-else class="space-y-4">
            <!-- Error message -->
            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                {{ error }}
            </div>

            <!-- Extract Type Selection -->
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Extract as</label>
                <div class="flex gap-2">
                    <button
                        type="button"
                        :class="[
                            'flex flex-1 items-center justify-center gap-2 rounded-lg border px-4 py-3 text-sm font-medium transition-colors',
                            extractType === 'scenes'
                                ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-800',
                        ]"
                        @click="extractType = 'scenes'"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Scene Beats
                    </button>
                    <button
                        type="button"
                        :class="[
                            'flex flex-1 items-center justify-center gap-2 rounded-lg border px-4 py-3 text-sm font-medium transition-colors',
                            extractType === 'codex'
                                ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-800',
                        ]"
                        @click="extractType = 'codex'"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Codex Entries
                    </button>
                </div>
            </div>

            <!-- Target Selection -->
            <div v-if="extractType === 'scenes'">
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Add to Chapter</label>
                <div v-if="loadingChapters" class="flex items-center justify-center py-4">
                    <div class="h-5 w-5 animate-spin rounded-full border-2 border-violet-600 border-t-transparent"></div>
                </div>
                <select
                    v-else
                    v-model="selectedChapterId"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                >
                    <option v-for="chapter in chapters" :key="chapter.id" :value="chapter.id">
                        {{ chapter.title }}
                    </option>
                </select>
            </div>

            <div v-else>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Entry Type</label>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="t in codexTypes"
                        :key="t.value"
                        type="button"
                        :class="[
                            'flex items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                            codexType === t.value
                                ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-800',
                        ]"
                        @click="codexType = t.value"
                    >
                        <span>{{ t.icon }}</span>
                        <span>{{ t.label }}</span>
                    </button>
                </div>
            </div>

            <!-- Parsed Items -->
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Detected Items ({{ parsedItems.length }})
                    </label>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="text-xs text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300"
                            @click="toggleAll(true)"
                        >
                            Select All
                        </button>
                        <span class="text-zinc-300 dark:text-zinc-600">|</span>
                        <button
                            type="button"
                            class="text-xs text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300"
                            @click="toggleAll(false)"
                        >
                            Deselect All
                        </button>
                    </div>
                </div>

                <div v-if="parsedItems.length === 0" class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-400">
                    No structured items detected. Try content with numbered lists or bullet points.
                </div>

                <div v-else class="max-h-64 space-y-2 overflow-y-auto rounded-lg border border-zinc-200 p-2 dark:border-zinc-700">
                    <div
                        v-for="item in parsedItems"
                        :key="item.id"
                        :class="[
                            'flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition-colors',
                            item.selected
                                ? 'border-violet-300 bg-violet-50 dark:border-violet-600 dark:bg-violet-900/20'
                                : 'border-zinc-200 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50',
                        ]"
                        @click="toggleItem(item.id)"
                    >
                        <div class="pt-0.5">
                            <div
                                :class="[
                                    'flex h-5 w-5 items-center justify-center rounded border-2 transition-colors',
                                    item.selected
                                        ? 'border-violet-500 bg-violet-500 text-white'
                                        : 'border-zinc-300 dark:border-zinc-600',
                                ]"
                            >
                                <svg v-if="item.selected" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-zinc-900 dark:text-white">{{ item.title }}</p>
                            <p class="mt-0.5 line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ item.content }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template v-if="!success" #footer>
            <div class="flex items-center justify-between">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ selectedItems.length }} of {{ parsedItems.length }} selected
                </p>
                <div class="flex gap-2">
                    <Button variant="ghost" @click="emit('close')">Cancel</Button>
                    <Button
                        :loading="loading"
                        :disabled="!canSubmit || loading"
                        @click="handleExtract"
                    >
                        Create {{ selectedItems.length }} {{ extractType === 'scenes' ? 'Scenes' : 'Entries' }}
                    </Button>
                </div>
            </div>
        </template>
    </Modal>
</template>
