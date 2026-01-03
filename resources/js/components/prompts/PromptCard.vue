<script setup lang="ts">
import type { Prompt } from '@/composables/usePrompts';
import Badge from '@/components/ui/Badge.vue';

interface Props {
    prompt: Prompt;
    selected?: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    select: [];
    clone: [];
    delete: [];
}>();
</script>

<template>
    <div
        class="group relative cursor-pointer rounded-lg border p-3 transition-all"
        :class="[
            selected
                ? 'border-violet-500 bg-violet-50 dark:border-violet-500 dark:bg-violet-950/30'
                : 'border-zinc-200 bg-white hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700',
        ]"
        @click="emit('select')"
    >
        <div class="flex items-start gap-2">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <h3 class="truncate text-sm font-medium text-zinc-900 dark:text-white">
                        {{ prompt.name }}
                    </h3>
                    <Badge v-if="prompt.is_system" variant="secondary" class="shrink-0 text-[10px]">
                        System
                    </Badge>
                </div>
                <p v-if="prompt.description" class="mt-1 line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ prompt.description }}
                </p>
            </div>

            <!-- Actions Menu -->
            <div class="flex shrink-0 items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                <button
                    type="button"
                    class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                    title="Clone prompt"
                    @click.stop="emit('clone')"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                        />
                    </svg>
                </button>
                <button
                    v-if="!prompt.is_system"
                    type="button"
                    class="rounded p-1 text-zinc-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400"
                    title="Delete prompt"
                    @click.stop="emit('delete')"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Usage Count -->
        <div class="mt-2 flex items-center gap-2 text-[10px] text-zinc-400 dark:text-zinc-500">
            <span v-if="prompt.usage_count > 0" class="flex items-center gap-1">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z"
                    />
                </svg>
                {{ prompt.usage_count }} uses
            </span>
        </div>
    </div>
</template>
