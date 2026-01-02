<script setup lang="ts">
import { computed, ref } from 'vue';
import { Motion } from 'motion-v';
import Modal from '@/components/ui/layout/Modal.vue';
import type { ContextItem, ContextLimitInfo, ContextTokenInfo } from '@/composables/useChatContext';

interface Props {
    show: boolean;
    contextItems: ContextItem[];
    tokenInfo: ContextTokenInfo | null;
    limitInfo: ContextLimitInfo | null;
    isLoading?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
    toggle: [itemId: number];
    remove: [itemId: number];
    clear: [];
}>();

const expandedItems = ref<Set<number>>(new Set());

// Toggle expanded state for an item
const toggleExpanded = (itemId: number) => {
    if (expandedItems.value.has(itemId)) {
        expandedItems.value.delete(itemId);
    } else {
        expandedItems.value.add(itemId);
    }
    expandedItems.value = new Set(expandedItems.value); // Trigger reactivity
};

// Format token count
const formatTokens = (tokens: number): string => {
    if (tokens >= 1000) {
        return `${(tokens / 1000).toFixed(1)}k`;
    }
    return tokens.toString();
};

// Usage bar color based on percentage
const usageBarColor = computed(() => {
    const percentage = props.limitInfo?.usage_percentage ?? 0;
    if (percentage > 100) return 'bg-red-500';
    if (percentage > 80) return 'bg-amber-500';
    return 'bg-violet-500';
});

// Get icon for context type
const getTypeIcon = (type: ContextItem['context_type']): string => {
    const icons: Record<string, string> = {
        scene: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        codex: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        summary: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        outline: 'M4 6h16M4 10h16M4 14h16M4 18h16',
        custom: 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z',
    };
    return icons[type] || icons.custom;
};

// Get name for context type
const getTypeName = (type: ContextItem['context_type']): string => {
    const names: Record<string, string> = {
        scene: 'Scene',
        codex: 'Codex',
        summary: 'Summary',
        outline: 'Outline',
        custom: 'Custom',
    };
    return names[type] || 'Context';
};

// Get item display name
const getItemName = (item: ContextItem): string => {
    switch (item.context_type) {
        case 'scene':
            return item.reference?.title ?? 'Untitled Scene';
        case 'codex':
            return item.reference?.name ?? 'Unknown Entry';
        case 'summary':
            return 'Novel Summary';
        case 'outline':
            return 'Story Outline';
        case 'custom':
            return 'Custom Context';
        default:
            return 'Context Item';
    }
};

// Active items
const activeItems = computed(() => props.contextItems.filter((item) => item.is_active));
const inactiveItems = computed(() => props.contextItems.filter((item) => !item.is_active));
</script>

