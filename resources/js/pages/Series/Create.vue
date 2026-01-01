<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import Toast from '@/components/ui/Toast.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Novel {
    id: number;
    title: string;
    cover_path: string | null;
}

defineProps<{
    availableNovels: Novel[];
}>();

const form = useForm({
    title: '',
    description: '',
    genre: '',
    novel_ids: [] as number[],
});

const toast = ref({
    show: false,
    variant: 'danger' as 'info' | 'success' | 'warning' | 'danger',
    title: '',
    message: '',
});

const showToast = (variant: 'info' | 'success' | 'warning' | 'danger', title: string, message: string) => {
    toast.value = { show: true, variant, title, message };
};

const toggleNovel = (novelId: number) => {
    const idx = form.novel_ids.indexOf(novelId);
    if (idx === -1) {
        form.novel_ids.push(novelId);
    } else {
        form.novel_ids.splice(idx, 1);
    }
};

const submit = () => {
    form.post('/api/series', {
        onSuccess: (response) => {
            const redirect = (response as { props?: { redirect?: string } }).props?.redirect;
            if (redirect) {
                router.visit(redirect);
            } else {
                router.visit('/series');
            }
        },
        onError: () => {
            showToast('danger', 'Error', 'Failed to create series. Please check the form.');
        },
    });
};
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <Head title="Create Series" />

        <!-- Header -->
        <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto max-w-3xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <Link
                        href="/series"
                        class="flex items-center gap-2 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Series
                    </Link>
                    <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-700" />
                    <h1 class="text-lg font-semibold text-zinc-900 dark:text-white">Create New Series</h1>
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

                <!-- Add Novels -->
                <Card v-if="availableNovels.length > 0">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Add Novels to Series</h2>
                    <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                        Select novels to include in this series. They will share the series Codex.
                    </p>

                    <div class="space-y-2">
                        <label
                            v-for="novel in availableNovels"
                            :key="novel.id"
                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 p-3 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800"
                            :class="{ 'border-violet-500 bg-violet-50 dark:border-violet-500 dark:bg-violet-900/20': form.novel_ids.includes(novel.id) }"
                        >
                            <input
                                type="checkbox"
                                :checked="form.novel_ids.includes(novel.id)"
                                class="h-4 w-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500"
                                @change="toggleNovel(novel.id)"
                            />
                            <div class="flex-1">
                                <p class="font-medium text-zinc-900 dark:text-white">{{ novel.title }}</p>
                            </div>
                        </label>
                    </div>
                </Card>

                <!-- No Available Novels -->
                <Card v-else>
                    <div class="py-6 text-center">
                        <svg class="mx-auto h-10 w-10 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                            />
                        </svg>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                            All your novels are already in a series, or you haven't created any novels yet.
                        </p>
                    </div>
                </Card>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <Button href="/series" as="a" variant="ghost">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ form.processing ? 'Creating...' : 'Create Series' }}
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
