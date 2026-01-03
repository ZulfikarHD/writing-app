<script setup lang="ts">
import type { Prompt } from '@/composables/usePrompts';
import { usePrompts, buildPromptFolderTree, getPromptDisplayName, type PromptFolderNode } from '@/composables/usePrompts';
import { ref, computed, onMounted, watch } from 'vue';
import { onClickOutside } from '@vueuse/core';

interface Props {
    modelValue?: number | null;
    type: 'chat' | 'prose' | 'replacement' | 'summary';
    placeholder?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    placeholder: 'Select prompt...',
    disabled: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
    change: [prompt: Prompt | null];
}>();

const { fetchPromptsByType, recordUsage } = usePrompts();

const isOpen = ref(false);
const isLoading = ref(false);
const prompts = ref<Prompt[]>([]);
const searchQuery = ref('');
const dropdownRef = ref<HTMLElement | null>(null);
const expandedFolders = ref<Set<string>>(new Set());

// Selected prompt
const selectedPrompt = computed(() => {
    if (!props.modelValue) return null;
    return prompts.value.find((p) => p.id === props.modelValue) || null;
});

// Filtered prompts
const filteredPrompts = computed(() => {
    if (!searchQuery.value) return prompts.value;

    const query = searchQuery.value.toLowerCase();
    return prompts.value.filter(
        (p) => p.name.toLowerCase().includes(query) || p.description?.toLowerCase().includes(query),
    );
});

// Grouped prompts (system first, then user)
const groupedPrompts = computed(() => {
    const system = filteredPrompts.value.filter((p) => p.is_system);
    const user = filteredPrompts.value.filter((p) => !p.is_system);
    return { system, user };
});

// Folder tree for user prompts
const userFolderTree = computed(() => {
    return buildPromptFolderTree(groupedPrompts.value.user);
});

// Toggle folder expansion
function toggleFolder(path: string) {
    if (expandedFolders.value.has(path)) {
        expandedFolders.value.delete(path);
    } else {
        expandedFolders.value.add(path);
    }
    expandedFolders.value = new Set(expandedFolders.value);
}

function isExpanded(path: string): boolean {
    return expandedFolders.value.has(path);
}

// Auto-expand when searching
watch(searchQuery, (query) => {
    if (query) {
        const allPaths = new Set<string>();
        const collectPaths = (nodes: PromptFolderNode[]) => {
            for (const node of nodes) {
                if (node.path) allPaths.add(node.path);
                collectPaths(node.children);
            }
        };
        collectPaths(userFolderTree.value);
        expandedFolders.value = allPaths;
    }
});

// Load prompts
async function loadPrompts() {
    if (prompts.value.length > 0) return;

    isLoading.value = true;
    prompts.value = await fetchPromptsByType(props.type);
    isLoading.value = false;
}

// Select a prompt
function selectPrompt(prompt: Prompt | null) {
    emit('update:modelValue', prompt?.id || null);
    emit('change', prompt);

    if (prompt) {
        recordUsage(prompt.id);
    }

    isOpen.value = false;
    searchQuery.value = '';
}

// Toggle dropdown
function toggleDropdown() {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        loadPrompts();
    }
}

// Close dropdown on outside click
onClickOutside(dropdownRef, () => {
    isOpen.value = false;
    searchQuery.value = '';
});

// Watch for type changes to reload prompts
watch(() => props.type, () => {
    prompts.value = [];
    if (isOpen.value) {
        loadPrompts();
    }
});

