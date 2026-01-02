<script setup lang="ts">
import { Motion } from 'motion-v';
import { ref, computed } from 'vue';
import { usePerformanceMode } from '@/composables/usePerformanceMode';

interface Props {
    canUndo: boolean;
    canRedo: boolean;
    isBold: boolean;
    isItalic: boolean;
    isUnderline: boolean;
    isStrike: boolean;
    isBulletList: boolean;
    isOrderedList: boolean;
    currentHeadingLevel: number;
    currentTextAlign: 'left' | 'center' | 'right' | 'justify';
    wordCount: number;
    saveStatus: 'saved' | 'saving' | 'unsaved' | 'error';
}

const props = withDefaults(defineProps<Props>(), {
    isBulletList: false,
    isOrderedList: false,
    currentHeadingLevel: 0,
    currentTextAlign: 'left',
});

const emit = defineEmits<{
    (e: 'undo'): void;
    (e: 'redo'): void;
    (e: 'bold'): void;
    (e: 'italic'): void;
    (e: 'underline'): void;
    (e: 'strike'): void;
    (e: 'heading', level: 0 | 1 | 2 | 3): void;
    (e: 'bulletList'): void;
    (e: 'orderedList'): void;
    (e: 'align', alignment: 'left' | 'center' | 'right' | 'justify'): void;
    (e: 'openSettings'): void;
    (e: 'openInfo'): void;
    (e: 'chatWithScene'): void;
}>();

// Performance mode
const { backdropBlurClass } = usePerformanceMode();

const headingDropdownOpen = ref(false);
const alignDropdownOpen = ref(false);

const headingOptions = [
    { level: 0 as const, label: 'Paragraph', shortcut: '' },
    { level: 1 as const, label: 'Heading 1', shortcut: '' },
    { level: 2 as const, label: 'Heading 2', shortcut: '' },
    { level: 3 as const, label: 'Heading 3', shortcut: '' },
];

const alignOptions = [
    { value: 'left' as const, label: 'Left', icon: 'alignLeft' },
    { value: 'center' as const, label: 'Center', icon: 'alignCenter' },
    { value: 'right' as const, label: 'Right', icon: 'alignRight' },
    { value: 'justify' as const, label: 'Justify', icon: 'alignJustify' },
];

const currentHeadingLabel = computed(() => {
    const option = headingOptions.find((o) => o.level === props.currentHeadingLevel);
    return option?.label || 'Paragraph';
});

const selectHeading = (level: 0 | 1 | 2 | 3) => {
    emit('heading', level);
    headingDropdownOpen.value = false;
};

const selectAlign = (align: 'left' | 'center' | 'right' | 'justify') => {
    emit('align', align);
    alignDropdownOpen.value = false;
};

const saveStatusText = computed(() => {
    switch (props.saveStatus) {
        case 'saved':
            return 'Saved';
        case 'saving':
            return 'Saving...';
        case 'unsaved':
            return 'Unsaved';
        case 'error':
            return 'Error';
        default:
            return '';
    }
});

const saveStatusClass = computed(() => {
    switch (props.saveStatus) {
        case 'saved':
            return 'text-green-600 dark:text-green-400';
        case 'saving':
            return 'text-amber-600 dark:text-amber-400';
        case 'unsaved':
            return 'text-zinc-500 dark:text-zinc-400';
        case 'error':
            return 'text-red-600 dark:text-red-400';
        default:
            return '';
    }
});

const closeDropdowns = () => {
    headingDropdownOpen.value = false;
    alignDropdownOpen.value = false;
};
</script>

