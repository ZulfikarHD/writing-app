<script setup lang="ts">
import { ref, computed } from 'vue';

interface Message {
    id: number;
    role: string;
    content: string;
    created_at: string;
}

interface Thread {
    id: number;
    title: string | null;
    updated_at: string;
    messages?: Message[];
}

const props = defineProps<{
    threads: Thread[];
    activeThreadId?: number;
    isLoading: boolean;
}>();

const emit = defineEmits<{
    select: [thread: Thread];
    create: [];
    delete: [thread: Thread];
    close: [];
}>();

const searchQuery = ref('');

const filteredThreads = computed(() => {
    if (!searchQuery.value) return props.threads;
    const query = searchQuery.value.toLowerCase();
    return props.threads.filter(
        (t) => t.title?.toLowerCase().includes(query) || t.messages?.[0]?.content?.toLowerCase().includes(query)
    );
});

const formatDate = (dateStr: string): string => {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (days === 0) {
        return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    } else if (days === 1) {
        return 'Yesterday';
    } else if (days < 7) {
        return date.toLocaleDateString('id-ID', { weekday: 'short' });
    } else {
        return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
    }
};

const getThreadPreview = (thread: Thread): string => {
    if (thread.title) return thread.title;
    const firstMessage = thread.messages?.[0];
    if (firstMessage) {
        return firstMessage.content.slice(0, 50) + (firstMessage.content.length > 50 ? '...' : '');
    }
    return 'New Chat';
};

// List animation
const listRef = ref<HTMLElement | null>(null);

// Confirm delete
const confirmingDelete = ref<number | null>(null);

const handleDelete = (thread: Thread, event: Event) => {
    event.stopPropagation();
    if (confirmingDelete.value === thread.id) {
        emit('delete', thread);
        confirmingDelete.value = null;
    } else {
        confirmingDelete.value = thread.id;
        // Reset after 3 seconds
        setTimeout(() => {
            if (confirmingDelete.value === thread.id) {
                confirmingDelete.value = null;
            }
        }, 3000);
    }
};
</script>

<template>
    <div class="flex h-full w-72 flex-col border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-zinc-200 p-3 dark:border-zinc-700">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">Chats</h2>
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    class="rounded-md p-1.5 text-zinc-500 transition-all hover:bg-zinc-200 hover:text-zinc-700 active:scale-95 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                    title="New Chat"
                    @click="emit('create')"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
                <button
                    type="button"
                    class="rounded-md p-1.5 text-zinc-500 transition-all hover:bg-zinc-200 hover:text-zinc-700 active:scale-95 dark:hover:bg-zinc-700 dark:hover:text-zinc-300 lg:hidden"
                    title="Close"
                    @click="emit('close')"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Search -->
        <div class="p-2">
            <div class="relative">
                <svg
                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search chats..."
                    class="w-full rounded-md border border-zinc-200 bg-white py-1.5 pl-9 pr-3 text-sm placeholder-zinc-400 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-700 dark:placeholder-zinc-500"
                />
            </div>
        </div>

        <!-- Thread List -->
        <div ref="listRef" class="flex-1 overflow-y-auto p-2">
            <!-- Loading State -->
            <div v-if="isLoading" class="space-y-2">
                <div v-for="i in 3" :key="i" class="animate-pulse rounded-lg bg-zinc-200 p-3 dark:bg-zinc-700">
                    <div class="h-4 w-3/4 rounded bg-zinc-300 dark:bg-zinc-600"></div>
                    <div class="mt-2 h-3 w-1/2 rounded bg-zinc-300 dark:bg-zinc-600"></div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="threads.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
                <svg class="h-12 w-12 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                    />
                </svg>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">No chats yet</p>
                <button
                    type="button"
                    class="mt-3 rounded-md bg-violet-600 px-3 py-1.5 text-sm font-medium text-white transition-all hover:bg-violet-700 active:scale-95"
                    @click="emit('create')"
                >
                    Start a Chat
                </button>
            </div>

            <!-- Thread Items -->
            <div v-else class="space-y-1">
                <button
                    v-for="thread in filteredThreads"
                    :key="thread.id"
                    type="button"
                    class="thread-item group relative w-full rounded-lg p-3 text-left transition-all active:scale-[0.98]"
                    :class="[
                        activeThreadId === thread.id
                            ? 'bg-violet-100 text-violet-900 dark:bg-violet-900/30 dark:text-violet-100'
                            : 'text-zinc-700 hover:bg-zinc-200 dark:text-zinc-300 dark:hover:bg-zinc-700',
                    ]"
                    @click="emit('select', thread)"
                >
                    <div class="flex items-start justify-between gap-2">
                        <span class="line-clamp-1 text-sm font-medium">
                            {{ getThreadPreview(thread) }}
                        </span>
                        <span class="shrink-0 text-xs text-zinc-400 dark:text-zinc-500">
                            {{ formatDate(thread.updated_at) }}
                        </span>
                    </div>

                    <!-- Delete button -->
                    <button
                        type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 opacity-0 transition-all group-hover:opacity-100"
                        :class="[
                            confirmingDelete === thread.id
                                ? 'bg-red-100 text-red-600 opacity-100 dark:bg-red-900/30 dark:text-red-400'
                                : 'text-zinc-400 hover:bg-zinc-300 hover:text-zinc-600 dark:hover:bg-zinc-600 dark:hover:text-zinc-300',
                        ]"
                        :title="confirmingDelete === thread.id ? 'Click again to confirm' : 'Delete'"
                        @click="handleDelete(thread, $event)"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                    </button>
                </button>
            </div>
        </div>
    </div>
</template>
