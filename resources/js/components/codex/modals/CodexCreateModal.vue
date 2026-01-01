<script setup lang="ts">
import Modal from '@/components/ui/Modal.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import Toast from '@/components/ui/Toast.vue';
import axios from 'axios';
import { ref, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    novelId: number;
    prefilledName?: string;
    prefilledType?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'created', entry: { id: number; name: string; type: string }): void;
}>();

const types = ['character', 'location', 'item', 'lore', 'organization', 'subplot'];

const typeConfig: Record<string, { label: string; icon: string; description: string }> = {
    character: { label: 'Character', icon: '👤', description: 'People, creatures, or beings' },
    location: { label: 'Location', icon: '📍', description: 'Places and environments' },
    item: { label: 'Item', icon: '⚔️', description: 'Objects and artifacts' },
    lore: { label: 'Lore', icon: '📜', description: 'History and world-building' },
    organization: { label: 'Organization', icon: '🏛️', description: 'Groups and factions' },
    subplot: { label: 'Subplot', icon: '📖', description: 'Narrative threads' },
};

const contextModes = [
    { value: 'always', label: 'Always', description: 'Always include in AI context' },
    { value: 'detected', label: 'Detected', description: 'Include when mentioned' },
    { value: 'manual', label: 'Manual', description: 'Only when manually selected' },
    { value: 'never', label: 'Never', description: 'Never include' },
];

// Form state
const selectedType = ref('');
const name = ref('');
const description = ref('');
const aiContextMode = ref('detected');
const isSubmitting = ref(false);

const toast = ref({
    show: false,
    variant: 'danger' as 'info' | 'success' | 'warning' | 'danger',
    title: '',
    message: '',
});

const showToast = (variant: 'info' | 'success' | 'warning' | 'danger', title: string, message: string) => {
    toast.value = { show: true, variant, title, message };
};

// Reset form when modal opens/closes
watch(
    () => props.show,
    (show) => {
        if (show) {
            selectedType.value = props.prefilledType || '';
            name.value = props.prefilledName || '';
            description.value = '';
            aiContextMode.value = 'detected';
        }
    }
);

const selectType = (type: string) => {
    selectedType.value = type;
};

const submit = async () => {
    if (!selectedType.value || !name.value.trim()) {
        showToast('danger', 'Error', 'Please select a type and enter a name');
        return;
    }

    isSubmitting.value = true;

    try {
        const response = await axios.post(`/api/novels/${props.novelId}/codex`, {
            type: selectedType.value,
            name: name.value.trim(),
            description: description.value.trim() || null,
            ai_context_mode: aiContextMode.value,
        });

        emit('created', response.data.entry);
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        showToast('danger', 'Error', axiosError.response?.data?.message || 'Failed to create entry');
    } finally {
        isSubmitting.value = false;
    }
};

const handleClose = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" size="lg" title="Create Codex Entry" :closable="true" @close="handleClose">
        <form @submit.prevent="submit">
            <!-- Step 1: Select Type -->
            <div class="mb-6">
                <h3 class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Entry Type</h3>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    <button
                        v-for="type in types"
                        :key="type"
                        type="button"
                        class="flex flex-col items-center rounded-lg border p-3 text-center transition-all active:scale-[0.98]"
                        :class="
                            selectedType === type
                                ? 'border-violet-500 bg-violet-50 ring-2 ring-violet-500/20 dark:border-violet-500 dark:bg-violet-900/20'
                                : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                        "
                        @click="selectType(type)"
                    >
                        <span class="text-xl">{{ typeConfig[type]?.icon }}</span>
                        <span class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">{{ typeConfig[type]?.label }}</span>
                        <span class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ typeConfig[type]?.description }}</span>
                    </button>
                </div>
            </div>

            <!-- Step 2: Basic Info -->
            <div class="mb-6 space-y-4">
                <Input v-model="name" label="Name" placeholder="Enter entry name" required />

                <Textarea
                    v-model="description"
                    label="Description (optional)"
                    placeholder="Describe this entry..."
                    rows="3"
                />
            </div>

            <!-- Step 3: AI Settings -->
            <div class="mb-6">
                <h3 class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">AI Context Mode</h3>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        v-for="mode in contextModes"
                        :key="mode.value"
                        type="button"
                        class="rounded-lg border p-2 text-left transition-all active:scale-[0.98]"
                        :class="
                            aiContextMode === mode.value
                                ? 'border-violet-500 bg-violet-50 ring-2 ring-violet-500/20 dark:border-violet-500 dark:bg-violet-900/20'
                                : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                        "
                        @click="aiContextMode = mode.value"
                    >
                        <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ mode.label }}</span>
                        <span class="mt-0.5 block text-xs text-zinc-500 dark:text-zinc-400">{{ mode.description }}</span>
                    </button>
                </div>
            </div>
        </form>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button variant="ghost" @click="handleClose">Cancel</Button>
                <Button :loading="isSubmitting" :disabled="!selectedType || !name.trim()" @click="submit">
                    Create Entry
                </Button>
            </div>
        </template>

        <!-- Toast -->
        <Toast
            v-if="toast.show"
            :variant="toast.variant"
            :title="toast.title"
            :duration="3000"
            position="top-right"
            @close="toast.show = false"
        >
            {{ toast.message }}
        </Toast>
    </Modal>
</template>
