<script setup lang="ts">
import { Motion } from 'motion-v';
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { usePerformanceMode } from '@/composables/usePerformanceMode';

interface Props {
    modelValue?: boolean;
    show?: boolean; // Alias for modelValue (backwards compatibility)
    title?: string;
    description?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | 'full';
    closable?: boolean;
    closeOnOverlay?: boolean;
    closeOnEscape?: boolean;
    persistent?: boolean;
    scrollable?: boolean;
    zIndex?: string;
    /** Hide the header section completely */
    hideHeader?: boolean;
    /** Hide the footer border for cleaner look */
    seamlessFooter?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: undefined,
    show: undefined,
    size: 'md',
    closable: true,
    closeOnOverlay: true,
    closeOnEscape: true,
    persistent: false,
    scrollable: false,
    zIndex: 'z-50',
    hideHeader: false,
    seamlessFooter: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
    close: [];
}>();

// Performance mode with modal-specific spring
const { isReducedMotion, backdropBlurClass, modalSpringConfig } = usePerformanceMode();

// Support both :show and v-model
const isOpen = computed(() => props.modelValue ?? props.show ?? false);

// Extended size classes with 2xl and 3xl
const sizeClasses: Record<string, string> = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    '3xl': 'max-w-3xl',
    full: 'max-w-4xl',
};

const close = () => {
    if (props.closable) {
        emit('update:modelValue', false);
        emit('close');
    }
};

const handleOverlayClick = () => {
    if (props.closeOnOverlay && !props.persistent) {
        close();
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.closeOnEscape && !props.persistent) {
        close();
    }
};

// Modal container classes - enhanced design with depth
const modalClasses = computed(() => {
    return [
        // Base styles
        'relative w-full transform rounded-2xl text-left',
        // Enhanced shadow for depth
        'shadow-xl shadow-black/10 dark:shadow-black/30',
        // Light mode: warm white, Dark mode: rich dark
        'bg-white dark:bg-zinc-900',
        // Refined border
        'ring-1 ring-zinc-200/80 dark:ring-zinc-700/50',
        // Size
        sizeClasses[props.size],
        // Scrollable handling
        props.scrollable ? 'flex max-h-[90vh] flex-col' : 'overflow-hidden',
    ].join(' ');
});

// Prevent body scroll when modal is open
watch(
    isOpen,
    (open) => {
        if (open) {
            document.body.style.overflow = 'hidden';
            document.addEventListener('keydown', handleKeydown);
        } else {
            document.body.style.overflow = '';
            document.removeEventListener('keydown', handleKeydown);
        }
    },
    { immediate: true }
);

onMounted(() => {
    if (isOpen.value) {
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', handleKeydown);
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Motion
            v-if="isOpen"
            :initial="{ opacity: 0 }"
            :animate="{ opacity: 1 }"
            :exit="{ opacity: 0 }"
            :transition="{ duration: 0.2 }"
            :class="['fixed inset-0 overflow-y-auto', zIndex]"
            aria-modal="true"
            role="dialog"
        >
            <!-- Overlay with blur -->
            <Motion
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :exit="{ opacity: 0 }"
                :transition="{ duration: 0.15 }"
                :class="[
                    'fixed inset-0 bg-black/40 dark:bg-black/60',
                    backdropBlurClass,
                ]"
                @click="handleOverlayClick"
            />

            <!-- Modal Container -->
            <div class="flex min-h-full items-center justify-center p-4">
                <Motion
                    :initial="isReducedMotion ? { opacity: 0 } : { opacity: 0, scale: 0.95, y: 20 }"
                    :animate="isReducedMotion ? { opacity: 1 } : { opacity: 1, scale: 1, y: 0 }"
                    :exit="isReducedMotion ? { opacity: 0 } : { opacity: 0, scale: 0.95, y: 10 }"
                    :transition="modalSpringConfig"
                    :class="modalClasses"
                >
                    <!-- Header with subtle gradient background -->
                    <div
                        v-if="!hideHeader && (title || $slots.header || closable)"
                        class="flex shrink-0 items-center justify-between gap-4 rounded-t-2xl border-b border-zinc-100 bg-gradient-to-b from-zinc-50/80 to-white px-5 py-4 dark:border-zinc-800 dark:from-zinc-800/50 dark:to-zinc-900"
                    >
                        <div v-if="$slots.header" class="min-w-0 flex-1">
                            <slot name="header" />
                        </div>
                        <div v-else-if="title || description" class="min-w-0 flex-1">
                            <h3 v-if="title" class="text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ title }}
                            </h3>
                            <p v-if="description" class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ description }}
                            </p>
                        </div>
                        <!-- Close button - iOS style with subtle background -->
                        <button
                            v-if="closable"
                            type="button"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 transition-all hover:bg-zinc-200 hover:text-zinc-700 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 focus-visible:ring-offset-2 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                            @click="close"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div :class="[
                        'p-5',
                        scrollable ? 'flex-1 overflow-y-auto' : '',
                    ]">
                        <slot />
                    </div>

                    <!-- Footer with subtle gradient background -->
                    <div
                        v-if="$slots.footer"
                        :class="[
                            'flex shrink-0 items-center justify-end gap-3 rounded-b-2xl px-5 py-4',
                            seamlessFooter
                                ? ''
                                : 'border-t border-zinc-100 bg-gradient-to-t from-zinc-50/80 to-white dark:border-zinc-800 dark:from-zinc-800/50 dark:to-zinc-900',
                        ]"
                    >
                        <slot name="footer" />
                    </div>
                </Motion>
            </div>
        </Motion>
    </Teleport>
</template>