<template>
    <Modal :show="show" title="Context Preview" size="lg" scrollable @close="emit('close')">
        <div class="space-y-4">
            <!-- Token Usage Bar -->
            <div v-if="limitInfo" class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Token Usage</span>
                    <span
                        :class="[
                            'text-sm font-semibold',
                            limitInfo.usage_percentage > 100
                                ? 'text-red-600 dark:text-red-400'
                                : limitInfo.usage_percentage > 80
                                  ? 'text-amber-600 dark:text-amber-400'
                                  : 'text-zinc-900 dark:text-white',
                        ]"
                    >
                        {{ formatTokens(limitInfo.tokens_used) }} / {{ formatTokens(limitInfo.limit) }}
                    </span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                    <Motion
                        :initial="{ width: '0%' }"
                        :animate="{ width: `${Math.min(limitInfo.usage_percentage, 100)}%` }"
                        :transition="{ duration: 0.5, ease: 'easeOut' }"
                        :class="['h-full rounded-full transition-colors', usageBarColor]"
                    />
                </div>
                <p v-if="limitInfo.usage_percentage > 80" class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                    <svg class="mr-1 inline h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    {{ limitInfo.usage_percentage > 100 ? 'Context exceeds limit. Some content may be truncated.' : 'Approaching context limit.' }}
                </p>
            </div>

            <!-- Active Context Items -->
            <div v-if="activeItems.length > 0">
                <h4 class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Active Context ({{ activeItems.length }})</h4>
                <div class="space-y-2">
                    <div
                        v-for="item in activeItems"
                        :key="item.id"
                        class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
                    >
                        <!-- Item Header -->
                        <div class="flex items-center gap-3 px-4 py-3">
                            <button
                                type="button"
                                class="flex h-5 w-5 items-center justify-center rounded border border-violet-500 bg-violet-500 text-white transition-all hover:bg-violet-600 active:scale-95"
                                @click="emit('toggle', item.id)"
                            >
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>

                            <div class="flex flex-1 items-center gap-2">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="getTypeIcon(item.context_type)" />
                                </svg>
                                <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ getItemName(item) }}</span>
                                <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">
                                    {{ getTypeName(item.context_type) }}
                                </span>
                            </div>

                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ formatTokens(item.tokens) }} tokens</span>

                            <button
                                type="button"
                                class="rounded p-1 text-zinc-400 transition-all hover:bg-zinc-100 hover:text-zinc-600 active:scale-95 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                                @click="toggleExpanded(item.id)"
                            >
                                <svg
                                    :class="['h-4 w-4 transition-transform', expandedItems.has(item.id) ? 'rotate-180' : '']"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                class="rounded p-1 text-zinc-400 transition-all hover:bg-red-50 hover:text-red-600 active:scale-95 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                @click="emit('remove', item.id)"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        <!-- Item Preview (Expanded) -->
                        <Motion
                            v-if="expandedItems.has(item.id)"
                            :initial="{ height: 0, opacity: 0 }"
                            :animate="{ height: 'auto', opacity: 1 }"
                            :exit="{ height: 0, opacity: 0 }"
                            :transition="{ duration: 0.2 }"
                            class="overflow-hidden"
                        >
                            <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <pre class="max-h-40 overflow-auto whitespace-pre-wrap text-xs text-zinc-600 dark:text-zinc-400">{{ item.preview || item.custom_content || 'No preview available' }}</pre>
                            </div>
                        </Motion>
                    </div>
                </div>
            </div>

            <!-- Inactive Context Items -->
            <div v-if="inactiveItems.length > 0">
                <h4 class="mb-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">Inactive ({{ inactiveItems.length }})</h4>
                <div class="space-y-2">
                    <div
                        v-for="item in inactiveItems"
                        :key="item.id"
                        class="flex items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 opacity-60 dark:border-zinc-700 dark:bg-zinc-800/50"
                    >
                        <button
                            type="button"
                            class="flex h-5 w-5 items-center justify-center rounded border border-zinc-300 bg-white transition-all hover:border-violet-500 active:scale-95 dark:border-zinc-600 dark:bg-zinc-700"
                            @click="emit('toggle', item.id)"
                        />

                        <div class="flex flex-1 items-center gap-2">
                            <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="getTypeIcon(item.context_type)" />
                            </svg>
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ getItemName(item) }}</span>
                        </div>

                        <span class="text-xs text-zinc-400">{{ formatTokens(item.tokens) }} tokens</span>

                        <button
                            type="button"
                            class="rounded p-1 text-zinc-400 transition-all hover:bg-red-50 hover:text-red-600 active:scale-95 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                            @click="emit('remove', item.id)"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="contextItems.length === 0" class="py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <h3 class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">No context added</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Add scenes, codex entries, or custom text to give AI more context.</p>
            </div>
        </div>

        <!-- Footer -->
        <template #footer>
            <button
                v-if="contextItems.length > 0"
                type="button"
                class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 active:scale-95 dark:border-red-800 dark:bg-transparent dark:text-red-400 dark:hover:bg-red-900/20"
                @click="emit('clear')"
            >
                Clear All
            </button>
            <button
                type="button"
                class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-zinc-800 active:scale-95 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100"
                @click="emit('close')"
            >
                Done
            </button>
        </template>
    </Modal>
</template>
