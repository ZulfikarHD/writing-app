<script setup lang="ts">
import { ref } from 'vue';
import { onClickOutside } from '@vueuse/core';
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
    export: [];
}>();

const showMenu = ref(false);
const menuRef = ref<HTMLElement | null>(null);

onClickOutside(menuRef, () => {
    showMenu.value = false;
});

function toggleMenu(event: Event) {
    event.stopPropagation();
    showMenu.value = !showMenu.value;
}

function handleAction(action: 'clone' | 'delete' | 'export') {
    showMenu.value = false;
    emit(action);
}
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
            <div ref="menuRef" class="relative shrink-0">
                <button
                    type="button"
                    class="rounded p-1 text-zinc-400 opacity-0 transition-opacity hover:bg-zinc-100 hover:text-zinc-600 group-hover:opacity-100 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                    :class="{ 'opacity-100': showMenu }"
                    title="More actions"
                    @click="toggleMenu"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div
                    v-if="showMenu"
                    class="absolute right-0 top-full z-10 mt-1 w-40 rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                >
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        @click.stop="handleAction('clone')"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                            />
                        </svg>
                        Clone
                    </button>
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        @click.stop="handleAction('export')"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                        </svg>
                        Copy to Clipboard
                    </button>
                    <div v-if="!prompt.is_system" class="my-1 border-t border-zinc-200 dark:border-zinc-700"></div>
                    <button
                        v-if="!prompt.is_system"
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950"
                        @click.stop="handleAction('delete')"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                        Delete
                    </button>
                </div>
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
