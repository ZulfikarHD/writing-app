import { ref, computed } from 'vue';

export interface ContextItem {
    id: number;
    thread_id: number;
    context_type: 'scene' | 'codex' | 'summary' | 'outline' | 'custom';
    reference_id: number | null;
    is_active: boolean;
    tokens: number;
    preview: string;
    created_at: string;
    reference?: {
        id: number;
        title?: string;
        name?: string;
        type?: string;
        word_count?: number;
    };
    custom_content?: string;
}

export interface ContextTokenInfo {
    total: number;
    base_tokens: number;
    items: Array<{
        id: number;
        type: string;
        name: string;
        tokens: number;
    }>;
}

export interface ContextLimitInfo {
    within_limit: boolean;
    usage_percentage: number;
    tokens_used: number;
    limit: number;
    model_limit: number;
}

export interface ContextSource {
    id: number;
    title?: string;
    name?: string;
    type?: string;
    word_count?: number;
    tokens: number;
}

export interface ContextSources {
    chapters: Array<{
        id: number;
        title: string;
        scenes: ContextSource[];
    }>;
    codex: Array<{
        type: string;
        entries: ContextSource[];
    }>;
    has_summary: boolean;
    has_outline: boolean;
}

/**
 * Composable for managing chat context items.
 */
export function useChatContext(novelId: number) {
    const contextItems = ref<ContextItem[]>([]);
    const tokenInfo = ref<ContextTokenInfo | null>(null);
    const limitInfo = ref<ContextLimitInfo | null>(null);
    const sources = ref<ContextSources | null>(null);
    const isLoading = ref(false);
    const isLoadingSources = ref(false);
    const error = ref<string | null>(null);

    const csrfToken = computed(() => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    });

    /**
     * Active context items only.
     */
    const activeItems = computed(() => {
        return contextItems.value.filter((item) => item.is_active);
    });

    /**
     * Total tokens from active context.
     */
    const totalTokens = computed(() => {
        return tokenInfo.value?.total ?? 0;
    });

    /**
     * Check if context is near limit (>80%).
     */
    const isNearLimit = computed(() => {
        return (limitInfo.value?.usage_percentage ?? 0) > 80;
    });

    /**
     * Check if context exceeds limit.
     */
    const isOverLimit = computed(() => {
        return !(limitInfo.value?.within_limit ?? true);
    });

    /**
     * Fetch context items for a thread.
     */
    const fetchContext = async (threadId: number): Promise<void> => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/chat/threads/${threadId}/context`);
            if (!response.ok) throw new Error('Failed to fetch context');

            const data = await response.json();
            contextItems.value = data.items || [];
            tokenInfo.value = data.tokens || null;
            limitInfo.value = data.limit || null;
        } catch (e) {
            error.value = 'Failed to load context';
            console.error('Failed to fetch context:', e);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Add a context item to a thread.
     */
    const addContext = async (
        threadId: number,
        contextType: ContextItem['context_type'],
        referenceId?: number,
        customContent?: string
    ): Promise<ContextItem | null> => {
        error.value = null;

        try {
            const response = await fetch(`/api/chat/threads/${threadId}/context`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify({
                    context_type: contextType,
                    reference_id: referenceId,
                    custom_content: customContent,
                }),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to add context');
            }

            const data = await response.json();

            // Update local state
            const existingIndex = contextItems.value.findIndex((item) => item.id === data.item.id);
            if (existingIndex >= 0) {
                contextItems.value[existingIndex] = data.item;
            } else {
                contextItems.value = [data.item, ...contextItems.value];
            }

            tokenInfo.value = data.tokens;
            limitInfo.value = data.limit;

            return data.item;
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Failed to add context';
            console.error('Failed to add context:', e);
            return null;
        }
    };

    /**
     * Add multiple context items at once.
     */
    const addBulkContext = async (
        threadId: number,
        items: Array<{
            context_type: ContextItem['context_type'];
            reference_id?: number;
            custom_content?: string;
        }>
    ): Promise<ContextItem[]> => {
        error.value = null;

        try {
            const response = await fetch(`/api/chat/threads/${threadId}/context/bulk`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify({ items }),
            });

            if (!response.ok) throw new Error('Failed to add context items');

            const data = await response.json();

            // Merge with existing items
            for (const newItem of data.items) {
                const existingIndex = contextItems.value.findIndex((item) => item.id === newItem.id);
                if (existingIndex >= 0) {
                    contextItems.value[existingIndex] = newItem;
                } else {
                    contextItems.value = [newItem, ...contextItems.value];
                }
            }

            tokenInfo.value = data.tokens;
            limitInfo.value = data.limit;

            return data.items;
        } catch (e) {
            error.value = 'Failed to add context items';
            console.error('Failed to add bulk context:', e);
            return [];
        }
    };

    /**
     * Toggle a context item's active state.
     */
    const toggleContext = async (itemId: number): Promise<void> => {
        const item = contextItems.value.find((i) => i.id === itemId);
        if (!item) return;

        error.value = null;

        try {
            const response = await fetch(`/api/chat/context/${itemId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify({
                    is_active: !item.is_active,
                }),
            });

            if (!response.ok) throw new Error('Failed to update context');

            const data = await response.json();

            // Update local state
            const index = contextItems.value.findIndex((i) => i.id === itemId);
            if (index >= 0) {
                contextItems.value[index] = data.item;
            }

            tokenInfo.value = data.tokens;
            limitInfo.value = data.limit;
        } catch (e) {
            error.value = 'Failed to toggle context';
            console.error('Failed to toggle context:', e);
        }
    };

    /**
     * Remove a context item.
     */
    const removeContext = async (itemId: number): Promise<void> => {
        error.value = null;

        try {
            const response = await fetch(`/api/chat/context/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) throw new Error('Failed to remove context');

            const data = await response.json();

            // Remove from local state
            contextItems.value = contextItems.value.filter((i) => i.id !== itemId);
            tokenInfo.value = data.tokens;
            limitInfo.value = data.limit;
        } catch (e) {
            error.value = 'Failed to remove context';
            console.error('Failed to remove context:', e);
        }
    };

    /**
     * Clear all context items from a thread.
     */
    const clearContext = async (threadId: number): Promise<void> => {
        error.value = null;

        try {
            const response = await fetch(`/api/chat/threads/${threadId}/context/clear`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) throw new Error('Failed to clear context');

            const data = await response.json();

            contextItems.value = [];
            tokenInfo.value = data.tokens;
            limitInfo.value = data.limit;
        } catch (e) {
            error.value = 'Failed to clear context';
            console.error('Failed to clear context:', e);
        }
    };

    /**
     * Fetch available context sources for the novel.
     */
    const fetchSources = async (): Promise<void> => {
        isLoadingSources.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/novels/${novelId}/context-sources`);
            if (!response.ok) throw new Error('Failed to fetch sources');

            const data = await response.json();
            sources.value = data;
        } catch (e) {
            error.value = 'Failed to load context sources';
            console.error('Failed to fetch sources:', e);
        } finally {
            isLoadingSources.value = false;
        }
    };

    /**
     * Get context preview for a thread.
     */
    const getPreview = async (threadId: number): Promise<{ text: string; tokens: number; items: unknown[] } | null> => {
        try {
            const response = await fetch(`/api/chat/threads/${threadId}/context/preview`);
            if (!response.ok) throw new Error('Failed to get preview');

            const data = await response.json();
            return data.preview;
        } catch (e) {
            console.error('Failed to get context preview:', e);
            return null;
        }
    };

    /**
     * Check if a source is already added as context.
     */
    const isSourceAdded = (contextType: ContextItem['context_type'], referenceId: number): boolean => {
        return contextItems.value.some(
            (item) => item.context_type === contextType && item.reference_id === referenceId
        );
    };

    /**
     * Get display name for a context item.
     */
    const getItemName = (item: ContextItem): string => {
        switch (item.context_type) {
            case 'scene':
                return item.reference?.title ?? 'Untitled Scene';
            case 'codex':
                return item.reference?.name ?? 'Unknown Entry';
            case 'summary':
                return 'Novel Summary';
            case 'outline':
                return 'Story Outline';
            case 'custom':
                return 'Custom Context';
            default:
                return 'Context Item';
        }
    };

    /**
     * Get icon name for context type.
     */
    const getTypeIcon = (contextType: ContextItem['context_type']): string => {
        switch (contextType) {
            case 'scene':
                return 'document-text';
            case 'codex':
                return 'book-open';
            case 'summary':
                return 'clipboard-document-list';
            case 'outline':
                return 'list-bullet';
            case 'custom':
                return 'pencil-square';
            default:
                return 'document';
        }
    };

    /**
     * Reset context state.
     */
    const resetContext = () => {
        contextItems.value = [];
        tokenInfo.value = null;
        limitInfo.value = null;
        error.value = null;
    };

    return {
        // State
        contextItems,
        tokenInfo,
        limitInfo,
        sources,
        isLoading,
        isLoadingSources,
        error,

        // Computed
        activeItems,
        totalTokens,
        isNearLimit,
        isOverLimit,

        // Actions
        fetchContext,
        addContext,
        addBulkContext,
        toggleContext,
        removeContext,
        clearContext,
        fetchSources,
        getPreview,
        isSourceAdded,
        getItemName,
        getTypeIcon,
        resetContext,
    };
}
