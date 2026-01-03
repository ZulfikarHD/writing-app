<script setup lang="ts">
/**
 * PromptExecutionWrapper
 * 
 * A wrapper component that handles prompt execution with input forms.
 * Use this component when you need to execute prompts that may have inputs.
 * 
 * Usage:
 * ```vue
 * <PromptExecutionWrapper
 *     ref="promptExecutor"
 *     @executed="handlePromptExecuted"
 *     @cancelled="handlePromptCancelled"
 * />
 * 
 * // In your script:
 * const promptExecutor = ref<InstanceType<typeof PromptExecutionWrapper>>();
 * 
 * async function runPrompt(prompt: Prompt) {
 *     await promptExecutor.value?.execute(prompt);
 * }
 * ```
 */
import InputForm from './InputForm.vue';
import type { PromptInputDef, InputValues } from './InputForm.vue';
import { usePromptExecution } from '@/composables/usePromptExecution';
import type { Prompt } from '@/composables/usePrompts';

export interface PromptExecutionResult {
    promptId: number;
    promptName: string;
    inputValues: InputValues;
    resolvedSystemMessage: string | null;
    resolvedUserMessage: string | null;
}

const emit = defineEmits<{
    (e: 'executed', result: PromptExecutionResult): void;
    (e: 'cancelled'): void;
}>();

const {
    showInputForm,
    currentInputs,
    currentPromptName,
    currentDefaultValues,
    executePrompt,
    handleInputSubmit,
    handleInputClose,
} = usePromptExecution();

/**
 * Execute a prompt, showing the input form if the prompt has inputs.
 * Returns a promise that resolves with the execution result or null if cancelled.
 */
async function execute(prompt: Prompt): Promise<PromptExecutionResult | null> {
    const result = await executePrompt(prompt);

    if (result) {
        const executionResult: PromptExecutionResult = {
            promptId: result.promptId,
            promptName: prompt.name,
            inputValues: result.inputValues,
            resolvedSystemMessage: result.resolvedSystemMessage,
            resolvedUserMessage: result.resolvedUserMessage,
        };
        emit('executed', executionResult);
        return executionResult;
    } else {
        emit('cancelled');
        return null;
    }
}

/**
 * Handle form submission
 */
function onSubmit(values: InputValues) {
    handleInputSubmit(values);
}

/**
 * Handle form close
 */
function onClose() {
    handleInputClose();
}

// Convert inputs to the format expected by InputForm
function mapInputs(inputs: typeof currentInputs.value): PromptInputDef[] {
    return inputs.map((input) => ({
        id: input.id,
        name: input.name,
        label: input.label,
        type: input.type,
        options: input.options,
        default_value: input.default_value,
        placeholder: input.placeholder,
        description: input.description,
        is_required: input.is_required,
        sort_order: input.sort_order,
    }));
}

// Expose the execute method for parent components
defineExpose({
    execute,
});
</script>

<template>
    <InputForm
        :show="showInputForm"
        :prompt-name="currentPromptName"
        :inputs="mapInputs(currentInputs)"
        :initial-values="currentDefaultValues"
        @submit="onSubmit"
        @close="onClose"
    />
</template>
