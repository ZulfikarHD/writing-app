<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import Modal from '@/components/ui/layout/Modal.vue';
import Button from '@/components/ui/buttons/Button.vue';
import Input from '@/components/ui/forms/Input.vue';
import Textarea from '@/components/ui/forms/Textarea.vue';

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

interface CodexEntry {
    type: string;
    name: string;
    description: string;
}

const props = defineProps<{
    show: boolean;
    message: Message | null;
    novelId: number;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'extracted', data: { entries: Array<{ id: number; name: string; type: string }> }): void;
}>();

// Entry types
const types = [
    { value: 'character', label: 'Character', icon: '👤' },
    { value: 'location', label: 'Location', icon: '📍' },
    { value: 'item', label: 'Item', icon: '⚔️' },
    { value: 'lore', label: 'Lore', icon: '📜' },
    { value: 'organization', label: 'Organization', icon: '🏛️' },
    { value: 'subplot', label: 'Subplot', icon: '📖' },
];

// State
const loading = ref(false);
const error = ref<string | null>(null);
const success = ref(false);
const entries = ref<CodexEntry[]>([]);
const createdEntries = ref<Array<{ id: number; name: string; type: string }>>([]);

// Suggest type based on content heuristics
const suggestType = (text: string): string => {
    const lowerText = text.toLowerCase();

    // Check for character indicators
    if (/\b(he|she|they|character|protagonist|antagonist|person|man|woman|boy|girl)\b/.test(lowerText)) {
        return 'character';
    }

    // Location indicators
    if (/\b(city|town|village|castle|forest|mountain|river|kingdom|realm|place|location|land|country)\b/.test(lowerText)) {
        return 'location';
    }

    // Organization indicators
    if (/\b(guild|order|council|company|army|group|clan|tribe|faction|alliance|organization)\b/.test(lowerText)) {
        return 'organization';
    }

    // Item indicators
    if (/\b(sword|ring|crown|book|scroll|amulet|staff|weapon|artifact|item|object|tool)\b/.test(lowerText)) {
        return 'item';
    }

    return 'lore';
};

// Extract potential name from content
const extractName = (text: string): string => {
    // Try to find a title-like pattern (capitalized words at start)
    const lines = text.split('\n');
    const firstLine = lines[0]?.trim() || '';

    // If first line looks like a title (short, possibly with # prefix)
    const titleMatch = firstLine.match(/^#*\s*(.+)$/);
    if (titleMatch && titleMatch[1].length < 50) {
        return titleMatch[1].trim();
    }

    // Look for name patterns in content
    const namePatterns = [
        /(?:named?|called?|known as)\s+["']?([A-Z][a-zA-Z\s]+)["']?/i,
        /^([A-Z][a-zA-Z]+(?:\s+[A-Z][a-zA-Z]+)?)\s+(?:is|was|the)/m,
    ];

    for (const pattern of namePatterns) {
        const match = text.match(pattern);
        if (match && match[1]) {
            return match[1].trim();
        }
    }

    return '';
};

// Initialize entries when modal opens
const initializeEntries = () => {
    if (!props.message) return;

    const content = props.message.content;
    const suggestedName = extractName(content);
    const suggestedType = suggestType(content);

    // Start with one entry
    entries.value = [{
        type: suggestedType,
        name: suggestedName,
        description: content.substring(0, 2000), // Limit description length
    }];
};

// Add new entry
const addEntry = () => {
    entries.value.push({
        type: 'character',
        name: '',
        description: '',
    });
};

// Remove entry
const removeEntry = (index: number) => {
    if (entries.value.length > 1) {
        entries.value.splice(index, 1);
    }
};

// Computed
const canSubmit = computed(() => {
    return entries.value.every(entry => entry.name.trim().length > 0);
});

// Handle extract
const handleExtract = async () => {
    if (!props.message || !canSubmit.value) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post(
            `/api/chat/messages/${props.message.id}/extract`,
            {
                entries: entries.value.map(entry => ({
                    type: entry.type,
                    name: entry.name.trim(),
                    description: entry.description.trim() || null,
                })),
            }
        );

        createdEntries.value = response.data.entries;
        success.value = true;

        setTimeout(() => {
            emit('extracted', { entries: response.data.entries });
            emit('close');
        }, 1500);
    } catch (e: unknown) {
        const axiosError = e as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to create Codex entries';
    } finally {
        loading.value = false;
    }
};

// Reset when modal opens/closes
watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            error.value = null;
            success.value = false;
            createdEntries.value = [];
            initializeEntries();
        }
    }
);
</script>

<template>
    <Modal :show="show" title="Extract to Codex" size="lg" @close="emit('close')">
        <!-- Success State -->
        <div v-if="success" class="flex flex-col items-center py-8">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">
                {{ createdEntries.length }} {{ createdEntries.length === 1 ? 'entry' : 'entries' }} created!
            </p>
            <div class="mt-2 flex flex-wrap justify-center gap-2">
                <span
                    v-for="entry in createdEntries"
                    :key="entry.id"
                    class="rounded-full bg-violet-100 px-3 py-1 text-sm font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300"
                >
                    {{ entry.name }}
                </span>
            </div>
        </div>

        <!-- Form -->
        <div v-else class="space-y-4">
            <!-- Error message -->
            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                {{ error }}
            </div>

            <!-- Source Content Preview -->
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                <p class="mb-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">Extracting from:</p>
                <p class="line-clamp-2 text-sm text-zinc-700 dark:text-zinc-300">
                    {{ message?.content }}
                </p>
            </div>

            <!-- Entries -->
            <div class="space-y-4">
                <div v-for="(entry, index) in entries" :key="index" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Entry {{ index + 1 }}
                        </span>
                        <button
                            v-if="entries.length > 1"
                            type="button"
                            class="text-zinc-400 hover:text-red-500 dark:hover:text-red-400"
                            @click="removeEntry(index)"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Type Selection -->
                    <div class="mb-3">
                        <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Type</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="t in types"
                                :key="t.value"
                                type="button"
                                :class="[
                                    'flex items-center gap-1 rounded-md border px-2 py-1 text-xs font-medium transition-colors',
                                    entry.type === t.value
                                        ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                        : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-800',
                                ]"
                                @click="entry.type = t.value"
                            >
                                <span>{{ t.icon }}</span>
                                <span>{{ t.label }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="mb-3">
                        <Input
                            v-model="entry.name"
                            label="Name"
                            placeholder="Enter the entry name"
                            required
                        />
                    </div>

                    <!-- Description -->
                    <div>
                        <Textarea
                            v-model="entry.description"
                            label="Description"
                            placeholder="Brief description for AI context..."
                            rows="3"
                        />
                    </div>
                </div>
            </div>

            <!-- Add Entry Button -->
            <button
                type="button"
                class="flex w-full items-center justify-center gap-1.5 rounded-lg border border-dashed border-zinc-300 py-2 text-sm font-medium text-zinc-500 transition-colors hover:border-violet-400 hover:bg-violet-50 hover:text-violet-600 dark:border-zinc-700 dark:hover:border-violet-500 dark:hover:bg-violet-900/20 dark:hover:text-violet-400"
                @click="addEntry"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add Another Entry
            </button>
        </div>

        <template v-if="!success" #footer>
            <div class="flex justify-end gap-2">
                <Button variant="ghost" @click="emit('close')">Cancel</Button>
                <Button
                    :loading="loading"
                    :disabled="!canSubmit || loading"
                    @click="handleExtract"
                >
                    Create {{ entries.length }} {{ entries.length === 1 ? 'Entry' : 'Entries' }}
                </Button>
            </div>
        </template>
    </Modal>
</template>
