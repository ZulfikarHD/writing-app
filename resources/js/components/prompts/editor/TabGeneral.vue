<script setup lang="ts">
import type { ModelSettings } from '@/composables/usePrompts';

interface Props {
    name: string;
    type: 'chat' | 'prose' | 'replacement' | 'summary';
    types: Record<string, string>;
    modelSettings: ModelSettings;
    isEditable: boolean;
    isCreating: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:name': [value: string];
    'update:type': [value: 'chat' | 'prose' | 'replacement' | 'summary'];
    'update:modelSettings': [value: ModelSettings];
}>();

function updateModelSetting(key: keyof ModelSettings, value: number | undefined) {
    emit('update:modelSettings', {
        ...props.modelSettings,
        [key]: value || undefined,
    });
}
</script>

<template>
    <div class="space-y-6">
        <!-- Name -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                Name <span v-if="isEditable" class="text-red-500">*</span>
            </label>
            <input
                :value="name"
                type="text"
                :disabled="!isEditable"
                placeholder="Enter prompt name..."
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                @input="emit('update:name', ($event.target as HTMLInputElement).value)"
            />
        </div>

        <!-- Type -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                Type <span v-if="isEditable && isCreating" class="text-red-500">*</span>
            </label>
            <select
                :value="type"
                :disabled="!isEditable || !isCreating"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                @change="emit('update:type', ($event.target as HTMLSelectElement).value as 'chat' | 'prose' | 'replacement' | 'summary')"
            >
                <option v-for="(label, value) in types" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                Determines where and how this prompt can be used.
            </p>
        </div>

        <!-- Model Settings Section -->
        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
            <h3 class="mb-3 font-medium text-zinc-900 dark:text-white">Model Settings</h3>
            <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                Override model settings for this prompt. Leave empty to use defaults.
            </p>

            <div class="grid gap-4 sm:grid-cols-2">
                <!-- Temperature -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Temperature
                    </label>
                    <input
                        :value="modelSettings.temperature"
                        type="number"
                        :disabled="!isEditable"
                        min="0"
                        max="2"
                        step="0.1"
                        placeholder="0.7"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                        @input="updateModelSetting('temperature', parseFloat(($event.target as HTMLInputElement).value) || undefined)"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        0 = focused, 2 = creative
                    </p>
                </div>

                <!-- Max Tokens -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Max Tokens
                    </label>
                    <input
                        :value="modelSettings.max_tokens"
                        type="number"
                        :disabled="!isEditable"
                        min="1"
                        placeholder="2048"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                        @input="updateModelSetting('max_tokens', parseInt(($event.target as HTMLInputElement).value) || undefined)"
                    />
                </div>

                <!-- Top P -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Top P
                    </label>
                    <input
                        :value="modelSettings.top_p"
                        type="number"
                        :disabled="!isEditable"
                        min="0"
                        max="1"
                        step="0.1"
                        placeholder="1"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                        @input="updateModelSetting('top_p', parseFloat(($event.target as HTMLInputElement).value) || undefined)"
                    />
                </div>

                <!-- Frequency Penalty -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Frequency Penalty
                    </label>
                    <input
                        :value="modelSettings.frequency_penalty"
                        type="number"
                        :disabled="!isEditable"
                        min="-2"
                        max="2"
                        step="0.1"
                        placeholder="0"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                        @input="updateModelSetting('frequency_penalty', parseFloat(($event.target as HTMLInputElement).value) || undefined)"
                    />
                </div>

                <!-- Presence Penalty -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Presence Penalty
                    </label>
                    <input
                        :value="modelSettings.presence_penalty"
                        type="number"
                        :disabled="!isEditable"
                        min="-2"
                        max="2"
                        step="0.1"
                        placeholder="0"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                        @input="updateModelSetting('presence_penalty', parseFloat(($event.target as HTMLInputElement).value) || undefined)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
