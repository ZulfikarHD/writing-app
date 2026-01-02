<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { animate } from 'motion';
import ChatThreadList from '@/components/chat/ChatThreadList.vue';
import ChatWindow from '@/components/chat/ChatWindow.vue';
import ChatInput from '@/components/chat/ChatInput.vue';
import ChatHeader from '@/components/chat/ChatHeader.vue';
import ContextPreview from '@/components/chat/ContextPreview.vue';
import { useChatContext } from '@/composables/useChatContext';

interface Novel {
    id: number;
    title: string;
}

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

interface Thread {
    id: number;
    novel_id: number;
    title: string | null;
    model: string | null;
    connection_id: number | null;
    is_pinned: boolean;
    archived_at: string | null;
    created_at: string;
    updated_at: string;
    messages: Message[];
}

const props = defineProps<{
    novel: Novel;
    initialSceneContext?: number | null;
}>();

const emit = defineEmits<{
    contextUsed: [];
}>();

// State
const threads = ref<Thread[]>([]);
const activeThread = ref<Thread | null>(null);
const messages = ref<Message[]>([]);
const isLoading = ref(false);
const isLoadingMessages = ref(false);
const isStreaming = ref(false);
const streamingContent = ref('');
const threadListOpen = ref(true);
const error = ref<string | null>(null);
const contextPreviewOpen = ref(false);

// Context management
const {
    contextItems,
    tokenInfo,
    limitInfo,
    sources,
    isLoadingSources,
    fetchContext,
    addContext,
    toggleContext,
    removeContext,
    clearContext,
    fetchSources,
    resetContext,
} = useChatContext(props.novel.id);

// Fetch context when active thread changes
watch(
    () => activeThread.value?.id,
    async (threadId) => {
        if (threadId) {
            await fetchContext(threadId);
        } else {
            resetContext();
        }
    }
);

// Fetch threads
const fetchThreads = async () => {
    isLoading.value = true;
    error.value = null;

    try {
        const response = await fetch(`/api/novels/${props.novel.id}/chat/threads`);
        const data = await response.json();
        threads.value = data.threads || [];

        // If no threads and we have an active thread that's not in the list, clear it
        if (activeThread.value && !threads.value.find((t) => t.id === activeThread.value?.id)) {
            activeThread.value = null;
            messages.value = [];
        }
    } catch (e) {
        error.value = 'Failed to load chat threads';
        console.error('Failed to fetch threads:', e);
    } finally {
        isLoading.value = false;
    }
};

// Fetch thread messages
const fetchMessages = async (threadId: number) => {
    isLoadingMessages.value = true;

    try {
        const response = await fetch(`/api/chat/threads/${threadId}`);
        const data = await response.json();
        activeThread.value = data.thread;
        messages.value = data.thread.messages || [];
    } catch (e) {
        error.value = 'Failed to load messages';
        console.error('Failed to fetch messages:', e);
    } finally {
        isLoadingMessages.value = false;
    }
};

// Create new thread
const createThread = async () => {
    try {
        const response = await fetch(`/api/novels/${props.novel.id}/chat/threads`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({}),
        });

        const data = await response.json();
        const newThread = data.thread;

        threads.value = [newThread, ...threads.value];
        activeThread.value = newThread;
        messages.value = [];
    } catch (e) {
        error.value = 'Failed to create thread';
        console.error('Failed to create thread:', e);
    }
};

// Select a thread
const selectThread = (thread: Thread) => {
    if (activeThread.value?.id === thread.id) return;
    activeThread.value = thread;
    fetchMessages(thread.id);
};

// Delete thread
const deleteThread = async (thread: Thread) => {
    try {
        await fetch(`/api/chat/threads/${thread.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        threads.value = threads.value.filter((t) => t.id !== thread.id);

        if (activeThread.value?.id === thread.id) {
            activeThread.value = threads.value[0] || null;
            if (activeThread.value) {
                fetchMessages(activeThread.value.id);
            } else {
                messages.value = [];
            }
        }
    } catch (e) {
        error.value = 'Failed to delete thread';
        console.error('Failed to delete thread:', e);
    }
};

// Update thread
const updateThread = async (thread: Thread, updates: Partial<Thread>) => {
    try {
        const response = await fetch(`/api/chat/threads/${thread.id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(updates),
        });

        const data = await response.json();
        const updatedThread = data.thread;

        // Update in list
        const index = threads.value.findIndex((t) => t.id === thread.id);
        if (index !== -1) {
            threads.value[index] = { ...threads.value[index], ...updatedThread };
        }

        // Update active thread if it's the same
        if (activeThread.value?.id === thread.id) {
            activeThread.value = { ...activeThread.value, ...updatedThread };
        }
    } catch (e) {
        error.value = 'Failed to update thread';
        console.error('Failed to update thread:', e);
    }
};

