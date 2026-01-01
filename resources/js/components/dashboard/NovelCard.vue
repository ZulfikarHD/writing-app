<script setup lang="ts">
import Badge from '@/components/ui/Badge.vue';
import Button from '@/components/ui/buttons/Button.vue';
import ConfirmDialog from '@/components/ui/overlays/ConfirmDialog.vue';
import { router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { computed, ref } from 'vue';

interface Novel {
    id: number;
    title: string;
    description: string | null;
    genre: string | null;
    word_count: number;
    chapter_count: number;
    status: string;
    last_edited_at: string | null;
    pen_name: { id: number; name: string } | null;
}

const props = defineProps<{
    novel: Novel;
    index: number;
}>();

const showMenu = ref(false);
const showDeleteConfirm = ref(false);
const isDeleting = ref(false);

type StatusKey = 'draft' | 'in_progress' | 'completed' | 'archived';

const statusVariants: Record<StatusKey, 'default' | 'info' | 'success' | 'warning'> = {
    draft: 'default',
    in_progress: 'info',
    completed: 'success',
    archived: 'warning',
};

const statusLabels: Record<StatusKey, string> = {
    draft: 'Draft',
    in_progress: 'In Progress',
    completed: 'Completed',
    archived: 'Archived',
};

const statusVariant = computed(() => statusVariants[props.novel.status as StatusKey] || 'default');
const statusLabel = computed(() => statusLabels[props.novel.status as StatusKey] || 'Draft');

const formattedWordCount = computed(() => {
    if (props.novel.word_count >= 1000) {
        return `${(props.novel.word_count / 1000).toFixed(1)}k`;
    }
    return props.novel.word_count.toString();
});

const formattedDate = computed(() => {
    if (!props.novel.last_edited_at) return 'Never edited';
    const date = new Date(props.novel.last_edited_at);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7) return `${days} days ago`;
    if (days < 30) return `${Math.floor(days / 7)} weeks ago`;
    return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
});

const handleDeleteClick = () => {
    showMenu.value = false;
    showDeleteConfirm.value = true;
};

const deleteNovel = () => {
    isDeleting.value = true;
    router.delete(`/novels/${props.novel.id}`, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            showDeleteConfirm.value = false;
        },
    });
};

const openWorkspace = () => {
    router.visit(`/novels/${props.novel.id}/workspace`);
};

const openEditor = () => {
    router.visit(`/novels/${props.novel.id}/workspace`);
};

const openPlan = () => {
    router.visit(`/novels/${props.novel.id}/workspace?mode=plan`);
};

const openCodex = () => {
    router.visit(`/novels/${props.novel.id}/workspace?mode=codex`);
};
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 20 }"
        :animate="{ opacity: 1, y: 0 }"
        :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: index * 0.05 }"
        class="group relative"
    >
        <div
            class="relative h-full cursor-pointer overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition-all hover:border-violet-300 hover:shadow-md active:scale-[0.98] dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-violet-700"
            @click="openEditor"
        >
            <!-- Header -->
            <div class="mb-3 flex items-start justify-between">
                <div class="flex-1 pr-8">
                    <h3 class="line-clamp-1 text-base font-semibold text-zinc-900 dark:text-white">
                        {{ novel.title }}
                    </h3>
                    <p v-if="novel.pen_name" class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-500">
                        by {{ novel.pen_name.name }}
                    </p>
                </div>

                <!-- Menu Button -->
                <div class="relative">
                    <button
                        type="button"
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-zinc-400 opacity-0 transition-opacity hover:bg-zinc-100 hover:text-zinc-600 group-hover:opacity-100 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        @click.stop="showMenu = !showMenu"
                    >
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="6" r="2" />
                            <circle cx="12" cy="12" r="2" />
                            <circle cx="12" cy="18" r="2" />
                        </svg>
                    </button>

                    <Transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div
                            v-if="showMenu"
                            class="absolute right-0 z-10 mt-1 w-40 origin-top-right rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                            @click.stop
                        >
                            <button
                                type="button"
                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                @click="openWorkspace"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                </svg>
                                Open Workspace
                            </button>
                            <button
                                type="button"
                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                @click="openPlan"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                                </svg>
                                Plan View
                            </button>
                            <button
                                type="button"
                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                @click="openCodex"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Codex View
                            </button>
                            <div class="my-1 border-t border-zinc-200 dark:border-zinc-700"></div>
                            <button
                                type="button"
                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                @click="handleDeleteClick"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Description -->
            <p v-if="novel.description" class="mb-4 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                {{ novel.description }}
            </p>
            <p v-else class="mb-4 text-sm italic text-zinc-400 dark:text-zinc-600">No description</p>

            <!-- Meta Info -->
            <div class="flex flex-wrap items-center gap-2">
                <Badge :variant="statusVariant" size="sm">
                    {{ statusLabel }}
                </Badge>
                <Badge v-if="novel.genre" variant="primary" size="sm">
                    {{ novel.genre }}
                </Badge>
            </div>

            <!-- Footer Stats -->
            <div class="mt-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                <div class="mb-2 flex items-center justify-between">
                    <div class="flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-500">
                        <span class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ formattedWordCount }} words
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                            {{ novel.chapter_count }} chapters
                        </span>
                    </div>
                    <span class="text-xs text-zinc-400">{{ formattedDate }}</span>
                </div>

                <!-- Quick Action Buttons -->
                <div class="mt-2 flex gap-2">
                    <Button size="xs" class="flex-1" @click.stop="openWorkspace">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        Open
                    </Button>
                    <Button size="xs" variant="outline" @click.stop="openPlan">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        Plan
                    </Button>
                    <Button size="xs" variant="outline" @click.stop="openCodex">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Codex
                    </Button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            v-model="showDeleteConfirm"
            title="Delete Novel"
            :message="`Are you sure you want to delete '${novel.title}'? This action cannot be undone and all chapters and scenes will be permanently deleted.`"
            confirm-text="Delete Novel"
            cancel-text="Cancel"
            variant="danger"
            :loading="isDeleting"
            @confirm="deleteNovel"
        />
    </Motion>
</template>