// Preload on mount if there's a value
onMounted(() => {
    if (props.modelValue) {
        loadPrompts();
    }
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <!-- Trigger Button -->
        <button
            type="button"
            :disabled="disabled"
            class="flex w-full items-center justify-between gap-2 rounded-lg border px-3 py-2 text-left text-sm transition-colors"
            :class="[
                disabled
                    ? 'cursor-not-allowed border-zinc-200 bg-zinc-50 text-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-500'
                    : isOpen
                      ? 'border-violet-500 bg-white ring-1 ring-violet-500 dark:border-violet-500 dark:bg-zinc-900'
                      : 'border-zinc-300 bg-white hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600',
            ]"
            @click="toggleDropdown"
        >
            <div class="flex items-center gap-2 truncate">
                <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                </svg>
                <span v-if="selectedPrompt" class="truncate text-zinc-900 dark:text-white">
                    {{ selectedPrompt.name }}
                </span>
                <span v-else class="truncate text-zinc-500 dark:text-zinc-400">
                    {{ placeholder }}
                </span>
            </div>
            <svg
                class="h-4 w-4 shrink-0 text-zinc-400 transition-transform"
                :class="{ 'rotate-180': isOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute left-0 right-0 z-50 mt-1 max-h-80 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
            >
                <!-- Search -->
                <div class="border-b border-zinc-200 p-2 dark:border-zinc-700">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search prompts..."
                        class="w-full rounded border-none bg-zinc-100 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-violet-500 dark:bg-zinc-800 dark:text-white"
                        @click.stop
                    />
                </div>

                <!-- Loading -->
                <div v-if="isLoading" class="flex items-center justify-center py-8">
                    <svg class="h-5 w-5 animate-spin text-violet-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Options -->
                <div v-else class="max-h-60 overflow-y-auto">
                    <!-- No prompt option -->
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                        @click="selectPrompt(null)"
                    >
                        <span class="italic">No prompt (default)</span>
                    </button>

                    <!-- System prompts -->
                    <div v-if="groupedPrompts.system.length > 0">
                        <div class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            System
                        </div>
                        <button
                            v-for="prompt in groupedPrompts.system"
                            :key="prompt.id"
                            type="button"
                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800"
                            :class="[
                                prompt.id === modelValue
                                    ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/30 dark:text-violet-300'
                                    : 'text-zinc-900 dark:text-white',
                            ]"
                            @click="selectPrompt(prompt)"
                        >
                            <div class="flex-1 truncate">
                                <div class="font-medium">{{ prompt.name }}</div>
                                <div v-if="prompt.description" class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ prompt.description }}
                                </div>
                            </div>
                            <svg
                                v-if="prompt.id === modelValue"
                                class="h-4 w-4 shrink-0 text-violet-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- User prompts with folder structure -->
                    <div v-if="groupedPrompts.user.length > 0">
                        <div class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Custom
                        </div>
                        
                        <!-- Folder tree -->
                        <template v-for="node in userFolderTree" :key="node.path || '_user_root'">
                            <!-- Root-level prompts (no folder) -->
                            <template v-if="node.name === ''">
                                <button
                                    v-for="prompt in node.prompts"
                                    :key="prompt.id"
                                    type="button"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                    :class="[
                                        prompt.id === modelValue
                                            ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/30 dark:text-violet-300'
                                            : 'text-zinc-900 dark:text-white',
                                    ]"
                                    @click="selectPrompt(prompt)"
                                >
                                    <div class="flex-1 truncate">
                                        <div class="font-medium">{{ getPromptDisplayName(prompt) }}</div>
                                        <div v-if="prompt.description" class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ prompt.description }}
                                        </div>
                                    </div>
                                    <svg
                                        v-if="prompt.id === modelValue"
                                        class="h-4 w-4 shrink-0 text-violet-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </template>

                            <!-- Folders -->
                            <div v-else class="folder-group">
                                <!-- Folder header -->
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                                    @click.stop="toggleFolder(node.path)"
                                >
                                    <svg
                                        class="h-3 w-3 shrink-0 text-zinc-400 transition-transform"
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                    <span class="flex-1 truncate font-medium">{{ node.name }}</span>
                                    <span class="text-xs text-zinc-400">{{ node.prompts.length + node.children.length }}</span>
                                </button>

                                <!-- Folder contents (expanded) -->
                                <div v-if="isExpanded(node.path)" class="ml-4 border-l border-zinc-200 pl-2 dark:border-zinc-700">
                                    <!-- Prompts in this folder -->
                                    <button
                                        v-for="prompt in node.prompts"
                                        :key="prompt.id"
                                        type="button"
                                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                        :class="[
                                            prompt.id === modelValue
                                                ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/30 dark:text-violet-300'
                                                : 'text-zinc-900 dark:text-white',
                                        ]"
                                        @click="selectPrompt(prompt)"
                                    >
                                        <div class="flex-1 truncate">
                                            <div class="font-medium">{{ getPromptDisplayName(prompt) }}</div>
                                            <div v-if="prompt.description" class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ prompt.description }}
                                            </div>
                                        </div>
                                        <svg
                                            v-if="prompt.id === modelValue"
                                            class="h-4 w-4 shrink-0 text-violet-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>

                                    <!-- Nested subfolders -->
                                    <template v-for="child in node.children" :key="child.path">
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                                            @click.stop="toggleFolder(child.path)"
                                        >
                                            <svg
                                                class="h-3 w-3 shrink-0 text-zinc-400 transition-transform"
                                                :class="{ 'rotate-90': isExpanded(child.path) }"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                            <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                            </svg>
                                            <span class="flex-1 truncate font-medium">{{ child.name }}</span>
                                            <span class="text-xs text-zinc-400">{{ child.prompts.length }}</span>
                                        </button>
                                        <div v-if="isExpanded(child.path)" class="ml-4 border-l border-zinc-200 pl-2 dark:border-zinc-700">
                                            <button
                                                v-for="prompt in child.prompts"
                                                :key="prompt.id"
                                                type="button"
                                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                                :class="[
                                                    prompt.id === modelValue
                                                        ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/30 dark:text-violet-300'
                                                        : 'text-zinc-900 dark:text-white',
                                                ]"
                                                @click="selectPrompt(prompt)"
                                            >
                                                <div class="flex-1 truncate">
                                                    <div class="font-medium">{{ getPromptDisplayName(prompt) }}</div>
                                                </div>
                                                <svg
                                                    v-if="prompt.id === modelValue"
                                                    class="h-4 w-4 shrink-0 text-violet-500"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="filteredPrompts.length === 0"
                        class="px-3 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        No prompts found
                    </div>
                </div>

                <!-- Footer link -->
                <div class="border-t border-zinc-200 p-2 dark:border-zinc-700">
                    <a
                        href="/prompts"
                        class="flex items-center justify-center gap-1 rounded px-3 py-1.5 text-xs text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                        @click.stop
                    >
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Manage Prompts
                    </a>
                </div>
            </div>
        </Transition>
    </div>
</template>
