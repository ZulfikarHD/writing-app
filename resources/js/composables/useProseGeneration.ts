import { ref, reactive } from 'vue';
import axios from 'axios';
import * as ProseGenerationActions from '@/actions/App/Http/Controllers/ProseGenerationController';

export interface ProseGenerationOptions {
    mode?: 'scene_beat' | 'continue' | 'custom';
    beat?: string;
    instructions?: string;
    content_before?: string;
    prompt_id?: number;
    connection_id?: number;
    model?: string;
    temperature?: number;
    max_tokens?: number;
}

export interface ProsePrompt {
    id: number;
    name: string;
    description: string | null;
    is_system: boolean;
}

export interface AIConnection {
    id: number;
    name: string;
    provider: string;
    is_default: boolean;
}

export interface ProseMode {
    name: string;
    description: string;
}

export function useProseGeneration() {
    // State
    const isGenerating = ref(false);
    const generatedContent = ref('');
    const error = ref<string | null>(null);
    const tokensUsed = reactive({ input: 0, output: 0 });
    
    // Options from server
    const availablePrompts = ref<ProsePrompt[]>([]);
    const availableConnections = ref<AIConnection[]>([]);
    const availableModes = ref<Record<string, ProseMode>>({});
    
    // SSE controller for aborting
    let abortController: AbortController | null = null;

    /**
     * Fetch available generation options from server.
     */
    async function fetchOptions() {
        try {
            const response = await axios.get(ProseGenerationActions.options.url());
            
            availablePrompts.value = response.data.prompts || [];
            availableConnections.value = response.data.connections || [];
            availableModes.value = response.data.modes || {};
        } catch (err) {
            console.error('Failed to fetch prose generation options:', err);
        }
    }

    /**
     * Generate prose for a scene with streaming.
     */
    async function generate(sceneId: number, options: ProseGenerationOptions = {}) {
        if (isGenerating.value) return;

        isGenerating.value = true;
        generatedContent.value = '';
        error.value = null;
        tokensUsed.input = 0;
        tokensUsed.output = 0;

        abortController = new AbortController();

        try {
            const url = ProseGenerationActions.generate.url({ scene: sceneId });
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream',
                    'X-XSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify(options),
                signal: abortController.signal,
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const reader = response.body?.getReader();
            if (!reader) {
                throw new Error('No response body');
            }

            const decoder = new TextDecoder();
            let buffer = '';

            while (true) {
                const { done, value } = await reader.read();
                
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                
                // Process SSE events from buffer
                const lines = buffer.split('\n');
                buffer = lines.pop() || ''; // Keep incomplete line in buffer

                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        const eventData = line.slice(6);
                        
                        try {
                            const data = JSON.parse(eventData);
                            
                            if (data.type === 'content') {
                                generatedContent.value += data.content;
                            } else if (data.type === 'error') {
                                error.value = data.error;
                                break;
                            } else if (data.type === 'done') {
                                if (data.tokens_input) tokensUsed.input = data.tokens_input;
                                if (data.tokens_output) tokensUsed.output = data.tokens_output;
                            }
                        } catch {
                            // Ignore JSON parse errors for incomplete data
                        }
                    }
                }
            }
        } catch (err) {
            if (err instanceof Error && err.name === 'AbortError') {
                // Generation was aborted by user
                return;
            }
            
            error.value = err instanceof Error ? err.message : 'Failed to generate prose';
            console.error('Prose generation error:', err);
        } finally {
            isGenerating.value = false;
            abortController = null;
        }
    }

    /**
     * Abort ongoing generation.
     */
    function abort() {
        if (abortController) {
            abortController.abort();
            abortController = null;
        }
        isGenerating.value = false;
    }

    /**
     * Reset state.
     */
    function reset() {
        abort();
        generatedContent.value = '';
        error.value = null;
        tokensUsed.input = 0;
        tokensUsed.output = 0;
    }

    /**
     * Get CSRF token from cookie.
     */
    function getCsrfToken(): string {
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : '';
    }

    return {
        // State
        isGenerating,
        generatedContent,
        error,
        tokensUsed,
        
        // Options
        availablePrompts,
        availableConnections,
        availableModes,
        
        // Methods
        generate,
        abort,
        reset,
        fetchOptions,
    };
}

export default useProseGeneration;
