<script setup lang="ts">
/**
 * SelectionActionMenu - Floating menu for text selection actions
 *
 * Sprint 15 (US-12.11): On mobile devices, provides a floating button
 * that appears when text is selected, allowing users to quickly create
 * codex entries from their selection.
 *
 * Works on both desktop (context menu alternative) and mobile (tap).
 */
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps<{
    containerRef: HTMLElement | null;
}>();

const emit = defineEmits<{
    (e: 'create-entry', selectedText: string): void;
}>();

// State
const visible = ref(false);
const position = ref({ x: 0, y: 0 });
const selectedText = ref('');

// Calculate position for the menu
const menuStyle = computed(() => ({
    position: 'fixed' as const,
    left: `${position.value.x}px`,
    top: `${position.value.y}px`,
    transform: 'translateX(-50%)',
    zIndex: 100,
}));

// Check if there's selected text
const checkSelection = () => {
    const selection = window.getSelection();
    if (!selection || selection.isCollapsed) {
        visible.value = false;
        selectedText.value = '';
        return;
    }

    const text = selection.toString().trim();
    if (!text || text.length === 0) {
        visible.value = false;
        selectedText.value = '';
        return;
    }

    // Check if selection is within our container
    if (props.containerRef) {
        const range = selection.getRangeAt(0);
        const container = props.containerRef;
        if (!container.contains(range.commonAncestorContainer)) {
            visible.value = false;
            return;
        }
    }

    selectedText.value = text;

    // Position the menu above the selection
    const range = selection.getRangeAt(0);
    const rect = range.getBoundingClientRect();

    position.value = {
        x: rect.left + rect.width / 2,
        y: rect.top - 10, // 10px above selection
    };

    visible.value = true;
};

// Handle creating entry
const handleCreateEntry = () => {
    if (selectedText.value) {
        emit('create-entry', selectedText.value);
        visible.value = false;
    }
};

// Debounced selection change handler
let selectionTimeout: ReturnType<typeof setTimeout> | null = null;

const handleSelectionChange = () => {
    if (selectionTimeout) {
        clearTimeout(selectionTimeout);
    }
    selectionTimeout = setTimeout(checkSelection, 100);
};

// Handle clicks outside to hide menu
const handleClickOutside = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (!target.closest('[data-selection-menu]')) {
        // Small delay to allow button click to process
        setTimeout(() => {
            if (!window.getSelection()?.toString().trim()) {
                visible.value = false;
            }
        }, 10);
    }
};

onMounted(() => {
    document.addEventListener('selectionchange', handleSelectionChange);
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('selectionchange', handleSelectionChange);
    document.removeEventListener('mousedown', handleClickOutside);
    if (selectionTimeout) {
        clearTimeout(selectionTimeout);
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 scale-90 translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-90"
        >
            <div
                v-if="visible && selectedText"
                data-selection-menu
                :style="menuStyle"
                class="flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2 py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
            >
                <button
                    type="button"
                    class="flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium text-violet-600 transition-colors hover:bg-violet-50 dark:text-violet-400 dark:hover:bg-violet-900/30"
                    @click="handleCreateEntry"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Create Codex Entry</span>
                    <span class="sm:hidden">Codex</span>
                </button>
            </div>
        </Transition>
    </Teleport>
</template>
