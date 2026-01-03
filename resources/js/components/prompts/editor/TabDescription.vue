<script setup lang="ts">
interface Props {
    description: string;
    isEditable: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    'update:description': [value: string];
}>();
</script>

<template>
    <div class="space-y-4">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-2 font-medium text-zinc-900 dark:text-white">Prompt Description</h3>
            <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                Add helpful notes and documentation for this prompt. This is <strong>never</strong> sent to the AI model.
            </p>

            <textarea
                :value="description"
                :disabled="!isEditable"
                rows="12"
                placeholder="Describe what this prompt does, how to use it effectively, tips and tricks, etc..."
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                @input="emit('update:description', ($event.target as HTMLTextAreaElement).value)"
            ></textarea>
        </div>

        <!-- Description Tips -->
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
            <h4 class="mb-2 flex items-center gap-2 text-sm font-medium text-amber-800 dark:text-amber-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                Tips for Writing Good Descriptions
            </h4>
            <ul class="space-y-1 text-sm text-amber-700 dark:text-amber-400">
                <li>• Explain what the prompt is designed to do</li>
                <li>• List the best use cases and scenarios</li>
                <li>• Document any required inputs and their expected values</li>
                <li>• Include example outputs or expected results</li>
                <li>• Note any limitations or edge cases</li>
            </ul>
        </div>
    </div>
</template>
