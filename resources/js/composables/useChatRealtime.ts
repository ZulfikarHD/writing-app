import { ref, watch, onUnmounted, type Ref } from 'vue';

/**
 * Message payload from WebSocket
 */
export interface RealtimeMessage {
    id: number;
    thread_id: number;
    role: 'user' | 'assistant' | 'system';
    content: string;
    model_used: string | null;
    tokens_input: number | null;
    tokens_output: number | null;
    created_at: string;
}

/**
 * Thread update payload from WebSocket
 */
export interface RealtimeThreadUpdate {
    thread: {
        id: number;
        novel_id: number;
        title: string | null;
        model: string | null;
        is_pinned: boolean;
        archived_at: string | null;
        updated_at: string;
    };
    update_type: 'updated' | 'deleted' | 'archived' | 'restored';
}

export interface UseChatRealtimeOptions {
    threadId: Ref<number | null | undefined>;
    novelId?: Ref<number | null | undefined>;
    onNewMessage?: (message: RealtimeMessage) => void;
    onThreadUpdate?: (update: RealtimeThreadUpdate) => void;
    enabled?: boolean;
}

/**
 * Composable for real-time chat updates using Laravel Echo/Reverb.
 *
 * Sprint 21 (F4): Real-time Chat Updates
 *
 * Usage:
 * ```ts
 * const { isConnected } = useChatRealtime({
 *   threadId: computed(() => activeThread.value?.id),
 *   onNewMessage: (message) => {
 *     messages.value.push(message);
 *   },
 *   onThreadUpdate: (update) => {
 *     // Handle thread updates
 *   }
 * });
 * ```
 */
export function useChatRealtime(options: UseChatRealtimeOptions) {
    const { threadId, novelId, onNewMessage, onThreadUpdate, enabled = true } = options;

    const isConnected = ref(false);
    const connectionError = ref<string | null>(null);

    // Track subscribed channels for cleanup
    let threadChannel: ReturnType<typeof window.Echo.private> | null = null;
    let novelChannel: ReturnType<typeof window.Echo.private> | null = null;

    /**
     * Subscribe to a chat thread channel for new messages
     */
    const subscribeToThread = (id: number) => {
        if (!window.Echo || !enabled) {
            return;
        }

        // Leave previous channel if any
        if (threadChannel) {
            window.Echo.leave(`chat.thread.${threadId.value}`);
            threadChannel = null;
        }

        try {
            threadChannel = window.Echo.private(`chat.thread.${id}`)
                .listen('.message.created', (e: { message: RealtimeMessage }) => {
                    if (onNewMessage) {
                        onNewMessage(e.message);
                    }
                })
                .listen('.thread.updated', (e: RealtimeThreadUpdate) => {
                    if (onThreadUpdate) {
                        onThreadUpdate(e);
                    }
                })
                .error((error: Error) => {
                    console.error('Echo thread channel error:', error);
                    connectionError.value = 'Failed to connect to chat channel';
                    isConnected.value = false;
                });

            isConnected.value = true;
            connectionError.value = null;
        } catch (error) {
            console.error('Failed to subscribe to thread channel:', error);
            connectionError.value = 'Failed to subscribe to chat channel';
        }
    };

    /**
     * Subscribe to a novel channel for thread list updates
     */
    const subscribeToNovel = (id: number) => {
        if (!window.Echo || !enabled) {
            return;
        }

        // Leave previous channel if any
        if (novelChannel) {
            window.Echo.leave(`chat.novel.${novelId?.value}`);
            novelChannel = null;
        }

        try {
            novelChannel = window.Echo.private(`chat.novel.${id}`)
                .listen('.thread.updated', (e: RealtimeThreadUpdate) => {
                    if (onThreadUpdate) {
                        onThreadUpdate(e);
                    }
                })
                .error((error: Error) => {
                    console.error('Echo novel channel error:', error);
                });
        } catch (error) {
            console.error('Failed to subscribe to novel channel:', error);
        }
    };

    /**
     * Unsubscribe from all channels
     */
    const unsubscribe = () => {
        if (window.Echo) {
            if (threadId.value) {
                window.Echo.leave(`chat.thread.${threadId.value}`);
            }
            if (novelId?.value) {
                window.Echo.leave(`chat.novel.${novelId.value}`);
            }
        }
        threadChannel = null;
        novelChannel = null;
        isConnected.value = false;
    };

    // Watch thread ID changes
    watch(
        () => threadId.value,
        (newId, oldId) => {
            if (!enabled) return;

            // Leave old thread channel
            if (oldId && window.Echo) {
                window.Echo.leave(`chat.thread.${oldId}`);
            }

            // Subscribe to new thread channel
            if (newId) {
                subscribeToThread(newId);
            } else {
                threadChannel = null;
                isConnected.value = false;
            }
        },
        { immediate: true }
    );

    // Watch novel ID changes (for thread list updates)
    watch(
        () => novelId?.value,
        (newId, oldId) => {
            if (!enabled) return;

            // Leave old novel channel
            if (oldId && window.Echo) {
                window.Echo.leave(`chat.novel.${oldId}`);
            }

            // Subscribe to new novel channel
            if (newId) {
                subscribeToNovel(newId);
            } else {
                novelChannel = null;
            }
        },
        { immediate: true }
    );

    // Cleanup on unmount
    onUnmounted(() => {
        unsubscribe();
    });

    return {
        isConnected,
        connectionError,
        unsubscribe,
    };
}
