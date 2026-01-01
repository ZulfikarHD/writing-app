<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Input from '@/components/ui/forms/Input.vue';
import Modal from '@/components/ui/layout/Modal.vue';
import Textarea from '@/components/ui/forms/Textarea.vue';
import axios from 'axios';
import { ref, watch, computed } from 'vue';

const props = defineProps<{
    show: boolean;
    novelId: number;
    selectedText?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'created', entry: { id: number; name: string; type: string }): void;
}>();

const loading = ref(false);
const error = ref<string | null>(null);

// Form state
const name = ref('');
const type = ref('character');
const description = ref('');

// Entry types
const types = [
    { value: 'character', label: 'Character', icon: '👤' },
    { value: 'location', label: 'Location', icon: '📍' },
    { value: 'item', label: 'Item', icon: '⚔️' },
    { value: 'lore', label: 'Lore', icon: '📜' },
    { value: 'organization', label: 'Organization', icon: '🏛️' },
    { value: 'subplot', label: 'Subplot', icon: '📖' },
];

// Auto-suggest type based on selected text heuristics
const suggestType = (text: string): string => {
    const lowerText = text.toLowerCase();

    // Location indicators
    if (/\b(city|town|village|castle|forest|mountain|river|kingdom|realm|house|building|street|room)\b/i.test(lowerText)) {
        return 'location';
    }

    // Organization indicators
    if (/\b(guild|order|council|company|army|group|clan|tribe|faction|alliance)\b/i.test(lowerText)) {
        return 'organization';
    }

    // Item indicators
    if (/\b(sword|ring|crown|book|scroll|amulet|staff|weapon|artifact)\b/i.test(lowerText)) {
        return 'item';
    }

    // Check if it looks like a proper noun (capitalized)
    if (/^[A-Z][a-z]/.test(text)) {
        return 'character';
    }

    return 'character';
};

// Watch for selected text changes and pre-fill
watch(
    () => props.selectedText,
    (newText) => {
        if (newText) {
            name.value = newText.trim();
            type.value = suggestType(newText);
        }
    },
    { immediate: true }
);

// Reset form when modal opens
watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            error.value = null;
            if (props.selectedText) {
                name.value = props.selectedText.trim();
                type.value = suggestType(props.selectedText);
            } else {
                name.value = '';
                type.value = 'character';
            }
            description.value = '';
        }
    }
);

const canSubmit = computed(() => name.value.trim().length > 0);

const handleSubmit = async () => {
    if (!canSubmit.value) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post(`/api/novels/${props.novelId}/codex/quick-create`, {
            name: name.value.trim(),
            type: type.value,
            description: description.value.trim() || null,
            add_alias: props.selectedText?.trim(),
        });

        emit('created', response.data.entry);
        emit('close');
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
        if (axiosError.response?.data?.errors) {
            const firstError = Object.values(axiosError.response.data.errors)[0];
            error.value = firstError?.[0] || 'Failed to create entry';
        } else {
            error.value = axiosError.response?.data?.message || 'Failed to create entry';
        }
    } finally {
        loading.value = false;
    }
};

const handleClose = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" title="Quick Create Codex Entry" max-width="md" @close="handleClose">
        <form class="space-y-4" @submit.prevent="handleSubmit">
            <!-- Error message -->
            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                {{ error }}
            </div>

            <!-- Selected text indicator -->
            <div v-if="selectedText" class="rounded-lg bg-violet-50 px-3 py-2 dark:bg-violet-900/20">
                <p class="text-xs font-medium text-violet-600 dark:text-violet-400">Creating entry from selected text:</p>
                <p class="mt-1 text-sm font-medium text-violet-900 dark:text-violet-100">"{{ selectedText }}"</p>
            </div>

            <!-- Name -->
            <Input
                v-model="name"
                label="Name"
                placeholder="Enter the entry name"
                required
                autofocus
            />

            <!-- Type -->
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</label>
                <div class="grid grid-cols-3 gap-2">
                    <button
                        v-for="t in types"
                        :key="t.value"
                        type="button"
                        :class="[
                            'flex items-center justify-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                            type === t.value
                                ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-800',
                        ]"
                        @click="type = t.value"
                    >
                        <span>{{ t.icon }}</span>
                        <span>{{ t.label }}</span>
                    </button>
                </div>
            </div>

            <!-- Description (optional) -->
            <Textarea
                v-model="description"
                label="Description (optional)"
                placeholder="Brief description for AI context..."
                rows="3"
            />

            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                The entry will be created with "Include when detected" AI context mode.
            </p>
        </form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button variant="ghost" @click="handleClose">Cancel</Button>
                <Button
                    :loading="loading"
                    :disabled="!canSubmit || loading"
                    @click="handleSubmit"
                >
                    Create Entry
                </Button>
            </div>
        </template>
    </Modal>
</template>
