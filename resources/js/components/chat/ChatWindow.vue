<script setup lang="ts">
import { ref, watch, nextTick, onMounted } from 'vue';
import { animate, spring, stagger } from 'motion';
import ChatMessage from './ChatMessage.vue';

interface Message {
    id: number;
    thread_id: number;
    role: 'user' | 'assistant' | 'system';
    content: string;
    model_used: string | null;
    tokens_input: number | null;
    tokens_output: number | null;
    created_at: string;
}

const props = defineProps<{
    messages: Message[];
    isLoading: boolean;
    isStreaming: boolean;
    streamingContent: string;
    error: string | null;
    canRegenerate: boolean;
}>();

const emit = defineEmits<{
    regenerate: [];
    dismissError: [];
}>();

const scrollContainer = ref<HTMLElement | null>(null);

// Auto-scroll to bottom when new messages arrive
const scrollToBottom = async () => {
    await nextTick();
    if (scrollContainer.value) {
        scrollContainer.value.scrollTo({
            top: scrollContainer.value.scrollHeight,
            behavior: 'smooth',
        });
    }
};

// Watch for new messages
watch(
    () => props.messages.length,
    () => {
        scrollToBottom();
    }
);

// Watch for streaming content
watch(
    () => props.streamingContent,
    () => {
        scrollToBottom();
    }
);

// Initial scroll
onMounted(() => {
    scrollToBottom();
});

// Animate messages on load
const animateMessages = () => {
    if (scrollContainer.value) {
        const messages = scrollContainer.value.querySelectorAll('.message-item');
        if (messages.length > 0) {
            animate(
                messages,
                { opacity: [0, 1], transform: ['translateY(10px)', 'translateY(0)'] },
                { duration: 0.3, delay: stagger(0.05), easing: spring({ stiffness: 300, damping: 25 }) }
            );
        }
    }
};

watch(
    () => props.isLoading,
    (loading) => {
        if (!loading) {
            nextTick(() => animateMessages());
        }
    }
);
</script>

<template>
    <div ref="scrollContainer" class="flex-1 overflow-y-auto px-4 py-6">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex flex-col items-center justify-center py-12">
            <div class="h-8 w-8 animate-spin rounded-full border-2 border-violet-600 border-t-transparent"></div>
            <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Loading messages...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="messages.length === 0 && !isStreaming" class="flex flex-col items-center justify-center py-12 text-center">
            <div class="rounded-full bg-violet-100 p-4 dark:bg-violet-900/30">
                <svg class="h-10 w-10 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                    />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">Start a conversation</h3>
            <p class="mt-1 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">
                Ask questions about your story, brainstorm ideas, or get help with your writing.
            </p>

            <!-- Quick prompts -->
            <div class="mt-6 flex flex-wrap justify-center gap-2">
                <button
                    v-for="prompt in ['Help me brainstorm', 'Suggest a plot twist', 'Describe a character']"
                    :key="prompt"
                    type="button"
                    class="rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-700 transition-all hover:border-violet-300 hover:bg-violet-50 active:scale-95 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-violet-600 dark:hover:bg-violet-900/20"
                >
                    {{ prompt }}
                </button>
            </div>
        </div>

        <!-- Messages -->
        <div v-else class="mx-auto max-w-3xl space-y-4">
            <ChatMessage
                v-for="message in messages"
                :key="message.id"
                :message="message"
                class="message-item"
            />

            <!-- Streaming Message -->
            <ChatMessage
                v-if="isStreaming && streamingContent"
                :message="{
                    id: -1,
                    thread_id: 0,
                    role: 'assistant',
                    content: streamingContent,
                    model_used: null,
                    tokens_input: null,
                    tokens_output: null,
                    created_at: new Date().toISOString(),
                }"
                :is-streaming="true"
                class="message-item"
            />

            <!-- Streaming Indicator (no content yet) -->
            <div v-else-if="isStreaming" class="flex justify-start">
                <div class="rounded-2xl rounded-bl-md bg-zinc-100 px-4 py-3 dark:bg-zinc-800">
                    <div class="flex items-center gap-1">
                        <div class="h-2 w-2 animate-bounce rounded-full bg-violet-500" style="animation-delay: 0ms"></div>
                        <div class="h-2 w-2 animate-bounce rounded-full bg-violet-500" style="animation-delay: 150ms"></div>
                        <div class="h-2 w-2 animate-bounce rounded-full bg-violet-500" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            </div>

            <!-- Regenerate Button -->
            <div v-if="canRegenerate" class="flex justify-center pt-2">
                <button
                    type="button"
                    class="flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-600 transition-all hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700 active:scale-95 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-violet-600 dark:hover:bg-violet-900/20 dark:hover:text-violet-300"
                    @click="emit('regenerate')"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        />
                    </svg>
                    Regenerate response
                </button>
            </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="fixed bottom-24 left-1/2 z-10 -translate-x-1/2">
            <div class="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700 shadow-lg dark:border-red-800 dark:bg-red-900/30 dark:text-red-400">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ error }}</span>
                <button type="button" class="ml-2 rounded p-0.5 hover:bg-red-100 dark:hover:bg-red-800" @click="emit('dismissError')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
