<script setup lang="ts">
import { watch, onUnmounted } from 'vue';
import { useEditorSettings, type FontFamily, type EditorWidth } from '@/composables/useEditorSettings';
import { useTheme } from '@/composables/useTheme';
import { usePerformanceMode } from '@/composables/usePerformanceMode';
import { Motion, AnimatePresence } from 'motion-v';

interface Props {
    open: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
}>();

const { settings, setFontFamily, setFontSize, setLineHeight, setEditorWidth, resetToDefaults, fontFamilyOptions, fontSizeOptions, lineHeightOptions, editorWidthOptions } =
    useEditorSettings();

const { theme, setTheme } = useTheme();

const { settings: perfSettings, setMode, backdropBlurClass } = usePerformanceMode();

const themeOptions = [
    { value: 'light' as const, label: 'Light' },
    { value: 'dark' as const, label: 'Dark' },
    { value: 'system' as const, label: 'System' },
];

// Spring animation config (iOS-like)
const springConfig = {
    type: 'spring' as const,
    stiffness: 400,
    damping: 30,
};

// Close on escape key
let escapeHandler: ((e: KeyboardEvent) => void) | null = null;

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            escapeHandler = (e: KeyboardEvent) => {
                if (e.key === 'Escape') {
                    emit('close');
                }
            };
            document.addEventListener('keydown', escapeHandler);
        } else if (escapeHandler) {
            document.removeEventListener('keydown', escapeHandler);
            escapeHandler = null;
        }
    },
    { immediate: true }
);

onUnmounted(() => {
    if (escapeHandler) {
        document.removeEventListener('keydown', escapeHandler);
    }
});
</script>

<template>
    <AnimatePresence>
        <!-- Backdrop -->
        <Motion
            v-if="open"
            :initial="{ opacity: 0 }"
            :animate="{ opacity: 1 }"
            :exit="{ opacity: 0 }"
            :transition="{ duration: 0.2 }"
            :class="['fixed inset-0 z-40 bg-black/20 dark:bg-black/40', backdropBlurClass]"
            @click="emit('close')"
        />
    </AnimatePresence>

    <AnimatePresence>
        <!-- Panel -->
        <Motion
            v-if="open"
            :initial="{ x: '100%' }"
            :animate="{ x: 0 }"
            :exit="{ x: '100%' }"
            :transition="springConfig"
            class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto border-l border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900 sm:max-w-xs"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Editor Settings</h2>
                <button
                    type="button"
                    class="rounded-md p-2 text-zinc-500 transition-all hover:bg-zinc-100 active:scale-95 dark:text-zinc-400 dark:hover:bg-zinc-800"
                    @click="emit('close')"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="space-y-6 p-4">
                <!-- Performance Mode -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Performance</label>
                    <p class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                        Reduced mode disables heavy animations for better performance on lower-spec devices.
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            :class="[
                                'flex flex-col items-center gap-1 rounded-lg border px-3 py-2.5 text-sm font-medium transition-colors',
                                perfSettings.mode === 'full'
                                    ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
                            ]"
                            @click="setMode('full')"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            <span>Full</span>
                        </button>
                        <button
                            type="button"
                            :class="[
                                'flex flex-col items-center gap-1 rounded-lg border px-3 py-2.5 text-sm font-medium transition-colors',
                                perfSettings.mode === 'reduced'
                                    ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
                            ]"
                            @click="setMode('reduced')"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Reduced</span>
                        </button>
                    </div>
                </div>

                <!-- Theme -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Theme</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="option in themeOptions"
                            :key="option.value"
                            type="button"
                            :class="[
                                'rounded-lg border px-3 py-2 text-sm font-medium transition-all active:scale-95',
                                theme === option.value
                                    ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
                            ]"
                            @click="setTheme(option.value)"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </div>

                <!-- Font Family -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Font Family</label>
                    <div class="space-y-2">
                        <button
                            v-for="option in fontFamilyOptions"
                            :key="option.value"
                            type="button"
                            :class="[
                                'flex w-full items-center justify-between rounded-lg border px-3 py-2.5 text-left text-sm transition-all active:scale-[0.98]',
                                settings.fontFamily === option.value
                                    ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
                            ]"
                            @click="setFontFamily(option.value as FontFamily)"
                        >
                            <span>{{ option.label }}</span>
                            <svg
                                v-if="settings.fontFamily === option.value"
                                class="h-4 w-4 text-violet-600 dark:text-violet-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Font Size -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Font Size
                        <span class="ml-2 text-zinc-500 dark:text-zinc-400">{{ settings.fontSize }}px</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="size in fontSizeOptions"
                            :key="size"
                            type="button"
                            :class="[
                                'rounded-lg border px-3 py-1.5 text-sm font-medium transition-all active:scale-95',
                                settings.fontSize === size
                                    ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
                            ]"
                            @click="setFontSize(size)"
                        >
                            {{ size }}
                        </button>
                    </div>
                </div>

                <!-- Line Height -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Line Height
                        <span class="ml-2 text-zinc-500 dark:text-zinc-400">{{ settings.lineHeight }}</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="height in lineHeightOptions"
                            :key="height"
                            type="button"
                            :class="[
                                'rounded-lg border px-3 py-1.5 text-sm font-medium transition-all active:scale-95',
                                settings.lineHeight === height
                                    ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
                            ]"
                            @click="setLineHeight(height)"
                        >
                            {{ height }}
                        </button>
                    </div>
                </div>

                <!-- Editor Width -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Editor Width</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="option in editorWidthOptions"
                            :key="option.value"
                            type="button"
                            :class="[
                                'rounded-lg border px-3 py-2 text-sm font-medium transition-all active:scale-95',
                                settings.editorWidth === option.value
                                    ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
                            ]"
                            @click="setEditorWidth(option.value as EditorWidth)"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </div>

                <!-- Reset Button -->
                <div class="pt-4">
                    <button
                        type="button"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-all hover:bg-zinc-50 active:scale-[0.98] dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        @click="resetToDefaults"
                    >
                        Reset to Defaults
                    </button>
                </div>
            </div>
        </Motion>
    </AnimatePresence>
</template>
