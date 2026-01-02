<script setup lang="ts">
import { ref, computed } from 'vue';

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

const props = withDefaults(
    defineProps<{
        message: Message;
        isStreaming?: boolean;
    }>(),
    {
        isStreaming: false,
    }
);

const copied = ref(false);

const isUser = computed(() => props.message.role === 'user');
const isAssistant = computed(() => props.message.role === 'assistant');

const formatTime = (dateStr: string): string => {
    const date = new Date(dateStr);
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(props.message.content);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (e) {
        console.error('Failed to copy:', e);
    }
};

// Simple markdown-like formatting
const formattedContent = computed(() => {
    let content = props.message.content;

    // Escape HTML
    content = content.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

    // Code blocks
    content = content.replace(/```(\w*)\n([\s\S]*?)```/g, '<pre class="my-2 overflow-x-auto rounded-lg bg-zinc-800 p-3 text-sm text-zinc-100"><code>$2</code></pre>');

    // Inline code
    content = content.replace(/`([^`]+)`/g, '<code class="rounded bg-zinc-200 px-1 py-0.5 text-sm dark:bg-zinc-700">$1</code>');

    // Bold
    content = content.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

    // Italic
    content = content.replace(/\*([^*]+)\*/g, '<em>$1</em>');

    // Line breaks
    content = content.replace(/\n/g, '<br>');

    return content;
});
</script>

<template>
    <div class="group flex gap-3" :class="[isUser ? 'justify-end' : 'justify-start']">
        <!-- Avatar for assistant -->
        <div v-if="isAssistant" class="shrink-0">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-100 dark:bg-violet-900/30">
                <svg class="h-5 w-5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Message Content -->
        <div
            class="relative max-w-[80%] rounded-2xl px-4 py-2.5"
            :class="[
                isUser
                    ? 'rounded-br-md bg-violet-600 text-white'
                    : 'rounded-bl-md bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100',
            ]"
        >
            <!-- Message Text -->
            <div class="prose prose-sm max-w-none" :class="[isUser ? 'prose-invert' : 'dark:prose-invert']" v-html="formattedContent"></div>

            <!-- Streaming cursor -->
            <span v-if="isStreaming" class="inline-block h-4 w-1 animate-pulse bg-current"></span>

            <!-- Metadata -->
            <div
                class="mt-1 flex items-center gap-2 text-xs"
                :class="[isUser ? 'text-violet-200' : 'text-zinc-400 dark:text-zinc-500']"
            >
                <span>{{ formatTime(message.created_at) }}</span>
                <span v-if="message.model_used" class="hidden sm:inline">• {{ message.model_used }}</span>
            </div>

            <!-- Copy Button (Assistant only) -->
            <button
                v-if="isAssistant && !isStreaming"
                type="button"
                class="absolute -right-2 top-2 rounded-full bg-white p-1.5 opacity-0 shadow-md transition-all group-hover:opacity-100 active:scale-95 dark:bg-zinc-700"
                :class="[copied ? 'text-green-600' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200']"
                :title="copied ? 'Copied!' : 'Copy'"
                @click="copyToClipboard"
            >
                <svg v-if="copied" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                    />
                </svg>
            </button>
        </div>

        <!-- Avatar for user -->
        <div v-if="isUser" class="shrink-0">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-700">
                <svg class="h-5 w-5 text-zinc-600 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        </div>
    </div>
</template>
