import { ref, computed } from 'vue';
import type { Prompt, PromptInput } from './usePrompts';

export interface InputValues {
    [key: string]: string | number | boolean;
}

export interface PromptExecutionResult {
    promptId: number;
    inputValues: InputValues;
    resolvedSystemMessage: string | null;
    resolvedUserMessage: string | null;
}

/**
 * Composable for handling prompt execution with input forms.
 * 
 * This composable manages the flow of:
 * 1. Checking if a prompt has inputs
 * 2. Showing an input form if needed
 * 3. Resolving input values into the prompt messages
 */
export function usePromptExecution() {
    // State for the input form modal
    const showInputForm = ref(false);
    const pendingPrompt = ref<Prompt | null>(null);
    const pendingCallback = ref<((result: PromptExecutionResult | null) => void) | null>(null);

    /**
     * Check if a prompt has any inputs defined.
     */
    function hasInputs(prompt: Prompt): boolean {
        return Array.isArray(prompt.inputs) && prompt.inputs.length > 0;
    }

    /**
     * Get inputs sorted by sort_order.
     */
    function getSortedInputs(prompt: Prompt): PromptInput[] {
        if (!prompt.inputs) return [];
        return [...prompt.inputs].sort((a, b) => a.sort_order - b.sort_order);
    }

    /**
     * Resolve {input("name")} placeholders in text with actual values.
     */
    function resolveInputsInText(text: string, inputValues: InputValues): string {
        if (!text) return text;

        // Match {input("name")} or {input('name')} patterns
        return text.replace(/\{input\(["']([^"']+)["']\)\}/g, (match, inputName) => {
            const value = inputValues[inputName];
            if (value === undefined || value === null) {
                return match; // Keep original if not found
            }
            return String(value);
        });
    }

    /**
     * Build default input values from prompt inputs.
     */
    function buildDefaultInputValues(prompt: Prompt): InputValues {
        const values: InputValues = {};

        if (!prompt.inputs) return values;

        for (const input of prompt.inputs) {
            if (input.default_value !== undefined && input.default_value !== null) {
                if (input.type === 'number') {
                    values[input.name] = parseFloat(input.default_value) || 0;
                } else if (input.type === 'checkbox') {
                    values[input.name] = input.default_value === 'true' || input.default_value === '1';
                } else {
                    values[input.name] = input.default_value;
                }
            } else {
                // Set type-appropriate defaults
                if (input.type === 'number') {
                    values[input.name] = 0;
                } else if (input.type === 'checkbox') {
                    values[input.name] = false;
                } else {
                    values[input.name] = '';
                }
            }
        }

        return values;
    }

    /**
     * Execute a prompt, showing input form if needed.
     * 
     * Returns a promise that resolves with the execution result
     * or null if cancelled.
     */
    function executePrompt(prompt: Prompt): Promise<PromptExecutionResult | null> {
        return new Promise((resolve) => {
            if (!hasInputs(prompt)) {
                // No inputs, resolve immediately
                resolve({
                    promptId: prompt.id,
                    inputValues: {},
                    resolvedSystemMessage: prompt.system_message,
                    resolvedUserMessage: prompt.user_message,
                });
                return;
            }

            // Has inputs, show the form
            pendingPrompt.value = prompt;
            pendingCallback.value = resolve;
            showInputForm.value = true;
        });
    }

    /**
     * Handle input form submission.
     */
    function handleInputSubmit(values: InputValues) {
        if (pendingPrompt.value && pendingCallback.value) {
            const result: PromptExecutionResult = {
                promptId: pendingPrompt.value.id,
                inputValues: values,
                resolvedSystemMessage: resolveInputsInText(
                    pendingPrompt.value.system_message || '',
                    values
                ),
                resolvedUserMessage: resolveInputsInText(
                    pendingPrompt.value.user_message || '',
                    values
                ),
            };
            pendingCallback.value(result);
        }

        // Reset state
        showInputForm.value = false;
        pendingPrompt.value = null;
        pendingCallback.value = null;
    }

    /**
     * Handle input form close/cancel.
     */
    function handleInputClose() {
        if (pendingCallback.value) {
            pendingCallback.value(null);
        }

        // Reset state
        showInputForm.value = false;
        pendingPrompt.value = null;
        pendingCallback.value = null;
    }

    /**
     * Get current inputs for the pending prompt.
     */
    const currentInputs = computed(() => {
        if (!pendingPrompt.value) return [];
        return getSortedInputs(pendingPrompt.value);
    });

    /**
     * Get current prompt name.
     */
    const currentPromptName = computed(() => {
        return pendingPrompt.value?.name || '';
    });

    /**
     * Get default values for the current prompt.
     */
    const currentDefaultValues = computed(() => {
        if (!pendingPrompt.value) return {};
        return buildDefaultInputValues(pendingPrompt.value);
    });

    return {
        // State
        showInputForm,
        currentInputs,
        currentPromptName,
        currentDefaultValues,

        // Methods
        hasInputs,
        getSortedInputs,
        resolveInputsInText,
        buildDefaultInputValues,
        executePrompt,
        handleInputSubmit,
        handleInputClose,
    };
}
