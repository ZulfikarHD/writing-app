<script setup lang="ts">
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

const statusColors = {
    draft: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
    in_progress: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    completed: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    archived: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
};

const statusLabels = {
    draft: 'Draft',
    in_progress: 'In Progress',
    completed: 'Completed',
    archived: 'Archived',
};

const statusColor = computed(() => statusColors[props.novel.status as keyof typeof statusColors] || statusColors.draft);
const statusLabel = computed(() => statusLabels[props.novel.status as keyof typeof statusLabels] || 'Draft');

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

const deleteNovel = () => {
    if (confirm(`Are you sure you want to delete "${props.novel.title}"? This action cannot be undone.`)) {
        router.delete(`/novels/${props.novel.id}`);
    }
    showMenu.value = false;
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
            class="relative h-full overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition-all hover:border-violet-300 hover:shadow-md active:scale-[0.98] dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-violet-700"
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
                        @click.stop="showMenu = !showMenu"
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-zinc-400 opacity-0 transition-opacity hover:bg-zinc-100 hover:text-zinc-600 group-hover:opacity-100 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
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
                            @click.stop
                            class="absolute right-0 z-10 mt-1 w-36 origin-top-right rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                        >
                            <button
                                @click="deleteNovel"
                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
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
            <p v-else class="mb-4 text-sm italic text-zinc-400 dark:text-zinc-600">
                No description
            </p>

            <!-- Meta Info -->
            <div class="flex flex-wrap items-center gap-2">
                <span :class="statusColor" class="rounded-full px-2 py-0.5 text-xs font-medium">
                    {{ statusLabel }}
                </span>
                <span v-if="novel.genre" class="rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-400">
                    {{ novel.genre }}
                </span>
            </div>

            <!-- Footer Stats -->
            <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800">
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
        </div>
    </Motion>
</template>
