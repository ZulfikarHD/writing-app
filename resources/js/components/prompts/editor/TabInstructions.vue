<script setup lang="ts">
import { computed } from 'vue';
import MessageList from './MessageList.vue';
import VariableAutocomplete from './VariableAutocomplete.vue';

export interface PromptMessage {
    id: string;
    role: 'user' | 'assistant';
    content: string;
}

interface Props {
    systemMessage: string;
    userMessage: string;
    messages: PromptMessage[];
    promptType: 'chat' | 'prose' | 'replacement' | 'summary';
    isEditable: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:systemMessage': [value: string];
    'update:userMessage': [value: string];
    'update:messages': [value: PromptMessage[]];
    insertVariable: [variable: string, field: 'system' | 'user'];
}>();

// Available variables based on prompt type (for MessageList)
const availableVariables = computed(() => {
    const baseVars = [
        { name: 'scene.title', description: 'Current scene title' },
        { name: 'scene.summary', description: 'Scene summary' },
        { name: 'scene.fullText', description: 'Full scene text' },
        { name: 'codex.characters', description: 'Character codex entries' },
        { name: 'codex.locations', description: 'Location codex entries' },
        { name: 'codex.context', description: 'Relevant codex context' },
        { name: 'textBefore', description: 'Text before cursor' },
        { name: 'textAfter', description: 'Text after cursor' },
    ];

    switch (props.promptType) {
        case 'chat':
            return [
                ...baseVars,
                { name: 'message', description: 'User message input' },
                { name: 'storySoFar', description: 'Story context so far' },
            ];
        case 'prose':
            return [
                ...baseVars,
                { name: 'nextBeat', description: 'Next beat to write' },
                { name: 'previousBeat', description: 'Previous beat' },
            ];
        case 'replacement':
            return [
                ...baseVars,
                { name: 'content', description: 'Selected text to transform' },
            ];
        case 'summary':
            return [
                ...baseVars,
                { name: 'scene.fullText', description: 'Full scene to summarize' },
            ];
        default:
            return baseVars;
    }
});

// Get placeholder for user message based on type
function getUserMessagePlaceholder() {
    switch (props.promptType) {
        case 'chat':
            return 'Enter the user message template (optional)...';
        case 'prose':
            return 'Please write prose for the following scene beat:\n\n{nextBeat}\n\nContext:\n{storySoFar}';
        case 'replacement':
            return 'Please transform the following text:\n\n{content}';
        case 'summary':
            return 'Please summarize this scene:\n\n{scene.fullText}';
        default:
            return 'Enter the user message template...';
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- System Message -->
        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    System Message
                </label>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    Type <code class="rounded bg-zinc-200 px-1 dark:bg-zinc-700">{`{`}</code> for variables
                </span>
            </div>
            <VariableAutocomplete
                v-if="isEditable"
                :model-value="systemMessage"
                :rows="8"
                placeholder="Enter system instructions for the AI..."
                @update:model-value="emit('update:systemMessage', $event)"
            />
            <textarea
                v-else
                :value="systemMessage"
                disabled
                rows="8"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 font-mono text-sm disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
            ></textarea>
        </div>

        <!-- User Message Template -->
        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    User Message Template
                </label>
            </div>
            <VariableAutocomplete
                v-if="isEditable"
                :model-value="userMessage"
                :rows="6"
                :placeholder="getUserMessagePlaceholder()"
                @update:model-value="emit('update:userMessage', $event)"
            />
            <textarea
                v-else
                :value="userMessage"
                disabled
                rows="6"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 font-mono text-sm disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
            ></textarea>
        </div>

        <!-- Advanced: Multiple Messages -->
        <div v-if="messages.length > 0 || isEditable" class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="mb-3 flex items-center justify-between">
                <h4 class="text-sm font-medium text-zinc-900 dark:text-white">
                    Additional Messages (Optional)
                </h4>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    For multi-turn prompts
                </span>
            </div>
            <MessageList
                :messages="messages"
                :is-editable="isEditable"
                :available-variables="availableVariables"
                @update:messages="emit('update:messages', $event)"
            />
        </div>
    </div>
</template>
