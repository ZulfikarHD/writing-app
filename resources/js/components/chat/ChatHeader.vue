<script setup lang="ts">
import { ref, computed } from 'vue';

interface Thread {
    id: number;
    title: string | null;
    model: string | null;
    is_pinned: boolean;
}

const props = defineProps<{
    thread: Thread | null;
    threadListOpen: boolean;
}>();

const emit = defineEmits<{
    toggleThreadList: [];
    updateThread: [thread: Thread, updates: Partial<Thread>];
    deleteThread: [thread: Thread];
}>();

const isEditing = ref(false);
const editTitle = ref('');
const showMenu = ref(false);

const startEditing = () => {
    if (!props.thread) return;
    editTitle.value = props.thread.title || '';
    isEditing.value = true;
};

const saveTitle = () => {
    if (!props.thread) return;
    emit('updateThread', props.thread, { title: editTitle.value || null });
    isEditing.value = false;
};

const cancelEditing = () => {
    isEditing.value = false;
};

const togglePin = () => {
    if (!props.thread) return;
    emit('updateThread', props.thread, { is_pinned: !props.thread.is_pinned });
    showMenu.value = false;
};

const handleDelete = () => {
    if (!props.thread) return;
    emit('deleteThread', props.thread);
    showMenu.value = false;
};

const displayTitle = computed(() => {
    if (!props.thread) return 'New Chat';
    return props.thread.title || 'New Chat';
});
</script>

<template>
    <header class="flex items-center justify-between border-b border-zinc-200 bg-white px-4 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center gap-3">
            <!-- Toggle Thread List -->
            <button
                type="button"
                class="rounded-md p-1.5 text-zinc-500 transition-all hover:bg-zinc-100 hover:text-zinc-700 active:scale-95 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                :title="threadListOpen ? 'Hide chat list' : 'Show chat list'"
                @click="emit('toggleThreadList')"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Title -->
            <div v-if="isEditing" class="flex items-center gap-2">
                <input
                    v-model="editTitle"
                    type="text"
                    class="rounded-md border border-zinc-300 bg-white px-2 py-1 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-800"
                    placeholder="Chat title..."
                    @keyup.enter="saveTitle"
                    @keyup.escape="cancelEditing"
                />
                <button
                    type="button"
                    class="rounded p-1 text-green-600 hover:bg-green-100 dark:hover:bg-green-900/30"
                    @click="saveTitle"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
                <button
                    type="button"
                    class="rounded p-1 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    @click="cancelEditing"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div v-else class="flex items-center gap-2">
                <h1 class="text-sm font-medium text-zinc-900 dark:text-white">
                    {{ displayTitle }}
                </h1>
                <button
                    v-if="thread"
                    type="button"
                    class="rounded p-1 text-zinc-400 transition-all hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                    title="Edit title"
                    @click="startEditing"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                        />
                    </svg>
                </button>
                <span v-if="thread?.is_pinned" class="text-amber-500" title="Pinned">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                        />
                    </svg>
                </span>
            </div>
        </div>

        <!-- Actions Menu -->
        <div v-if="thread" class="relative">
            <button
                type="button"
                class="rounded-md p-1.5 text-zinc-500 transition-all hover:bg-zinc-100 hover:text-zinc-700 active:scale-95 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                @click="showMenu = !showMenu"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </button>

            <!-- Dropdown -->
            <Transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
            >
                <div
                    v-if="showMenu"
                    class="absolute right-0 z-10 mt-1 w-48 rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                >
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        @click="togglePin"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                            />
                        </svg>
                        {{ thread.is_pinned ? 'Unpin' : 'Pin' }} Chat
                    </button>
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                        @click="handleDelete"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                        Delete Chat
                    </button>
                </div>
            </Transition>

            <!-- Backdrop -->
            <div v-if="showMenu" class="fixed inset-0 z-0" @click="showMenu = false"></div>
        </div>
    </header>
</template>