<template>
    <div
        :class="['flex flex-wrap items-center justify-between gap-2 border-b border-zinc-200 bg-white/80 px-2 py-1.5 dark:border-zinc-700 dark:bg-zinc-900/80 sm:px-4 sm:py-2', backdropBlurClass]"
        @click.self="closeDropdowns"
    >
        <!-- Left side: formatting controls -->
        <div class="flex flex-wrap items-center gap-0.5 sm:gap-1">
            <!-- Undo/Redo -->
            <button
                type="button"
                :disabled="!canUndo"
                class="rounded-md p-1.5 text-zinc-600 transition-all hover:bg-zinc-100 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40 dark:text-zinc-400 dark:hover:bg-zinc-800 sm:p-2"
                title="Undo (Ctrl+Z)"
                @click="emit('undo')"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
            </button>
            <button
                type="button"
                :disabled="!canRedo"
                class="rounded-md p-1.5 text-zinc-600 transition-all hover:bg-zinc-100 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40 dark:text-zinc-400 dark:hover:bg-zinc-800 sm:p-2"
                title="Redo (Ctrl+Y)"
                @click="emit('redo')"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" />
                </svg>
            </button>

            <div class="mx-1 h-5 w-px bg-zinc-200 dark:bg-zinc-700 sm:mx-2" />

            <!-- Heading Dropdown -->
            <div class="relative">
                <button
                    type="button"
                    class="flex items-center gap-1 rounded-md px-2 py-1.5 text-sm text-zinc-600 transition-all hover:bg-zinc-100 active:scale-95 dark:text-zinc-400 dark:hover:bg-zinc-800"
                    @click="headingDropdownOpen = !headingDropdownOpen; alignDropdownOpen = false"
                >
                    <span class="hidden min-w-[70px] text-left sm:inline">{{ currentHeadingLabel }}</span>
                    <span class="inline sm:hidden">{{ currentHeadingLevel === 0 ? 'P' : `H${currentHeadingLevel}` }}</span>
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <Motion
                    v-if="headingDropdownOpen"
                    :initial="{ opacity: 0, scale: 0.95, y: -8 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :exit="{ opacity: 0, scale: 0.95, y: -8 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 35, duration: 0.15 }"
                    class="absolute left-0 top-full z-20 mt-1 w-36 rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                >
                    <button
                        v-for="option in headingOptions"
                        :key="option.level"
                        type="button"
                        :class="[
                            'flex w-full items-center px-3 py-1.5 text-left text-sm transition-colors',
                            currentHeadingLevel === option.level
                                ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300'
                                : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700',
                        ]"
                        @click="selectHeading(option.level)"
                    >
                        {{ option.label }}
                    </button>
                </Motion>
            </div>

            <div class="mx-1 h-5 w-px bg-zinc-200 dark:bg-zinc-700 sm:mx-2" />

            <!-- Basic formatting -->
            <button
                type="button"
                :class="[
                    'rounded-md p-1.5 transition-all active:scale-95 sm:p-2',
                    isBold
                        ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300'
                        : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
                ]"
                title="Bold (Ctrl+B)"
                @click="emit('bold')"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6zm0 8h9a4 4 0 014 4 4 4 0 01-4 4H6z" />
                </svg>
            </button>
            <button
                type="button"
                :class="[
                    'rounded-md p-1.5 transition-all active:scale-95 sm:p-2',
                    isItalic
                        ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300'
                        : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
                ]"
                title="Italic (Ctrl+I)"
                @click="emit('italic')"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="4" x2="10" y2="4" />
                    <line x1="14" y1="20" x2="5" y2="20" />
                    <line x1="15" y1="4" x2="9" y2="20" />
                </svg>
            </button>
            <button
                type="button"
                :class="[
                    'rounded-md p-1.5 transition-all active:scale-95 sm:p-2',
                    isUnderline
                        ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300'
                        : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
                ]"
                title="Underline (Ctrl+U)"
                @click="emit('underline')"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3" />
                    <line x1="4" y1="21" x2="20" y2="21" />
                </svg>
            </button>
            <button
                type="button"
                :class="[
                    'hidden rounded-md p-1.5 transition-all active:scale-95 sm:block sm:p-2',
                    isStrike
                        ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300'
                        : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
                ]"
                title="Strikethrough"
                @click="emit('strike')"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 4H9a3 3 0 00-3 3v1a3 3 0 003 3h6a3 3 0 013 3v1a3 3 0 01-3 3H6" />
                    <line x1="4" y1="12" x2="20" y2="12" />
                </svg>
            </button>

            <div class="mx-1 h-5 w-px bg-zinc-200 dark:bg-zinc-700 sm:mx-2" />

            <!-- Lists -->
            <button
                type="button"
                :class="[
                    'rounded-md p-1.5 transition-all active:scale-95 sm:p-2',
                    isBulletList
                        ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300'
                        : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
                ]"
                title="Bullet List"
                @click="emit('bulletList')"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6" />
                    <line x1="8" y1="12" x2="21" y2="12" />
                    <line x1="8" y1="18" x2="21" y2="18" />
                    <circle cx="4" cy="6" r="1" fill="currentColor" />
                    <circle cx="4" cy="12" r="1" fill="currentColor" />
                    <circle cx="4" cy="18" r="1" fill="currentColor" />
                </svg>
            </button>
            <button
                type="button"
                :class="[
                    'rounded-md p-1.5 transition-all active:scale-95 sm:p-2',
                    isOrderedList
                        ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300'
                        : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
                ]"
                title="Numbered List"
                @click="emit('orderedList')"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="10" y1="6" x2="21" y2="6" />
                    <line x1="10" y1="12" x2="21" y2="12" />
                    <line x1="10" y1="18" x2="21" y2="18" />
                    <text x="3" y="8" font-size="8" fill="currentColor" stroke="none">1</text>
                    <text x="3" y="14" font-size="8" fill="currentColor" stroke="none">2</text>
                    <text x="3" y="20" font-size="8" fill="currentColor" stroke="none">3</text>
                </svg>
            </button>

            <div class="hidden h-5 w-px bg-zinc-200 dark:bg-zinc-700 sm:mx-2 sm:block" />

            <!-- Alignment Dropdown -->
            <div class="relative hidden sm:block">
                <button
                    type="button"
                    class="flex items-center gap-1 rounded-md p-2 text-zinc-600 transition-all hover:bg-zinc-100 active:scale-95 dark:text-zinc-400 dark:hover:bg-zinc-800"
                    title="Text Alignment"
                    @click="alignDropdownOpen = !alignDropdownOpen; headingDropdownOpen = false"
                >
                    <!-- Current alignment icon -->
                    <svg v-if="currentTextAlign === 'left'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="15" y2="12" />
                        <line x1="3" y1="18" x2="18" y2="18" />
                    </svg>
                    <svg v-else-if="currentTextAlign === 'center'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="6" y1="12" x2="18" y2="12" />
                        <line x1="5" y1="18" x2="19" y2="18" />
                    </svg>
                    <svg v-else-if="currentTextAlign === 'right'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="9" y1="12" x2="21" y2="12" />
                        <line x1="6" y1="18" x2="21" y2="18" />
                    </svg>
                    <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <Motion
                    v-if="alignDropdownOpen"
                    :initial="{ opacity: 0, scale: 0.95, y: -8 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :exit="{ opacity: 0, scale: 0.95, y: -8 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 35, duration: 0.15 }"
                    class="absolute left-0 top-full z-20 mt-1 flex gap-1 rounded-lg border border-zinc-200 bg-white p-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                >
                    <button
                        v-for="option in alignOptions"
                        :key="option.value"
                        type="button"
                        :class="[
                            'rounded-md p-2 transition-colors',
                            currentTextAlign === option.value
                                ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300'
                                : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700',
                        ]"
                        :title="option.label"
                        @click="selectAlign(option.value)"
                    >
                        <svg v-if="option.value === 'left'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <line x1="3" y1="12" x2="15" y2="12" />
                            <line x1="3" y1="18" x2="18" y2="18" />
                        </svg>
                        <svg v-else-if="option.value === 'center'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <line x1="6" y1="12" x2="18" y2="12" />
                            <line x1="5" y1="18" x2="19" y2="18" />
                        </svg>
                        <svg v-else-if="option.value === 'right'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <line x1="9" y1="12" x2="21" y2="12" />
                            <line x1="6" y1="18" x2="21" y2="18" />
                        </svg>
                        <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <line x1="3" y1="12" x2="21" y2="12" />
                            <line x1="3" y1="18" x2="21" y2="18" />
                        </svg>
                    </button>
                </Motion>
            </div>
        </div>

        <!-- Right side: info and settings -->
        <div class="flex items-center gap-2 sm:gap-4">
            <span class="hidden text-sm text-zinc-500 dark:text-zinc-400 sm:inline">{{ wordCount.toLocaleString() }} words</span>
            <span :class="saveStatusClass" class="flex items-center gap-1.5 text-sm">
                <svg v-if="saveStatus === 'saving'" class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <svg v-else-if="saveStatus === 'saved'" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                {{ saveStatusText }}
            </span>

            <!-- Chat with Scene button -->
            <button
                type="button"
                class="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-zinc-600 transition-all hover:bg-violet-50 hover:text-violet-700 active:scale-95 dark:text-zinc-400 dark:hover:bg-violet-900/20 dark:hover:text-violet-300 sm:px-3"
                title="Chat with this scene"
                @click="emit('chatWithScene')"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <span class="hidden text-sm font-medium sm:inline">Chat</span>
            </button>

            <!-- Info button -->
            <button
                type="button"
                class="rounded-md p-1.5 text-zinc-600 transition-all hover:bg-zinc-100 active:scale-95 dark:text-zinc-400 dark:hover:bg-zinc-800 sm:p-2"
                title="Scene Info (Ctrl+I)"
                @click="emit('openInfo')"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>

            <!-- Settings button -->
            <button
                type="button"
                class="rounded-md p-1.5 text-zinc-600 transition-all hover:bg-zinc-100 active:scale-95 dark:text-zinc-400 dark:hover:bg-zinc-800 sm:p-2"
                title="Editor Settings"
                @click="emit('openSettings')"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                    />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Click outside to close dropdowns -->
    <div v-if="headingDropdownOpen || alignDropdownOpen" class="fixed inset-0 z-10" @click="closeDropdowns" />
</template>
