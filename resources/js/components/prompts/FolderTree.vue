<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { Prompt } from '@/composables/usePrompts';
import { buildPromptFolderTree, getPromptDisplayName, type PromptFolderNode } from '@/composables/usePrompts';
import Badge from '@/components/ui/Badge.vue';

interface Props {
    prompts: Prompt[];
    selectedPromptId?: number | null;
    typeFilter?: string | null;
    searchQuery?: string;
}

const props = withDefaults(defineProps<Props>(), {
    selectedPromptId: null,
    typeFilter: null,
    searchQuery: '',
});

const emit = defineEmits<{
    select: [prompt: Prompt];
    clone: [promptId: number];
    delete: [promptId: number];
    export: [promptId: number];
}>();

// Track expanded folders
const expandedFolders = ref<Set<string>>(new Set());

// Filter prompts
const filteredPrompts = computed(() => {
    let result = props.prompts;
    
    if (props.typeFilter) {
        result = result.filter(p => p.type === props.typeFilter);
    }
    
    if (props.searchQuery) {
        const query = props.searchQuery.toLowerCase();
        result = result.filter(
            p => p.name.toLowerCase().includes(query) || p.description?.toLowerCase().includes(query)
        );
    }
    
    return result;
});

// Build folder tree
const folderTree = computed(() => {
    return buildPromptFolderTree(filteredPrompts.value);
});

// Auto-expand folders when searching
watch(() => props.searchQuery, (query) => {
    if (query) {
        // Expand all folders when searching
        const allPaths = new Set<string>();
        const collectPaths = (nodes: PromptFolderNode[]) => {
            for (const node of nodes) {
                if (node.path) allPaths.add(node.path);
                collectPaths(node.children);
            }
        };
        collectPaths(folderTree.value);
        expandedFolders.value = allPaths;
    }
});

function toggleFolder(path: string) {
    if (expandedFolders.value.has(path)) {
        expandedFolders.value.delete(path);
    } else {
        expandedFolders.value.add(path);
    }
    expandedFolders.value = new Set(expandedFolders.value); // Trigger reactivity
}

function isExpanded(path: string): boolean {
    return expandedFolders.value.has(path);
}

// Count total prompts in a folder (including nested)
function countPromptsInFolder(node: PromptFolderNode): number {
    let count = node.prompts.length;
    for (const child of node.children) {
        count += countPromptsInFolder(child);
    }
    return count;
}
</script>

<template>
    <div class="folder-tree space-y-1">
        <!-- Empty State -->
        <div v-if="folderTree.length === 0" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
            No prompts found
        </div>

        <!-- Tree Nodes -->
        <template v-for="node in folderTree" :key="node.path || '_root'">
            <!-- Ungrouped prompts (root level without folder) -->
            <template v-if="node.name === ''">
                <div
                    v-for="prompt in node.prompts"
                    :key="prompt.id"
                    class="prompt-item group relative cursor-pointer rounded-lg border p-2.5 transition-all"
                    :class="[
                        selectedPromptId === prompt.id
                            ? 'border-violet-500 bg-violet-50 dark:border-violet-500 dark:bg-violet-950/30'
                            : 'border-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800',
                    ]"
                    @click="emit('select', prompt)"
                >
                    <div class="flex items-center gap-2">
                        <span class="flex-1 truncate text-sm text-zinc-900 dark:text-white">
                            {{ getPromptDisplayName(prompt) }}
                        </span>
                        <Badge v-if="prompt.is_system" variant="secondary" class="shrink-0 text-[10px]">
                            System
                        </Badge>
                    </div>
                </div>
            </template>

            <!-- Folder nodes -->
            <div v-else class="folder-node">
                <!-- Folder header -->
                <button
                    type="button"
                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    @click="toggleFolder(node.path)"
                >
                    <svg
                        class="h-4 w-4 shrink-0 text-zinc-400 transition-transform"
                        :class="{ 'rotate-90': isExpanded(node.path) }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <svg
                        class="h-4 w-4 shrink-0"
                        :class="isExpanded(node.path) ? 'text-violet-500' : 'text-zinc-400'"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            v-if="isExpanded(node.path)"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"
                        />
                        <path
                            v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"
                        />
                    </svg>
                    <span class="flex-1 truncate text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ node.name }}
                    </span>
                    <span class="text-xs text-zinc-400 dark:text-zinc-500">
                        {{ countPromptsInFolder(node) }}
                    </span>
                </button>

                <!-- Expanded content -->
                <div v-if="isExpanded(node.path)" class="ml-4 mt-1 space-y-1 border-l border-zinc-200 pl-2 dark:border-zinc-700">
                    <!-- Prompts in this folder -->
                    <div
                        v-for="prompt in node.prompts"
                        :key="prompt.id"
                        class="prompt-item group relative cursor-pointer rounded-lg border p-2.5 transition-all"
                        :class="[
                            selectedPromptId === prompt.id
                                ? 'border-violet-500 bg-violet-50 dark:border-violet-500 dark:bg-violet-950/30'
                                : 'border-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800',
                        ]"
                        @click="emit('select', prompt)"
                    >
                        <div class="flex items-center gap-2">
                            <span class="flex-1 truncate text-sm text-zinc-900 dark:text-white">
                                {{ getPromptDisplayName(prompt) }}
                            </span>
                            <Badge v-if="prompt.is_system" variant="secondary" class="shrink-0 text-[10px]">
                                System
                            </Badge>
                        </div>
                    </div>

                    <!-- Nested folders -->
                    <template v-for="child in node.children" :key="child.path">
                        <div class="nested-folder">
                            <button
                                type="button"
                                class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                @click="toggleFolder(child.path)"
                            >
                                <svg
                                    class="h-4 w-4 shrink-0 text-zinc-400 transition-transform"
                                    :class="{ 'rotate-90': isExpanded(child.path) }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <svg
                                    class="h-4 w-4 shrink-0"
                                    :class="isExpanded(child.path) ? 'text-violet-500' : 'text-zinc-400'"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        v-if="isExpanded(child.path)"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"
                                    />
                                    <path
                                        v-else
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"
                                    />
                                </svg>
                                <span class="flex-1 truncate text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ child.name }}
                                </span>
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                    {{ countPromptsInFolder(child) }}
                                </span>
                            </button>

                            <div v-if="isExpanded(child.path)" class="ml-4 mt-1 space-y-1 border-l border-zinc-200 pl-2 dark:border-zinc-700">
                                <div
                                    v-for="prompt in child.prompts"
                                    :key="prompt.id"
                                    class="prompt-item group relative cursor-pointer rounded-lg border p-2.5 transition-all"
                                    :class="[
                                        selectedPromptId === prompt.id
                                            ? 'border-violet-500 bg-violet-50 dark:border-violet-500 dark:bg-violet-950/30'
                                            : 'border-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800',
                                    ]"
                                    @click="emit('select', prompt)"
                                >
                                    <div class="flex items-center gap-2">
                                        <span class="flex-1 truncate text-sm text-zinc-900 dark:text-white">
                                            {{ getPromptDisplayName(prompt) }}
                                        </span>
                                        <Badge v-if="prompt.is_system" variant="secondary" class="shrink-0 text-[10px]">
                                            System
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</template>