// Send message
const sendMessage = async (content: string, model?: string, connectionId?: number) => {
    if (!content.trim()) return;

    // Create thread if none exists
    if (!activeThread.value) {
        await createThread();
    }

    if (!activeThread.value) return;

    const threadId = activeThread.value.id;
    isStreaming.value = true;
    streamingContent.value = '';
    error.value = null;

    // Optimistically add user message
    const tempUserMessage: Message = {
        id: Date.now(),
        thread_id: threadId,
        role: 'user',
        content: content,
        model_used: null,
        tokens_input: null,
        tokens_output: null,
        created_at: new Date().toISOString(),
    };
    messages.value = [...messages.value, tempUserMessage];

    try {
        const response = await fetch(`/api/chat/threads/${threadId}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'text/event-stream',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                message: content,
                model: model || undefined,
                connection_id: connectionId || undefined,
            }),
        });

        if (!response.ok) {
            throw new Error(`Request failed: ${response.status}`);
        }

        const reader = response.body?.getReader();
        const decoder = new TextDecoder();

        if (!reader) {
            throw new Error('No response body');
        }

        let fullContent = '';
        let assistantMessageId: number | null = null;

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            const chunk = decoder.decode(value, { stream: true });
            const lines = chunk.split('\n');

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    try {
                        const data = JSON.parse(line.slice(6));

                        if (data.type === 'user_message') {
                            // Update temp message ID with real ID
                            const idx = messages.value.findIndex((m) => m.id === tempUserMessage.id);
                            if (idx !== -1) {
                                messages.value[idx] = { ...messages.value[idx], id: data.message_id };
                            }
                        } else if (data.type === 'content') {
                            fullContent += data.content;
                            streamingContent.value = fullContent;
                        } else if (data.type === 'done') {
                            assistantMessageId = data.message_id;
                        } else if (data.type === 'error') {
                            error.value = data.error;
                        }
                    } catch {
                        // Ignore parse errors
                    }
                }
            }
        }

        // Add assistant message
        if (fullContent && assistantMessageId) {
            const assistantMessage: Message = {
                id: assistantMessageId,
                thread_id: threadId,
                role: 'assistant',
                content: fullContent,
                model_used: model || activeThread.value?.model || null,
                tokens_input: null,
                tokens_output: null,
                created_at: new Date().toISOString(),
            };
            messages.value = [...messages.value, assistantMessage];
        }

        // Refresh thread list to update timestamps
        fetchThreads();
    } catch (e) {
        error.value = 'Failed to send message';
        console.error('Failed to send message:', e);
        // Remove optimistic user message on error
        messages.value = messages.value.filter((m) => m.id !== tempUserMessage.id);
    } finally {
        isStreaming.value = false;
        streamingContent.value = '';
    }
};

// Regenerate last response
const regenerateResponse = async () => {
    if (!activeThread.value) return;

    isStreaming.value = true;
    streamingContent.value = '';
    error.value = null;

    // Remove last assistant message
    const lastAssistantIdx = messages.value.findLastIndex((m) => m.role === 'assistant');
    if (lastAssistantIdx !== -1) {
        messages.value = messages.value.slice(0, lastAssistantIdx);
    }

    try {
        const response = await fetch(`/api/chat/threads/${activeThread.value.id}/regenerate`, {
            method: 'POST',
            headers: {
                Accept: 'text/event-stream',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (!response.ok) {
            throw new Error(`Request failed: ${response.status}`);
        }

        const reader = response.body?.getReader();
        const decoder = new TextDecoder();

        if (!reader) {
            throw new Error('No response body');
        }

        let fullContent = '';
        let assistantMessageId: number | null = null;

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            const chunk = decoder.decode(value, { stream: true });
            const lines = chunk.split('\n');

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    try {
                        const data = JSON.parse(line.slice(6));

                        if (data.type === 'content') {
                            fullContent += data.content;
                            streamingContent.value = fullContent;
                        } else if (data.type === 'done') {
                            assistantMessageId = data.message_id;
                        } else if (data.type === 'error') {
                            error.value = data.error;
                        }
                    } catch {
                        // Ignore parse errors
                    }
                }
            }
        }

        // Add regenerated message
        if (fullContent && assistantMessageId) {
            const assistantMessage: Message = {
                id: assistantMessageId,
                thread_id: activeThread.value.id,
                role: 'assistant',
                content: fullContent,
                model_used: activeThread.value.model || null,
                tokens_input: null,
                tokens_output: null,
                created_at: new Date().toISOString(),
            };
            messages.value = [...messages.value, assistantMessage];
        }
    } catch (e) {
        error.value = 'Failed to regenerate response';
        console.error('Failed to regenerate:', e);
    } finally {
        isStreaming.value = false;
        streamingContent.value = '';
    }
};

// Toggle thread list
const toggleThreadList = () => {
    threadListOpen.value = !threadListOpen.value;
};

// Check if can regenerate
const canRegenerate = computed(() => {
    return messages.value.length > 0 && messages.value[messages.value.length - 1]?.role === 'assistant' && !isStreaming.value;
});

// Context handlers
const handleAddContext = async (
    type: 'scene' | 'codex' | 'summary' | 'outline' | 'custom',
    referenceId?: number,
    customContent?: string
) => {
    // Create thread if none exists
    if (!activeThread.value) {
        await createThread();
    }
    if (!activeThread.value) return;

    await addContext(activeThread.value.id, type, referenceId, customContent);
};

const handleToggleContext = async (itemId: number) => {
    await toggleContext(itemId);
};

const handleRemoveContext = async (itemId: number) => {
    await removeContext(itemId);
};

const handleClearContext = async () => {
    if (!activeThread.value) return;
    await clearContext(activeThread.value.id);
};

onMounted(async () => {
    await fetchThreads();

    // Check for scene_context parameter (from "Chat with scene" via URL)
    const url = new URL(window.location.href);
    const sceneContextId = url.searchParams.get('scene_context');

    if (sceneContextId) {
        // Create a new thread and add the scene as context
        await createThread();
        if (activeThread.value) {
            await addContext(activeThread.value.id, 'scene', parseInt(sceneContextId));
        }

        // Clean up the URL parameter
        url.searchParams.delete('scene_context');
        window.history.replaceState({}, '', url.toString());
    }

    // Handle initial scene context from prop (from pinned panel)
    if (props.initialSceneContext) {
        await handleInitialSceneContext(props.initialSceneContext);
    }
});

// Handle initial scene context (for pinned panel)
const handleInitialSceneContext = async (sceneId: number) => {
    // Create a new thread and add the scene as context
    await createThread();
    if (activeThread.value) {
        await addContext(activeThread.value.id, 'scene', sceneId);
    }
    emit('contextUsed');
};

// Watch for changes to initialSceneContext prop
watch(
    () => props.initialSceneContext,
    async (newSceneId) => {
        if (newSceneId) {
            await handleInitialSceneContext(newSceneId);
        }
    }
);

// Chat input ref for setting message from prompts
const chatInputRef = ref<InstanceType<typeof ChatInput> | null>(null);

// Handle prompt selection from ChatWindow
const handlePromptSelect = (prompt: string) => {
    chatInputRef.value?.setMessage(prompt);
};

// Animation for panel entrance
const panelRef = ref<HTMLElement | null>(null);

onMounted(() => {
    if (panelRef.value) {
        animate(panelRef.value, { opacity: [0, 1], transform: ['translateY(10px)', 'translateY(0)'] }, { duration: 0.3, easing: [0.16, 1, 0.3, 1] });
    }
});
</script>

<template>
    <div ref="panelRef" class="flex h-full">
        <!-- Thread List Sidebar -->
        <ChatThreadList
            v-if="threadListOpen"
            :threads="threads"
            :active-thread-id="activeThread?.id"
            :is-loading="isLoading"
            @select="selectThread"
            @create="createThread"
            @delete="deleteThread"
            @close="toggleThreadList"
        />

        <!-- Main Chat Area -->
        <div class="flex flex-1 flex-col overflow-hidden bg-white dark:bg-zinc-900">
            <!-- Chat Header -->
            <ChatHeader
                :thread="activeThread"
                :thread-list-open="threadListOpen"
                @toggle-thread-list="toggleThreadList"
                @update-thread="updateThread"
                @delete-thread="deleteThread"
            />

            <!-- Messages Area -->
            <ChatWindow
                :messages="messages"
                :is-loading="isLoadingMessages"
                :is-streaming="isStreaming"
                :streaming-content="streamingContent"
                :error="error"
                :can-regenerate="canRegenerate"
                @regenerate="regenerateResponse"
                @dismiss-error="error = null"
                @select-prompt="handlePromptSelect"
            />

            <!-- Input Area -->
            <ChatInput
                ref="chatInputRef"
                :is-streaming="isStreaming"
                :disabled="!activeThread && threads.length === 0"
                :thread-id="activeThread?.id"
                :novel-id="novel.id"
                :context-items="contextItems"
                :sources="sources"
                :is-loading-sources="isLoadingSources"
                :limit-info="limitInfo"
                @send="sendMessage"
                @create-thread="createThread"
                @add-context="handleAddContext"
                @fetch-sources="fetchSources"
                @open-context-preview="contextPreviewOpen = true"
            />
        </div>

        <!-- Context Preview Modal -->
        <ContextPreview
            :show="contextPreviewOpen"
            :context-items="contextItems"
            :token-info="tokenInfo"
            :limit-info="limitInfo"
            @close="contextPreviewOpen = false"
            @toggle="handleToggleContext"
            @remove="handleRemoveContext"
            @clear="handleClearContext"
        />
    </div>
</template>
