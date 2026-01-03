<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import ModelSelector from '@/components/ai/ModelSelector.vue';
import ContextSelector from '@/components/chat/ContextSelector.vue';
import QuickPromptsDrawer from '@/components/chat/QuickPromptsDrawer.vue';
import type { ContextItem, ContextSources, ContextLimitInfo } from '@/composables/useChatContext';
import { useCodexAliasDetection, type CodexAliasEntry } from '@/composables/useCodexAliasDetection';
import type { Prompt } from '@/composables/usePrompts';

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
    promptSelected: [prompt: Prompt];
}>();

// Quick prompts drawer state
const showQuickPrompts = ref(false);

const message = ref('');
const selectedModel = ref<string>('');
const selectedConnectionId = ref<number | undefined>();
const textareaRef = ref<HTMLTextAreaElement | null>(null);

// Alias detection
const novelIdRef = computed(() => props.novelId ?? null);
const { detectUniqueEntries } = useCodexAliasDetection({
    novelId: novelIdRef,
    enabled: true,
});

// Detected aliases in current message
const detectedAliases = ref<CodexAliasEntry[]>([]);
const dismissedAliasIds = ref<Set<number>>(new Set());

// Debounce timer for alias detection
let aliasDetectionTimer: ReturnType<typeof setTimeout> | null = null;

// Watch message for alias detection (debounced)
watch(message, (newMessage) => {
    if (aliasDetectionTimer) {
        clearTimeout(aliasDetectionTimer);
    }

    if (!newMessage.trim()) {
        detectedAliases.value = [];
        return;
    }

    aliasDetectionTimer = setTimeout(() => {
        const detected = detectUniqueEntries(newMessage);
        // Filter out already added context and dismissed aliases
        const contextIds = new Set(
            props.contextItems?.filter((c) => c.context_type === 'codex').map((c) => c.reference_id) ?? []
        );
        detectedAliases.value = detected.filter((entry) => !contextIds.has(entry.id) && !dismissedAliasIds.value.has(entry.id));
    }, 300);
});

// Clear dismissed aliases when message is sent
const clearDismissedOnSend = () => {
    dismissedAliasIds.value.clear();
};

// Add detected alias as context
const addAliasAsContext = (entry: CodexAliasEntry) => {
    emit('addContext', 'codex', entry.id);
    // Remove from detected list
    detectedAliases.value = detectedAliases.value.filter((e) => e.id !== entry.id);
};

// Dismiss detected alias (don't show again for this message)
const dismissAlias = (entry: CodexAliasEntry) => {
    dismissedAliasIds.value.add(entry.id);
    detectedAliases.value = detectedAliases.value.filter((e) => e.id !== entry.id);
};

// Add all detected aliases as context
const addAllAliasesAsContext = () => {
    for (const entry of detectedAliases.value) {
        emit('addContext', 'codex', entry.id);
    }
    detectedAliases.value = [];
};

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

// Get entry type icon
const getTypeIcon = (type: string): string => {
    const icons: Record<string, string> = {
        character: '👤',
        location: '📍',
        item: '🎁',
        event: '📅',
        concept: '💡',
        faction: '⚔️',
        species: '🦋',
        other: '📌',
    };
    return icons[type] || '📌';
};

// Get entry type color
const getTypeColor = (type: string): string => {
    const colors: Record<string, string> = {
        character: 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800',
        location: 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800',
        item: 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800',
        event: 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800',
        concept: 'bg-pink-100 text-pink-700 border-pink-200 dark:bg-pink-900/30 dark:text-pink-300 dark:border-pink-800',
        faction: 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800',
        species: 'bg-teal-100 text-teal-700 border-teal-200 dark:bg-teal-900/30 dark:text-teal-300 dark:border-teal-800',
        other: 'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700',
    };
    return colors[type] || colors.other;
};

const handleSend = () => {
    if (!canSend.value) return;

    emit('send', message.value.trim(), selectedModel.value || undefined, selectedConnectionId.value);
    message.value = '';
    clearDismissedOnSend();
    detectedAliases.value = [];

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

// Handle quick prompt selection
const handleQuickPromptSelect = (prompt: Prompt) => {
    // If the prompt has a user_message, set it in the input
    if (prompt.user_message) {
        setMessage(prompt.user_message);
    }
    // Emit event so parent can handle prompts with inputs
    emit('promptSelected', prompt);
    showQuickPrompts.value = false;
};

defineExpose({
    setMessage,
});
</script>

<template>
    <div class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="w-full">
            <!-- Detected Aliases Suggestions -->
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="detectedAliases.length > 0" class="mb-2">
                    <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400 mb-1.5">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <span>Detected Codex entries:</span>
                        <button
                            v-if="detectedAliases.length > 1"
                            type="button"
                            class="text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300 font-medium"
                            @click="addAllAliasesAsContext"
                        >
                            Add all
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <div
                            v-for="entry in detectedAliases"
                            :key="entry.id"
                            :class="[
                                'group flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs transition-all',
                                getTypeColor(entry.type),
                            ]"
                        >
                            <span class="text-sm">{{ getTypeIcon(entry.type) }}</span>
                            <span class="font-medium">{{ entry.name }}</span>
                            <button
                                type="button"
                                class="ml-0.5 rounded-full p-0.5 opacity-70 hover:opacity-100 hover:bg-black/10 dark:hover:bg-white/10 transition-all active:scale-90"
                                title="Add as context"
                                @click="addAliasAsContext(entry)"
                            >
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                class="rounded-full p-0.5 opacity-50 hover:opacity-100 hover:bg-black/10 dark:hover:bg-white/10 transition-all active:scale-90"
                                title="Dismiss"
                                @click="dismissAlias(entry)"
                            >
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>

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

                    <!-- Right side actions -->
                    <div class="flex items-center gap-2">
                        <!-- Quick Prompts Button -->
                        <div class="relative">
                            <button
                                type="button"
                                class="flex h-9 items-center justify-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 text-sm font-medium text-zinc-600 transition-all hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700 active:scale-95 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-violet-600 dark:hover:bg-violet-900/20 dark:hover:text-violet-300"
                                :class="{ 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-500 dark:bg-violet-900/30 dark:text-violet-300': showQuickPrompts }"
                                title="Quick Prompts"
                                @click="showQuickPrompts = !showQuickPrompts"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="hidden sm:inline">Prompts</span>
                            </button>

                            <!-- Quick Prompts Drawer -->
                            <QuickPromptsDrawer
                                :show="showQuickPrompts"
                                @close="showQuickPrompts = false"
                                @select-prompt="handleQuickPromptSelect"
                            />

                            <!-- Backdrop for drawer -->
                            <div
                                v-if="showQuickPrompts"
                                class="fixed inset-0 z-40"
                                @click="showQuickPrompts = false"
                            ></div>
                        </div>

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
            </div>

            <!-- Hints -->
            <div class="mt-2 flex items-center justify-between text-xs text-zinc-400 dark:text-zinc-500">
                <span>Press <kbd class="rounded border border-zinc-300 px-1 dark:border-zinc-600">Enter</kbd> to send, <kbd class="rounded border border-zinc-300 px-1 dark:border-zinc-600">Shift+Enter</kbd> for new line</span>
                <span v-if="message.length > 0">{{ message.length.toLocaleString() }} characters</span>
            </div>
        </div>
    </div>
</template>
