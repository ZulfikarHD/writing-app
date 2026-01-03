import { ref, reactive } from 'vue';
import axios from 'axios';
import * as TextReplacementActions from '@/actions/App/Http/Controllers/TextReplacementController';

export interface TextReplacementOptions {
    type: 'expand' | 'rephrase' | 'shorten' | 'custom';
    scene_id?: number;
    prompt_id?: number;
    connection_id?: number;
    model?: string;
    
    // Expand options
    expand_amount?: 'slightly' | 'double' | 'triple';
    expand_method?: 'sensory_details' | 'inner_thoughts' | 'description' | 'dialogue';
    
    // Rephrase options
    rephrase_options?: string[];
    target_pov?: string;
    target_tense?: string;
    
    // Shorten options
    shorten_amount?: 'half' | 'quarter' | 'single_paragraph';
    
    // Custom options
    instructions?: string;
    
    // Model settings
    temperature?: number;
    max_tokens?: number;
}

export interface ReplacementPrompt {
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

export function useTextReplacement() {
    // State
    const isTransforming = ref(false);
    const originalText = ref('');
    const transformedText = ref('');
    const error = ref<string | null>(null);
    const tokensUsed = reactive({ input: 0, output: 0 });
    
    // Options from server
    const availableTypes = ref<Record<string, string>>({});
    const expandAmounts = ref<Record<string, string>>({});
    const shortenAmounts = ref<Record<string, string>>({});
    const rephraseOptions = ref<Record<string, string>>({});
    const availablePrompts = ref<ReplacementPrompt[]>([]);
    const availableConnections = ref<AIConnection[]>([]);
    
    // SSE controller for aborting
    let abortController: AbortController | null = null;

    /**
     * Fetch available replacement options from server.
     */
    async function fetchOptions() {
        try {
            const response = await axios.get(TextReplacementActions.options.url());
            
            availableTypes.value = response.data.types || {};
            expandAmounts.value = response.data.expand_amounts || {};
            shortenAmounts.value = response.data.shorten_amounts || {};
            rephraseOptions.value = response.data.rephrase_options || {};
            availablePrompts.value = response.data.prompts || [];
            availableConnections.value = response.data.connections || [];
        } catch (err) {
            console.error('Failed to fetch text replacement options:', err);
        }
    }

    /**
     * Transform selected text with streaming.
     */
    async function transform(selectedText: string, options: TextReplacementOptions) {
        if (isTransforming.value) return;

        // Validate minimum word count
        const wordCount = selectedText.trim().split(/\s+/).filter(Boolean).length;
        if (wordCount < 4) {
            error.value = 'Please select at least 4 words to use text replacement.';
            return;
        }

        isTransforming.value = true;
        originalText.value = selectedText;
        transformedText.value = '';
        error.value = null;
        tokensUsed.input = 0;
        tokensUsed.output = 0;

        abortController = new AbortController();

        try {
            const url = TextReplacementActions.replace.url();
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream',
                    'X-XSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    selected_text: selectedText,
                    ...options,
                }),
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
                                transformedText.value += data.content;
                            } else if (data.type === 'error') {
                                error.value = data.error;
                                break;
                            } else if (data.type === 'start') {
                                originalText.value = data.original || selectedText;
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
                // Transformation was aborted by user
                return;
            }
            
            error.value = err instanceof Error ? err.message : 'Failed to transform text';
            console.error('Text replacement error:', err);
        } finally {
            isTransforming.value = false;
            abortController = null;
        }
    }

    /**
     * Quick expand transformation.
     */
    async function expand(
        selectedText: string, 
        amount: 'slightly' | 'double' | 'triple' = 'double',
        method?: 'sensory_details' | 'inner_thoughts' | 'description' | 'dialogue',
        sceneId?: number
    ) {
        return transform(selectedText, {
            type: 'expand',
            expand_amount: amount,
            expand_method: method,
            scene_id: sceneId,
        });
    }

    /**
     * Quick rephrase transformation.
     */
    async function rephrase(
        selectedText: string, 
        options: string[] = [],
        sceneId?: number
    ) {
        return transform(selectedText, {
            type: 'rephrase',
            rephrase_options: options,
            scene_id: sceneId,
        });
    }

    /**
     * Quick shorten transformation.
     */
    async function shorten(
        selectedText: string, 
        amount: 'half' | 'quarter' | 'single_paragraph' = 'half',
        sceneId?: number
    ) {
        return transform(selectedText, {
            type: 'shorten',
            shorten_amount: amount,
            scene_id: sceneId,
        });
    }

    /**
     * Abort ongoing transformation.
     */
    function abort() {
        if (abortController) {
            abortController.abort();
            abortController = null;
        }
        isTransforming.value = false;
    }

    /**
     * Reset state.
     */
    function reset() {
        abort();
        originalText.value = '';
        transformedText.value = '';
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
        isTransforming,
        originalText,
        transformedText,
        error,
        tokensUsed,
        
        // Options
        availableTypes,
        expandAmounts,
        shortenAmounts,
        rephraseOptions,
        availablePrompts,
        availableConnections,
        
        // Methods
        transform,
        expand,
        rephrase,
        shorten,
        abort,
        reset,
        fetchOptions,
    };
}

export default useTextReplacement;
