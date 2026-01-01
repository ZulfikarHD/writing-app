<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Card from '@/components/ui/layout/Card.vue';
import Input from '@/components/ui/forms/Input.vue';
import Textarea from '@/components/ui/forms/Textarea.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps<{
    novel: { id: number; title: string };
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

const form = useForm({
    type: '',
    name: '',
    description: '',
    ai_context_mode: 'detected',
});

const isSubmitting = ref(false);
const error = ref<string | null>(null);

const selectType = (type: string) => {
    form.type = type;
};

const submit = async () => {
    if (!form.type || !form.name.trim()) {
        error.value = 'Please fill in all required fields';
        return;
    }

    isSubmitting.value = true;
    error.value = null;

    try {
        const response = await axios.post(`/api/novels/${props.novel.id}/codex`, {
            type: form.type,
            name: form.name.trim(),
            description: form.description.trim() || null,
            ai_context_mode: form.ai_context_mode,
        });

        if (response.data.redirect) {
            router.visit(response.data.redirect);
        } else {
            router.visit(`/codex/${response.data.entry.id}`);
        }
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to create entry';
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <Head :title="`Create Codex Entry - ${novel.title}`" />

        <!-- Header -->
        <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto max-w-3xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <Link
                        :href="`/novels/${novel.id}/codex`"
                        class="flex items-center gap-2 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Codex
                    </Link>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Create Codex Entry</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Add a new character, location, item, or other story element.</p>
            </div>

            <!-- Error Alert -->
            <div v-if="error" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                {{ error }}
            </div>

            <form @submit.prevent="submit">
                <!-- Step 1: Select Type -->
                <Card class="mb-6">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Entry Type</h2>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <button
                            v-for="type in types"
                            :key="type"
                            type="button"
                            class="flex flex-col items-center rounded-lg border p-4 text-center transition-all active:scale-[0.98]"
                            :class="
                                form.type === type
                                    ? 'border-violet-500 bg-violet-50 ring-2 ring-violet-500/20 dark:border-violet-500 dark:bg-violet-900/20'
                                    : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                            "
                            @click="selectType(type)"
                        >
                            <span class="text-2xl">{{ typeConfig[type]?.icon }}</span>
                            <span class="mt-2 font-medium text-zinc-900 dark:text-white">{{ typeConfig[type]?.label }}</span>
                            <span class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ typeConfig[type]?.description }}</span>
                        </button>
                    </div>
                </Card>

                <!-- Step 2: Basic Info -->
                <Card class="mb-6">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Basic Information</h2>
                    <div class="space-y-4">
                        <Input v-model="form.name" label="Name" placeholder="Enter entry name" required :error="form.errors.name" />

                        <Textarea
                            v-model="form.description"
                            label="Description"
                            placeholder="Describe this entry... (supports rich text)"
                            rows="5"
                            :error="form.errors.description"
                        />
                    </div>
                </Card>

                <!-- Step 3: AI Settings -->
                <Card class="mb-6">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">AI Context Settings</h2>
                    <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">Control how this entry is included when AI generates content.</p>
                    <div class="space-y-2">
                        <label
                            v-for="mode in contextModes"
                            :key="mode"
                            class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition-all"
                            :class="
                                form.ai_context_mode === mode
                                    ? 'border-violet-500 bg-violet-50 dark:border-violet-500 dark:bg-violet-900/20'
                                    : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                            "
                        >
                            <input
                                v-model="form.ai_context_mode"
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
                    <Button :href="`/novels/${novel.id}/codex`" as="a" variant="ghost"> Cancel </Button>
                    <Button type="submit" :loading="isSubmitting" :disabled="!form.type || !form.name.trim() || isSubmitting"> Create Entry </Button>
                </div>
            </form>
        </main>
    </div>
</template>
