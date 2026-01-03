<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { SECTION_TYPES, SECTION_TYPE_COLORS } from '@/extensions/SectionNode';

interface Props {
    open: boolean;
    type: 'content' | 'note' | 'alternative' | 'beat';
    color: string;
    wordCount: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'change-type', type: 'content' | 'note' | 'alternative' | 'beat'): void;
    (e: 'change-color', color: string): void;
    (e: 'copy'): void;
    (e: 'dissolve'): void;
    (e: 'delete'): void;
}>();

const menuRef = ref<HTMLElement | null>(null);
const showColorPicker = ref(false);

const predefinedColors = [
    '#6366f1', // Indigo
    '#8b5cf6', // Violet
    '#ec4899', // Pink
    '#f43f5e', // Rose
    '#ef4444', // Red
    '#f97316', // Orange
    '#eab308', // Yellow
    '#22c55e', // Green
    '#14b8a6', // Teal
    '#06b6d4', // Cyan
    '#3b82f6', // Blue
    '#71717a', // Gray
];

const handleClickOutside = (event: MouseEvent) => {
    if (menuRef.value && !menuRef.value.contains(event.target as Node)) {
        emit('close');
    }
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        emit('close');
    }
};

const selectType = (type: 'content' | 'note' | 'alternative' | 'beat') => {
    emit('change-type', type);
    emit('close');
};

const selectColor = (color: string) => {
    emit('change-color', color);
    showColorPicker.value = false;
};

const handleCopy = () => {
    emit('copy');
    emit('close');
};

const handleDissolve = () => {
    emit('dissolve');
    emit('close');
};

const handleDelete = () => {
    emit('delete');
    emit('close');
};

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            document.addEventListener('click', handleClickOutside);
            document.addEventListener('keydown', handleKeydown);
        } else {
            document.removeEventListener('click', handleClickOutside);
            document.removeEventListener('keydown', handleKeydown);
            showColorPicker.value = false;
        }
    }
);

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="open"
                ref="menuRef"
                class="fixed z-50 mt-1 w-56 rounded-lg bg-white dark:bg-zinc-800 shadow-lg ring-1 ring-black ring-opacity-5 divide-y divide-zinc-100 dark:divide-zinc-700"
                style="top: var(--menu-top, 100px); right: 20px;"
            >
                <!-- Word Count -->
                <div class="px-4 py-2">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ wordCount }} {{ wordCount === 1 ? 'word' : 'words' }}
                    </p>
                </div>

                <!-- Section Type -->
                <div class="py-1">
                    <p class="px-4 py-1 text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">
                        Section Type
                    </p>
                    <button
                        v-for="(label, typeKey) in SECTION_TYPES"
                        :key="typeKey"
                        type="button"
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700"
                        :class="{ 'bg-zinc-50 dark:bg-zinc-700/50': type === typeKey }"
                        @click="selectType(typeKey as 'content' | 'note' | 'alternative' | 'beat')"
                    >
                        <span
                            class="w-3 h-3 rounded-full"
                            :style="{ backgroundColor: SECTION_TYPE_COLORS[typeKey] }"
                        ></span>
                        <span class="text-zinc-700 dark:text-zinc-200">{{ label }}</span>
                        <svg
                            v-if="type === typeKey"
                            class="ml-auto w-4 h-4 text-indigo-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>

                <!-- Color Picker -->
                <div class="py-1">
                    <button
                        type="button"
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700"
                        @click="showColorPicker = !showColorPicker"
                    >
                        <span
                            class="w-4 h-4 rounded-full border border-zinc-300 dark:border-zinc-600"
                            :style="{ backgroundColor: color }"
                        ></span>
                        <span class="text-zinc-700 dark:text-zinc-200">Change Color</span>
                        <svg
                            class="ml-auto w-4 h-4 text-zinc-400 transition-transform"
                            :class="{ 'rotate-180': showColorPicker }"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-if="showColorPicker" class="px-4 py-2">
                        <div class="grid grid-cols-6 gap-1">
                            <button
                                v-for="c in predefinedColors"
                                :key="c"
                                type="button"
                                class="w-6 h-6 rounded-full border-2 transition-transform hover:scale-110"
                                :class="color === c ? 'border-zinc-900 dark:border-white' : 'border-transparent'"
                                :style="{ backgroundColor: c }"
                                @click="selectColor(c)"
                            ></button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="py-1">
                    <button
                        type="button"
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                        @click="handleCopy"
                    >
                        <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Copy Text
                    </button>
                    <button
                        type="button"
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                        @click="handleDissolve"
                    >
                        <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        Dissolve Section
                    </button>
                </div>

                <!-- Danger Zone -->
                <div class="py-1">
                    <button
                        type="button"
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                        @click="handleDelete"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Section
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
