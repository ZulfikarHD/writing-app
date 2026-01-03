<script setup lang="ts">
import Badge from '@/components/ui/Badge.vue';
import type { Persona, InteractionType } from '@/composables/usePersonas';

defineProps<{
    persona: Persona;
    compact?: boolean;
}>();

const emit = defineEmits<{
    (e: 'click', persona: Persona): void;
    (e: 'edit', persona: Persona): void;
}>();

// Get interaction type labels
const interactionTypeLabels: Record<InteractionType, string> = {
    chat: 'Chat',
    prose: 'Prose',
    replacement: 'Replace',
    summary: 'Summary',
};
</script>

<template>
    <div
        class="group relative rounded-lg border border-zinc-200 bg-white p-3 transition-colors hover:border-violet-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-violet-600"
        :class="compact ? 'p-2' : 'p-3'"
    >
        <button
            type="button"
            class="w-full text-left"
            @click="emit('click', persona)"
        >
            <div class="flex items-start gap-2">
                <!-- Icon -->
                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400"
                    :class="compact ? 'h-6 w-6' : 'h-8 w-8'"
                >
                    <svg class="h-4 w-4" :class="compact ? 'h-3 w-3' : 'h-4 w-4'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>

                <!-- Content -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <h3
                            class="truncate font-medium text-zinc-900 dark:text-white"
                            :class="compact ? 'text-xs' : 'text-sm'"
                        >
                            {{ persona.name }}
                        </h3>
                        <Badge v-if="persona.is_default" variant="info" size="sm">Default</Badge>
                    </div>

                    <p
                        v-if="!compact && persona.description"
                        class="mt-0.5 line-clamp-1 text-xs text-zinc-500 dark:text-zinc-400"
                    >
                        {{ persona.description }}
                    </p>

                    <!-- Interaction types -->
                    <div v-if="!compact" class="mt-1.5 flex flex-wrap gap-1">
                        <span
                            v-if="!persona.interaction_types || persona.interaction_types.length === 0"
                            class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400"
                        >
                            All types
                        </span>
                        <span
                            v-else
                            v-for="type in persona.interaction_types"
                            :key="type"
                            class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400"
                        >
                            {{ interactionTypeLabels[type] }}
                        </span>
                    </div>
                </div>
            </div>
        </button>

        <!-- Edit button (on hover) -->
        <button
            type="button"
            class="absolute right-2 top-2 rounded p-1 opacity-0 transition-opacity hover:bg-zinc-100 group-hover:opacity-100 dark:hover:bg-zinc-700"
            title="Edit persona"
            @click.stop="emit('edit', persona)"
        >
            <svg class="h-3.5 w-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
        </button>
    </div>
</template>
