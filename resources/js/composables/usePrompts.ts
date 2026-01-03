import { ref, computed } from 'vue';

export interface PromptMessage {
    id: string;
    role: 'user' | 'assistant';
    content: string;
}

export interface PromptInput {
    id: number;
    prompt_id: number;
    name: string;
    label: string;
    type: 'text' | 'textarea' | 'select' | 'number' | 'checkbox';
    options?: { value: string; label: string }[];
    default_value?: string | null;
    placeholder?: string | null;
    description?: string | null;
    is_required: boolean;
    sort_order: number;
}

export interface PromptComponent {
    id: number;
    user_id: number;
    name: string;
    label: string;
    content: string;
    description?: string | null;
    is_system: boolean;
    created_at?: string | null;
    updated_at?: string | null;
}

export interface Prompt {
    id: number;
    user_id: number | null;
    folder_id: number | null;
    name: string;
    description: string | null;
    type: 'chat' | 'prose' | 'replacement' | 'summary';
    type_label: string;
    system_message: string | null;
    user_message: string | null;
    messages: PromptMessage[] | null;
    model_settings: ModelSettings | null;
    is_system: boolean;
    is_active: boolean;
    sort_order: number;
    usage_count: number;
    inputs?: PromptInput[];
    created_at: string | null;
    updated_at: string | null;
}

export interface ModelSettings {
    temperature?: number;
    max_tokens?: number;
    top_p?: number;
    frequency_penalty?: number;
    presence_penalty?: number;
}

export interface PromptFormData {
    name: string;
    description?: string | null;
    type: 'chat' | 'prose' | 'replacement' | 'summary';
    system_message?: string | null;
    user_message?: string | null;
    messages?: PromptMessage[] | null;
    model_settings?: ModelSettings | null;
    folder_id?: number | null;
    sort_order?: number;
    is_active?: boolean;
}

export interface PromptTypeInfo {
    chat: string;
    prose: string;
    replacement: string;
    summary: string;
}

/**
 * Composable for managing prompts.
 */
export function usePrompts() {
    const prompts = ref<Prompt[]>([]);
    const isLoading = ref(false);
    const error = ref<string | null>(null);
    const types = ref<PromptTypeInfo>({
        chat: 'Workshop Chat',
        prose: 'Scene Beat Completion',
        replacement: 'Text Replacement',
        summary: 'Scene Summarization',
    });

    const csrfToken = computed(() => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    });

    /**
     * Filter prompts by type.
     */
    const promptsByType = computed(() => {
        const grouped: Record<string, Prompt[]> = {
            chat: [],
            prose: [],
            replacement: [],
            summary: [],
        };

        for (const prompt of prompts.value) {
            if (grouped[prompt.type]) {
                grouped[prompt.type].push(prompt);
            }
        }

        return grouped;
    });

    /**
     * System prompts only.
     */
    const systemPrompts = computed(() => {
        return prompts.value.filter((p) => p.is_system);
    });

    /**
     * User prompts only.
     */
    const userPrompts = computed(() => {
        return prompts.value.filter((p) => !p.is_system);
    });

    /**
     * Fetch all accessible prompts.
     */
    async function fetchPrompts(type?: string, search?: string): Promise<void> {
        isLoading.value = true;
        error.value = null;

        try {
            const params = new URLSearchParams();
            if (type) params.append('type', type);
            if (search) params.append('search', search);

            const url = `/api/prompts${params.toString() ? `?${params}` : ''}`;
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch prompts');
            }

            const data = await response.json();
            prompts.value = data.prompts;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch prompts';
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Fetch prompts by type (for dropdowns).
     */
    async function fetchPromptsByType(type: string): Promise<Prompt[]> {
        try {
            const response = await fetch(`/api/prompts/type/${type}`, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch prompts');
            }

            const data = await response.json();
            return data.prompts;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch prompts';
            return [];
        }
    }

    /**
     * Get a single prompt.
     */
    async function getPrompt(id: number): Promise<Prompt | null> {
        try {
            const response = await fetch(`/api/prompts/${id}`, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch prompt');
            }

            const data = await response.json();
            return data.prompt;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch prompt';
            return null;
        }
    }

    /**
     * Create a new prompt.
     */
    async function createPrompt(data: PromptFormData): Promise<Prompt | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/prompts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to create prompt');
            }

            const result = await response.json();
            prompts.value.push(result.prompt);
            return result.prompt;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to create prompt';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Update an existing prompt.
     */
    async function updatePrompt(id: number, data: Partial<PromptFormData>): Promise<Prompt | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompts/${id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to update prompt');
            }

            const result = await response.json();

            // Update local state
            const index = prompts.value.findIndex((p) => p.id === id);
            if (index !== -1) {
                prompts.value[index] = result.prompt;
            }

            return result.prompt;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to update prompt';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Delete a prompt.
     */
    async function deletePrompt(id: number): Promise<boolean> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompts/${id}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to delete prompt');
            }

            // Remove from local state
            prompts.value = prompts.value.filter((p) => p.id !== id);
            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to delete prompt';
            return false;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Clone a prompt.
     */
    async function clonePrompt(id: number, newName?: string): Promise<Prompt | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompts/${id}/clone`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify({ name: newName }),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to clone prompt');
            }

            const result = await response.json();
            prompts.value.push(result.prompt);
            return result.prompt;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to clone prompt';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Record prompt usage.
     */
    async function recordUsage(id: number): Promise<void> {
        try {
            await fetch(`/api/prompts/${id}/usage`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            // Update local state
            const prompt = prompts.value.find((p) => p.id === id);
            if (prompt) {
                prompt.usage_count++;
            }
        } catch {
            // Silent fail - usage tracking is not critical
        }
    }

    /**
     * Get type label for a prompt type.
     */
    function getTypeLabel(type: string): string {
        return types.value[type as keyof PromptTypeInfo] || type;
    }

    return {
        // State
        prompts,
        isLoading,
        error,
        types,

        // Computed
        promptsByType,
        systemPrompts,
        userPrompts,

        // Actions
        fetchPrompts,
        fetchPromptsByType,
        getPrompt,
        createPrompt,
        updatePrompt,
        deletePrompt,
        clonePrompt,
        recordUsage,
        getTypeLabel,
    };
}
