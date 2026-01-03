import { ref, computed } from 'vue';
import type { Prompt } from '@/composables/usePrompts';

export interface PromptImportPreview {
    name: string;
    description: string | null;
    type: string;
    has_system_message: boolean;
    has_user_message: boolean;
    has_messages: boolean;
    has_model_settings: boolean;
    input_count: number;
    version: string;
    app: string;
    exported_at: string | null;
}

/**
 * Composable for prompt sharing (export/import).
 */
export function usePromptSharing() {
    const isExporting = ref(false);
    const isImporting = ref(false);
    const isPreviewing = ref(false);
    const error = ref<string | null>(null);

    const csrfToken = computed(() => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    });

    /**
     * Export a prompt to clipboard.
     */
    async function exportToClipboard(promptId: number): Promise<boolean> {
        isExporting.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompts/${promptId}/export`, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.error || 'Failed to export prompt');
            }

            const data = await response.json();
            await navigator.clipboard.writeText(data.encoded);
            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to export prompt';
            return false;
        } finally {
            isExporting.value = false;
        }
    }

    /**
     * Preview an imported prompt from clipboard.
     */
    async function previewFromClipboard(): Promise<PromptImportPreview | null> {
        isPreviewing.value = true;
        error.value = null;

        try {
            const encoded = await navigator.clipboard.readText();
            
            if (!encoded || encoded.trim().length === 0) {
                throw new Error('Clipboard is empty. Please copy a prompt first.');
            }

            const response = await fetch('/api/prompts/import/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify({ encoded }),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.error || 'Failed to preview prompt');
            }

            const data = await response.json();
            return data.preview;
        } catch (err) {
            if (err instanceof Error && err.name === 'NotAllowedError') {
                error.value = 'Clipboard access denied. Please allow clipboard access and try again.';
            } else {
                error.value = err instanceof Error ? err.message : 'Failed to preview prompt';
            }
            return null;
        } finally {
            isPreviewing.value = false;
        }
    }

    /**
     * Preview an imported prompt from an encoded string.
     */
    async function previewFromString(encoded: string): Promise<PromptImportPreview | null> {
        isPreviewing.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/prompts/import/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify({ encoded }),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.error || 'Failed to preview prompt');
            }

            const data = await response.json();
            return data.preview;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to preview prompt';
            return null;
        } finally {
            isPreviewing.value = false;
        }
    }

    /**
     * Import a prompt from clipboard.
     */
    async function importFromClipboard(): Promise<Prompt | null> {
        isImporting.value = true;
        error.value = null;

        try {
            const encoded = await navigator.clipboard.readText();
            
            if (!encoded || encoded.trim().length === 0) {
                throw new Error('Clipboard is empty. Please copy a prompt first.');
            }

            const response = await fetch('/api/prompts/import', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify({ encoded }),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.error || 'Failed to import prompt');
            }

            const data = await response.json();
            return data.prompt;
        } catch (err) {
            if (err instanceof Error && err.name === 'NotAllowedError') {
                error.value = 'Clipboard access denied. Please allow clipboard access and try again.';
            } else {
                error.value = err instanceof Error ? err.message : 'Failed to import prompt';
            }
            return null;
        } finally {
            isImporting.value = false;
        }
    }

    /**
     * Import a prompt from an encoded string.
     */
    async function importFromString(encoded: string): Promise<Prompt | null> {
        isImporting.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/prompts/import', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify({ encoded }),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.error || 'Failed to import prompt');
            }

            const data = await response.json();
            return data.prompt;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to import prompt';
            return null;
        } finally {
            isImporting.value = false;
        }
    }

    /**
     * Clear any error.
     */
    function clearError() {
        error.value = null;
    }

    return {
        // State
        isExporting,
        isImporting,
        isPreviewing,
        error,

        // Actions
        exportToClipboard,
        previewFromClipboard,
        previewFromString,
        importFromClipboard,
        importFromString,
        clearError,
    };
}
