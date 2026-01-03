import { ref, computed } from 'vue';

export interface Preset {
    id: number;
    user_id: number;
    prompt_id: number;
    name: string;
    model: string | null;
    temperature: number;
    max_tokens: number | null;
    top_p: number | null;
    frequency_penalty: number | null;
    presence_penalty: number | null;
    stop_sequences: string[] | null;
    input_values: Record<string, unknown> | null;
    is_default: boolean;
    prompt: {
        id: number;
        name: string;
        type: string;
    } | null;
    created_at: string | null;
    updated_at: string | null;
}

export interface PresetFormData {
    name: string;
    model?: string | null;
    temperature?: number;
    max_tokens?: number | null;
    top_p?: number | null;
    frequency_penalty?: number | null;
    presence_penalty?: number | null;
    stop_sequences?: string[] | null;
    input_values?: Record<string, unknown> | null;
    is_default?: boolean;
}

export interface ModelSettings {
    model?: string;
    temperature?: number;
    max_tokens?: number;
    top_p?: number;
    frequency_penalty?: number;
    presence_penalty?: number;
    stop?: string[];
}

/**
 * Composable for managing prompt presets.
 */
export function usePresets() {
    const presets = ref<Preset[]>([]);
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    const csrfToken = computed(() => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    });

    /**
     * Default presets only.
     */
    const defaultPresets = computed(() => {
        return presets.value.filter((p) => p.is_default);
    });

    /**
     * Group presets by prompt ID.
     */
    const presetsByPrompt = computed(() => {
        const grouped: Record<number, Preset[]> = {};

        for (const preset of presets.value) {
            if (!grouped[preset.prompt_id]) {
                grouped[preset.prompt_id] = [];
            }
            grouped[preset.prompt_id].push(preset);
        }

        return grouped;
    });

    /**
     * Fetch all presets for the current user.
     */
    async function fetchPresets(): Promise<void> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/prompt-presets', {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch presets');
            }

            const data = await response.json();
            presets.value = data.presets;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch presets';
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Fetch presets for a specific prompt.
     */
    async function fetchPresetsForPrompt(promptId: number): Promise<Preset[]> {
        try {
            const response = await fetch(`/api/prompts/${promptId}/presets`, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch presets');
            }

            const data = await response.json();
            return data.presets;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch presets';
            return [];
        }
    }

    /**
     * Get a single preset.
     */
    async function getPreset(id: number): Promise<Preset | null> {
        try {
            const response = await fetch(`/api/prompt-presets/${id}`, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch preset');
            }

            const data = await response.json();
            return data.preset;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch preset';
            return null;
        }
    }

    /**
     * Create a new preset for a prompt.
     */
    async function createPreset(promptId: number, data: PresetFormData): Promise<Preset | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompts/${promptId}/presets`, {
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
                throw new Error(errorData.message || 'Failed to create preset');
            }

            const result = await response.json();
            presets.value.push(result.preset);
            return result.preset;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to create preset';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Update an existing preset.
     */
    async function updatePreset(id: number, data: Partial<PresetFormData>): Promise<Preset | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompt-presets/${id}`, {
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
                throw new Error(errorData.message || 'Failed to update preset');
            }

            const result = await response.json();

            // Update local state
            const index = presets.value.findIndex((p) => p.id === id);
            if (index !== -1) {
                presets.value[index] = result.preset;
            }

            return result.preset;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to update preset';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Set a preset as default for its prompt.
     */
    async function setPresetAsDefault(id: number): Promise<Preset | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompt-presets/${id}/set-default`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to set preset as default');
            }

            const result = await response.json();
            const preset = result.preset;

            // Update local state: unset other defaults for the same prompt
            for (const p of presets.value) {
                if (p.prompt_id === preset.prompt_id) {
                    p.is_default = p.id === preset.id;
                }
            }

            return preset;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to set preset as default';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Delete a preset.
     */
    async function deletePreset(id: number): Promise<boolean> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompt-presets/${id}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to delete preset');
            }

            // Remove from local state
            presets.value = presets.value.filter((p) => p.id !== id);
            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to delete preset';
            return false;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Get presets for a specific prompt from local state.
     */
    function getPresetsForPrompt(promptId: number): Preset[] {
        return presets.value.filter((p) => p.prompt_id === promptId);
    }

    /**
     * Get default preset for a prompt from local state.
     */
    function getDefaultPresetForPrompt(promptId: number): Preset | undefined {
        return presets.value.find((p) => p.prompt_id === promptId && p.is_default);
    }

    /**
     * Convert preset to model settings format for AI requests.
     */
    function presetToModelSettings(preset: Preset): ModelSettings {
        const settings: ModelSettings = {};

        if (preset.model) {
            settings.model = preset.model;
        }

        if (preset.temperature !== null) {
            settings.temperature = preset.temperature;
        }

        if (preset.max_tokens !== null) {
            settings.max_tokens = preset.max_tokens;
        }

        if (preset.top_p !== null) {
            settings.top_p = preset.top_p;
        }

        if (preset.frequency_penalty !== null) {
            settings.frequency_penalty = preset.frequency_penalty;
        }

        if (preset.presence_penalty !== null) {
            settings.presence_penalty = preset.presence_penalty;
        }

        if (preset.stop_sequences && preset.stop_sequences.length > 0) {
            settings.stop = preset.stop_sequences;
        }

        return settings;
    }

    return {
        // State
        presets,
        isLoading,
        error,

        // Computed
        defaultPresets,
        presetsByPrompt,

        // Actions
        fetchPresets,
        fetchPresetsForPrompt,
        getPreset,
        createPreset,
        updatePreset,
        setPresetAsDefault,
        deletePreset,
        getPresetsForPrompt,
        getDefaultPresetForPrompt,
        presetToModelSettings,
    };
}
