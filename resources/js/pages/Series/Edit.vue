<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import Toast from '@/components/ui/Toast.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

interface SeriesData {
    id: number;
    title: string;
    description: string | null;
    cover_path: string | null;
    genre: string | null;
}

const props = defineProps<{
    series: SeriesData;
}>();

const form = useForm({
    title: props.series.title,
    description: props.series.description || '',
    genre: props.series.genre || '',
});

const toast = ref({
    show: false,
    variant: 'success' as 'info' | 'success' | 'warning' | 'danger',
    title: '',
    message: '',
});

const saving = ref(false);

const showToast = (variant: 'info' | 'success' | 'warning' | 'danger', title: string, message: string) => {
    toast.value = { show: true, variant, title, message };
};

const submit = async () => {
    saving.value = true;
    try {
        await axios.patch(`/api/series/${props.series.id}`, {
            title: form.title,
            description: form.description || null,
            genre: form.genre || null,
        });
        showToast('success', 'Saved', 'Series updated successfully.');
        router.visit(`/series/${props.series.id}`);
    } catch {
        showToast('danger', 'Error', 'Failed to update series.');
    } finally {
        saving.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <Head :title="`Edit ${series.title}`" />

        <!-- Header -->
        <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto max-w-3xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <Link
                        :href="`/series/${series.id}`"
                        class="flex items-center gap-2 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Series
                    </Link>
                    <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-700" />
                    <h1 class="text-lg font-semibold text-zinc-900 dark:text-white">Edit Series</h1>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info -->
                <Card>
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Series Information</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title *</label>
                            <Input v-model="form.title" placeholder="Enter series title" required :error="form.errors.title" />
                            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
                            <Textarea v-model="form.description" placeholder="Describe your series..." rows="3" />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Genre</label>
                            <Input v-model="form.genre" placeholder="e.g., Fantasy, Sci-Fi, Romance" />
                        </div>
                    </div>
                </Card>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <Button :href="`/series/${series.id}`" as="a" variant="ghost">Cancel</Button>
                    <Button type="submit" :disabled="saving">
                        <svg v-if="saving" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ saving ? 'Saving...' : 'Save Changes' }}
                    </Button>
                </div>
            </form>
        </main>

        <!-- Toast -->
        <Toast v-if="toast.show" :variant="toast.variant" :title="toast.title" :duration="5000" position="top-right" @close="toast.show = false">
            {{ toast.message }}
        </Toast>
    </div>
</template>
