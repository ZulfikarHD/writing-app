<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import ModelSelector from '@/components/ai/ModelSelector.vue';

const props = defineProps<{
    isStreaming: boolean;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    send: [content: string, model?: string, connectionId?: number];
    createThread: [];
}>();

const message = ref('');
const selectedModel = ref<string>('');
const selectedConnectionId = ref<number | undefined>();
const textareaRef = ref<HTMLTextAreaElement | null>(null);

const canSend = computed(() => {
    return message.value.trim().length > 0 && !props.isStreaming && !props.disabled;
});

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
</script>

<template>
    <div class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mx-auto max-w-3xl">
            <!-- Input Area -->
            <div class="flex items-end gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-2 focus-within:border-violet-500 focus-within:ring-1 focus-within:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800">
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

                <!-- Actions -->
                <div class="flex shrink-0 items-center gap-2">
                    <!-- Model Selector -->
                    <ModelSelector v-model="selectedModel" v-model:connection-id="selectedConnectionId" size="sm" placeholder="Auto" />

                    <!-- Send Button -->
                    <button
                        type="button"
                        :disabled="!canSend"
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-600 text-white transition-all hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-50 active:scale-95"
                        :title="isStreaming ? 'Generating...' : 'Send message'"
                        @click="handleSend"
                    >
                        <svg v-if="isStreaming" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
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
