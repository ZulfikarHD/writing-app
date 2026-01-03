<script setup lang="ts">
import Badge from '@/components/ui/Badge.vue';
import type { PromptComponent } from '@/composables/useComponents';
import { ref } from 'vue';

const props = defineProps<{
    component: PromptComponent;
    compact?: boolean;
}>();

const emit = defineEmits<{
    (e: 'click', component: PromptComponent): void;
    (e: 'edit', component: PromptComponent): void;
    (e: 'clone', component: PromptComponent): void;
    (e: 'delete', component: PromptComponent): void;
    (e: 'copy', component: PromptComponent): void;
}>();

const showDropdown = ref(false);
const showCopiedBadge = ref(false);

// Copy include call to clipboard
async function handleCopy() {
    try {
        await navigator.clipboard.writeText(`{include("${props.component.name}")}`);
        showCopiedBadge.value = true;
        emit('copy', props.component);
        setTimeout(() => {
            showCopiedBadge.value = false;
        }, 2000);
    } catch {
        // Silently fail
    }
    showDropdown.value = false;
}

// Get truncated content preview
function getContentPreview(content: string, maxLength = 60): string {
    if (content.length <= maxLength) return content;
    return content.substring(0, maxLength).trim() + '...';
}
</script>

<template>
    <div
        class="group relative rounded-lg border border-zinc-200 bg-white transition-colors hover:border-violet-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-violet-600"
        :class="compact ? 'p-2' : 'p-3'"
    >
        <button
            type="button"
            class="w-full text-left"
            @click="emit('click', component)"
        >
            <div class="flex items-start gap-2">
                <!-- Icon -->
                <div
                    class="flex shrink-0 items-center justify-center rounded-lg"
                    :class="[
                        compact ? 'h-6 w-6' : 'h-8 w-8',
                        component.is_system
                            ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'
                            : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                    ]"
                >
                    <svg
                        class="h-4 w-4"
                        :class="compact ? 'h-3 w-3' : 'h-4 w-4'"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                        />
                    </svg>
                </div>

                <!-- Content -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <h3
                            class="truncate font-medium text-zinc-900 dark:text-white"
                            :class="compact ? 'text-xs' : 'text-sm'"
                        >
                            {{ component.label }}
                        </h3>
                        <Badge v-if="component.is_system" variant="info" size="sm">System</Badge>
                        <Badge v-if="showCopiedBadge" variant="success" size="sm">Copied!</Badge>
                    </div>

                    <!-- Name (identifier) -->
                    <code
                        v-if="!compact"
                        class="mt-0.5 block truncate text-[10px] text-zinc-500 dark:text-zinc-400"
                    >
                        {include("{{ component.name }}")}
                    </code>

                    <!-- Content preview -->
                    <p
                        v-if="!compact && component.content"
                        class="mt-1 line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400"
                    >
                        {{ getContentPreview(component.content) }}
                    </p>

                    <!-- Description -->
                    <p
                        v-if="!compact && component.description"
                        class="mt-1 line-clamp-1 text-[10px] italic text-zinc-400 dark:text-zinc-500"
                    >
                        {{ component.description }}
                    </p>
                </div>
            </div>
        </button>

        <!-- Action buttons (on hover) -->
        <div
            class="absolute right-2 top-2 flex items-center gap-0.5 opacity-0 transition-opacity group-hover:opacity-100"
        >
            <!-- Copy button -->
            <button
                type="button"
                class="rounded p-1 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                title="Copy include() call"
                @click.stop="handleCopy"
            >
                <svg class="h-3.5 w-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                    />
                </svg>
            </button>

            <!-- Edit button -->
            <button
                type="button"
                class="rounded p-1 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                title="Edit component"
                @click.stop="emit('edit', component)"
            >
                <svg class="h-3.5 w-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                    />
                </svg>
            </button>

            <!-- More actions dropdown -->
            <div class="relative">
                <button
                    type="button"
                    class="rounded p-1 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                    title="More actions"
                    @click.stop="showDropdown = !showDropdown"
                >
                    <svg class="h-3.5 w-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
                        />
                    </svg>
                </button>

                <!-- Dropdown menu -->
                <div
                    v-if="showDropdown"
                    class="absolute right-0 top-full z-10 mt-1 min-w-[140px] rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                    @mouseleave="showDropdown = false"
                >
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        @click.stop="emit('clone', component); showDropdown = false"
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
                        v-if="!component.is_system"
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30"
                        @click.stop="emit('delete', component); showDropdown = false"
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
    </div>
</template>
