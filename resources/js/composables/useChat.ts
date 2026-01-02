import { ref, computed } from 'vue';

export interface ChatMessage {
    id: number;
    thread_id: number;
    role: 'user' | 'assistant' | 'system';
    content: string;
    model_used: string | null;
    tokens_input: number | null;
    tokens_output: number | null;
    created_at: string;
}

export interface ChatThread {
    id: number;
    novel_id: number;
    title: string | null;
    model: string | null;
    connection_id: number | null;
    is_pinned: boolean;
    archived_at: string | null;
    created_at: string;
    updated_at: string;
    messages?: ChatMessage[];
}

/**
 * Composable for managing chat state and streaming.
 */
export function useChat(novelId: number) {
    const threads = ref<ChatThread[]>([]);
    const activeThread = ref<ChatThread | null>(null);
    const messages = ref<ChatMessage[]>([]);
    const isLoading = ref(false);
    const isStreaming = ref(false);
    const streamingContent = ref('');
    const error = ref<string | null>(null);

    const csrfToken = computed(() => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    });

    /**
     * Fetch all threads for the novel.
     */
    const fetchThreads = async () => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/novels/${novelId}/chat/threads`);
            const data = await response.json();
            threads.value = data.threads || [];
        } catch (e) {
            error.value = 'Failed to load chat threads';
            console.error('Failed to fetch threads:', e);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Fetch messages for a specific thread.
     */
    const fetchMessages = async (threadId: number) => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/chat/threads/${threadId}`);
            const data = await response.json();
            activeThread.value = data.thread;
            messages.value = data.thread.messages || [];
        } catch (e) {
            error.value = 'Failed to load messages';
            console.error('Failed to fetch messages:', e);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Create a new thread.
     */
    const createThread = async (title?: string): Promise<ChatThread | null> => {
        error.value = null;

        try {
            const response = await fetch(`/api/novels/${novelId}/chat/threads`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify({ title }),
            });

            const data = await response.json();
            const newThread = data.thread;

            threads.value = [newThread, ...threads.value];
            activeThread.value = newThread;
            messages.value = [];

            return newThread;
        } catch (e) {
            error.value = 'Failed to create thread';
            console.error('Failed to create thread:', e);
            return null;
        }
    };

    /**
     * Send a message with streaming response.
     */
    const sendMessage = async (
        content: string,
        options?: { model?: string; connectionId?: number }
    ): Promise<void> => {
        if (!content.trim()) return;

        // Create thread if none exists
        if (!activeThread.value) {
            const newThread = await createThread();
            if (!newThread) return;
        }

        if (!activeThread.value) return;

        const threadId = activeThread.value.id;
        isStreaming.value = true;
        streamingContent.value = '';
        error.value = null;

        // Optimistically add user message
        const tempUserMessage: ChatMessage = {
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
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify({
                    message: content,
                    model: options?.model,
                    connection_id: options?.connectionId,
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
                const assistantMessage: ChatMessage = {
                    id: assistantMessageId,
                    thread_id: threadId,
                    role: 'assistant',
                    content: fullContent,
                    model_used: options?.model || activeThread.value?.model || null,
                    tokens_input: null,
                    tokens_output: null,
                    created_at: new Date().toISOString(),
                };
                messages.value = [...messages.value, assistantMessage];
            }

            // Refresh thread list
            await fetchThreads();
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

    /**
     * Delete a thread.
     */
    const deleteThread = async (threadId: number): Promise<void> => {
        error.value = null;

        try {
            await fetch(`/api/chat/threads/${threadId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            threads.value = threads.value.filter((t) => t.id !== threadId);

            if (activeThread.value?.id === threadId) {
                activeThread.value = threads.value[0] || null;
                if (activeThread.value) {
                    await fetchMessages(activeThread.value.id);
                } else {
                    messages.value = [];
                }
            }
        } catch (e) {
            error.value = 'Failed to delete thread';
            console.error('Failed to delete thread:', e);
        }
    };

    /**
     * Update a thread.
     */
    const updateThread = async (threadId: number, updates: Partial<ChatThread>): Promise<void> => {
        error.value = null;

        try {
            const response = await fetch(`/api/chat/threads/${threadId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify(updates),
            });

            const data = await response.json();
            const updatedThread = data.thread;

            // Update in list
            const index = threads.value.findIndex((t) => t.id === threadId);
            if (index !== -1) {
                threads.value[index] = { ...threads.value[index], ...updatedThread };
            }

            // Update active thread if same
            if (activeThread.value?.id === threadId) {
                activeThread.value = { ...activeThread.value, ...updatedThread };
            }
        } catch (e) {
            error.value = 'Failed to update thread';
            console.error('Failed to update thread:', e);
        }
    };

    /**
     * Select and load a thread.
     */
    const selectThread = async (thread: ChatThread): Promise<void> => {
        if (activeThread.value?.id === thread.id) return;
        activeThread.value = thread;
        await fetchMessages(thread.id);
    };

    /**
     * Clear error.
     */
    const clearError = () => {
        error.value = null;
    };

    /**
     * Check if can regenerate (last message is from assistant).
     */
    const canRegenerate = computed(() => {
        return (
            messages.value.length > 0 &&
            messages.value[messages.value.length - 1]?.role === 'assistant' &&
            !isStreaming.value
        );
    });

    return {
        // State
        threads,
        activeThread,
        messages,
        isLoading,
        isStreaming,
        streamingContent,
        error,
        canRegenerate,

        // Actions
        fetchThreads,
        fetchMessages,
        createThread,
        sendMessage,
        deleteThread,
        updateThread,
        selectThread,
        clearError,
    };
}
