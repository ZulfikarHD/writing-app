<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import ModelSelector from '@/components/ai/ModelSelector.vue';
import ContextSelector from '@/components/chat/ContextSelector.vue';
import type { ContextItem, ContextSources, ContextLimitInfo } from '@/composables/useChatContext';

const props = defineProps<{
    isStreaming: boolean;
    disabled?: boolean;
    threadId?: number | null;
    novelId?: number;
    contextItems?: ContextItem[];
    sources?: ContextSources | null;
    isLoadingSources?: boolean;
    limitInfo?: ContextLimitInfo | null;
}>();

const emit = defineEmits<{
    send: [content: string, model?: string, connectionId?: number];
    createThread: [];
    addContext: [type: ContextItem['context_type'], referenceId?: number, customContent?: string];
    fetchSources: [];
    openContextPreview: [];
}>();

const message = ref('');
const selectedModel = ref<string>('');
const selectedConnectionId = ref<number | undefined>();
const textareaRef = ref<HTMLTextAreaElement | null>(null);

const canSend = computed(() => {
    return message.value.trim().length > 0 && !props.isStreaming && !props.disabled;
});

// Active context count
const activeContextCount = computed(() => {
    return props.contextItems?.filter((item) => item.is_active).length ?? 0;
});

// Format tokens for display
const formatTokens = (tokens: number): string => {
    if (tokens >= 1000) {
        return `${(tokens / 1000).toFixed(1)}k`;
    }
    return tokens.toString();
};

const handleSend = () => {
    if (!canSend.value) return;

    emit('send', message.value.trim(), selectedModel.value || undefined, selectedConnectionId.value);
    message.value = '';

    // Reset textarea height
    if (textareaRef.value) {
        textareaRef.value.style.height = 'auto';
    }
};

const handleKeyDown = (e: KeyboardEvent) => {
    // Send on Enter (without Shift)
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        handleSend();
    }
};

// Auto-resize textarea
const adjustTextareaHeight = () => {
    if (textareaRef.value) {
        textareaRef.value.style.height = 'auto';
        const maxHeight = 200;
        textareaRef.value.style.height = Math.min(textareaRef.value.scrollHeight, maxHeight) + 'px';
    }
};

watch(message, () => {
    adjustTextareaHeight();
});

onMounted(() => {
    textareaRef.value?.focus();
});

// Expose method to set message from parent
const setMessage = (text: string) => {
    message.value = text;
    // Focus and adjust height
    nextTick(() => {
        adjustTextareaHeight();
        textareaRef.value?.focus();
    });
};

defineExpose({
    setMessage,
});
</script>

<template>
    <div class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="w-full">
            <!-- Context Bar (shows when context is added) -->
            <div v-if="activeContextCount > 0" class="mb-2 flex items-center gap-2">
                <button
                    type="button"
                    class="flex items-center gap-1.5 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-sm text-violet-700 transition-all hover:bg-violet-100 active:scale-95 dark:border-violet-800 dark:bg-violet-900/30 dark:text-violet-300 dark:hover:bg-violet-900/50"
                    @click="emit('openContextPreview')"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    {{ activeContextCount }} context item{{ activeContextCount !== 1 ? 's' : '' }}
                    <span v-if="limitInfo" class="text-xs opacity-75">({{ formatTokens(limitInfo.tokens_used) }} tokens)</span>
                </button>
                <span
                    v-if="limitInfo && limitInfo.usage_percentage > 80"
                    :class="[
                        'text-xs',
                        limitInfo.usage_percentage > 100 ? 'text-red-500' : 'text-amber-500',
                    ]"
                >
                    {{ limitInfo.usage_percentage > 100 ? 'Over limit!' : 'Near limit' }}
                </span>
            </div>

            <!-- Input Area -->
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 focus-within:border-violet-500 focus-within:ring-1 focus-within:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800">
                <!-- Textarea Row -->
                <div class="flex items-start gap-3 p-3">
                    <!-- Context Selector -->
                    <div v-if="novelId" class="shrink-0 pt-0.5">
                        <ContextSelector
                            :thread-id="threadId ?? null"
                            :novel-id="novelId"
                            :sources="sources ?? null"
                            :context-items="contextItems ?? []"
                            :is-loading-sources="isLoadingSources ?? false"
                            @add-context="(type, refId, content) => emit('addContext', type, refId, content)"
                            @fetch-sources="emit('fetchSources')"
                        />
                    </div>

                    <!-- Textarea -->
                    <div class="flex-1">
                        <textarea
                            ref="textareaRef"
                            v-model="message"
                            :disabled="isStreaming || disabled"
                            placeholder="Ask anything about your story..."
                            rows="1"
                            class="max-h-[200px] w-full resize-none bg-transparent text-sm text-zinc-900 placeholder-zinc-500 focus:outline-none disabled:opacity-50 dark:text-white dark:placeholder-zinc-400"
                            @keydown="handleKeyDown"
                        ></textarea>
                    </div>
                </div>

                <!-- Actions Row - Fixed at bottom -->
                <div class="flex items-center justify-between gap-3 border-t border-zinc-200 px-3 py-2 dark:border-zinc-700">
                    <!-- Model Selector -->
                    <ModelSelector v-model="selectedModel" v-model:connection-id="selectedConnectionId" size="sm" placeholder="Auto" />

                    <!-- Send Button -->
                    <button
                        type="button"
                        :disabled="!canSend"
                        class="flex h-9 items-center justify-center gap-2 rounded-lg bg-violet-600 px-4 text-sm font-medium text-white transition-all hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-50 active:scale-95"
                        :title="isStreaming ? 'Generating...' : 'Send message'"
                        @click="handleSend"
                    >
                        <svg v-if="isStreaming" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span>{{ isStreaming ? 'Generating...' : 'Send' }}</span>
                    </button>
                </div>
            </div>

            <!-- Hints -->
            <div class="mt-2 flex items-center justify-between text-xs text-zinc-400 dark:text-zinc-500">
                <span>Press <kbd class="rounded border border-zinc-300 px-1 dark:border-zinc-600">Enter</kbd> to send, <kbd class="rounded border border-zinc-300 px-1 dark:border-zinc-600">Shift+Enter</kbd> for new line</span>
                <span v-if="message.length > 0">{{ message.length.toLocaleString() }} characters</span>
            </div>
        </div>
    </div>
</template>
