<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    canUndo: boolean;
    canRedo: boolean;
    isBold: boolean;
    isItalic: boolean;
    isUnderline: boolean;
    isStrike: boolean;
    wordCount: number;
    saveStatus: 'saved' | 'saving' | 'unsaved' | 'error';
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'undo'): void;
    (e: 'redo'): void;
    (e: 'bold'): void;
    (e: 'italic'): void;
    (e: 'underline'): void;
    (e: 'strike'): void;
}>();

const saveStatusText = computed(() => {
    switch (props.saveStatus) {
        case 'saved': return 'Saved';
        case 'saving': return 'Saving...';
        case 'unsaved': return 'Unsaved';
        case 'error': return 'Error';
        default: return '';
    }
});

const saveStatusClass = computed(() => {
    switch (props.saveStatus) {
        case 'saved': return 'text-green-600 dark:text-green-400';
        case 'saving': return 'text-amber-600 dark:text-amber-400';
        case 'unsaved': return 'text-zinc-500 dark:text-zinc-400';
        case 'error': return 'text-red-600 dark:text-red-400';
        default: return '';
    }
});
</script>

<template>
    <div class="flex items-center justify-between gap-2 border-b border-zinc-200 bg-white/80 px-4 py-2 backdrop-blur-sm dark:border-zinc-700 dark:bg-zinc-900/80">
        <div class="flex items-center gap-1">
            <button type="button" :disabled="!canUndo" class="rounded-md p-2 text-zinc-600 transition-colors hover:bg-zinc-100 disabled:cursor-not-allowed disabled:opacity-40 dark:text-zinc-400 dark:hover:bg-zinc-800" title="Undo (Ctrl+Z)" @click="emit('undo')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
            </button>
            <button type="button" :disabled="!canRedo" class="rounded-md p-2 text-zinc-600 transition-colors hover:bg-zinc-100 disabled:cursor-not-allowed disabled:opacity-40 dark:text-zinc-400 dark:hover:bg-zinc-800" title="Redo (Ctrl+Y)" @click="emit('redo')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" /></svg>
            </button>
            <div class="mx-2 h-5 w-px bg-zinc-200 dark:bg-zinc-700" />
            <button type="button" :class="['rounded-md p-2 transition-colors', isBold ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800']" title="Bold (Ctrl+B)" @click="emit('bold')">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6zm0 8h9a4 4 0 014 4 4 4 0 01-4 4H6z" /></svg>
            </button>
            <button type="button" :class="['rounded-md p-2 transition-colors', isItalic ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800']" title="Italic (Ctrl+I)" @click="emit('italic')">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="4" x2="10" y2="4" /><line x1="14" y1="20" x2="5" y2="20" /><line x1="15" y1="4" x2="9" y2="20" /></svg>
            </button>
            <button type="button" :class="['rounded-md p-2 transition-colors', isUnderline ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800']" title="Underline (Ctrl+U)" @click="emit('underline')">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3" /><line x1="4" y1="21" x2="20" y2="21" /></svg>
            </button>
            <button type="button" :class="['rounded-md p-2 transition-colors', isStrike ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800']" title="Strikethrough" @click="emit('strike')">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4H9a3 3 0 00-3 3v1a3 3 0 003 3h6a3 3 0 013 3v1a3 3 0 01-3 3H6" /><line x1="4" y1="12" x2="20" y2="12" /></svg>
            </button>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <span class="text-zinc-500 dark:text-zinc-400">{{ wordCount.toLocaleString() }} words</span>
            <span :class="saveStatusClass" class="flex items-center gap-1.5">
                <svg v-if="saveStatus === 'saving'" class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                <svg v-else-if="saveStatus === 'saved'" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                {{ saveStatusText }}
            </span>
        </div>
    </div>
</template>
