<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import Toast from '@/components/ui/Toast.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

interface CodexEntry {
    id: number;
    type: string;
    name: string;
    description: string | null;
    thumbnail_path: string | null;
    ai_context_mode: string;
}

const props = defineProps<{
    novel: { id: number; title: string };
    entry: CodexEntry;
    types: string[];
    contextModes: string[];
}>();

const typeConfig: Record<string, { label: string; icon: string; description: string }> = {
    character: { label: 'Character', icon: '👤', description: 'People, creatures, or beings in your story' },
    location: { label: 'Location', icon: '📍', description: 'Places, settings, and environments' },
    item: { label: 'Item', icon: '⚔️', description: 'Objects, artifacts, and possessions' },
    lore: { label: 'Lore', icon: '📜', description: 'History, legends, and world-building details' },
    organization: { label: 'Organization', icon: '🏛️', description: 'Groups, factions, and institutions' },
    subplot: { label: 'Subplot', icon: '📖', description: 'Secondary storylines and narrative threads' },
};

const contextModeLabels: Record<string, { label: string; description: string }> = {
    always: { label: 'Always', description: 'Always include in AI context' },
    detected: { label: 'Detected', description: 'Include when mentioned in text' },
    manual: { label: 'Manual', description: 'Only when manually selected' },
    never: { label: 'Never', description: 'Never include in AI context' },
};

// Form state
const formData = ref({
    type: props.entry.type,
    name: props.entry.name,
    description: props.entry.description || '',
    ai_context_mode: props.entry.ai_context_mode,
});

const isSubmitting = ref(false);
const errors = ref<Record<string, string>>({});

const toast = ref({
    show: false,
    variant: 'success' as 'info' | 'success' | 'warning' | 'danger',
    title: '',
    message: '',
});

const showToast = (variant: 'info' | 'success' | 'warning' | 'danger', title: string, message: string) => {
    toast.value = { show: true, variant, title, message };
};

const selectType = (type: string) => {
    formData.value.type = type;
};

const submit = async () => {
    if (!formData.value.name.trim()) {
        errors.value.name = 'Name is required';
        return;
    }

    isSubmitting.value = true;
    errors.value = {};

    try {
        await axios.patch(`/api/codex/${props.entry.id}`, {
            type: formData.value.type,
            name: formData.value.name.trim(),
            description: formData.value.description.trim() || null,
            ai_context_mode: formData.value.ai_context_mode,
        });

        showToast('success', 'Entry Updated', 'Your changes have been saved');
        setTimeout(() => {
            router.visit(`/codex/${props.entry.id}`);
        }, 500);
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
        if (axiosError.response?.data?.errors) {
            const fieldErrors = axiosError.response.data.errors;
            for (const [key, messages] of Object.entries(fieldErrors)) {
                errors.value[key] = messages[0];
            }
        } else {
            showToast('danger', 'Error', axiosError.response?.data?.message || 'Failed to update entry');
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <Head :title="`Edit ${entry.name} - Codex`" />

        <!-- Header -->
        <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto max-w-3xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <Link
                        :href="`/codex/${entry.id}`"
                        class="flex items-center gap-2 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Entry
                    </Link>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Edit Entry</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Update {{ entry.name }}'s information.</p>
            </div>

            <form @submit.prevent="submit">
                <!-- Entry Type -->
                <Card class="mb-6">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Entry Type</h2>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <button
                            v-for="type in types"
                            :key="type"
                            type="button"
                            class="flex flex-col items-center rounded-lg border p-4 text-center transition-all active:scale-[0.98]"
                            :class="
                                formData.type === type
                                    ? 'border-violet-500 bg-violet-50 ring-2 ring-violet-500/20 dark:border-violet-500 dark:bg-violet-900/20'
                                    : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                            "
                            @click="selectType(type)"
                        >
                            <span class="text-2xl">{{ typeConfig[type]?.icon }}</span>
                            <span class="mt-2 font-medium text-zinc-900 dark:text-white">{{ typeConfig[type]?.label }}</span>
                        </button>
                    </div>
                </Card>

                <!-- Basic Info -->
                <Card class="mb-6">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Basic Information</h2>
                    <div class="space-y-4">
                        <Input v-model="formData.name" label="Name" placeholder="Enter entry name" required :error="errors.name" />

                        <Textarea
                            v-model="formData.description"
                            label="Description"
                            placeholder="Describe this entry..."
                            rows="5"
                            :error="errors.description"
                        />
                    </div>
                </Card>

                <!-- AI Settings -->
                <Card class="mb-6">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">AI Context Settings</h2>
                    <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">Control how this entry is included when AI generates content.</p>
                    <div class="space-y-2">
                        <label
                            v-for="mode in contextModes"
                            :key="mode"
                            class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition-all"
                            :class="
                                formData.ai_context_mode === mode
                                    ? 'border-violet-500 bg-violet-50 dark:border-violet-500 dark:bg-violet-900/20'
                                    : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                            "
                        >
                            <input
                                v-model="formData.ai_context_mode"
                                type="radio"
                                :value="mode"
                                class="mt-1 h-4 w-4 border-zinc-300 text-violet-600 focus:ring-violet-500"
                            />
                            <div>
                                <span class="font-medium text-zinc-900 dark:text-white">{{ contextModeLabels[mode]?.label }}</span>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ contextModeLabels[mode]?.description }}</p>
                            </div>
                        </label>
                    </div>
                </Card>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <Button :href="`/codex/${entry.id}`" as="a" variant="ghost"> Cancel </Button>
                    <Button type="submit" :loading="isSubmitting" :disabled="!formData.name.trim() || isSubmitting"> Save Changes </Button>
                </div>
            </form>
        </main>

        <!-- Toast -->
        <Toast v-if="toast.show" :variant="toast.variant" :title="toast.title" :duration="5000" position="top-right" @close="toast.show = false">
            {{ toast.message }}
        </Toast>
    </div>
</template>
