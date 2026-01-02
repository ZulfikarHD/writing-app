<script setup lang="ts">
import { Motion } from 'motion-v';
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { usePerformanceMode } from '@/composables/usePerformanceMode';

interface Props {
    modelValue?: boolean;
    show?: boolean; // Alias for modelValue (backwards compatibility)
    title?: string;
    description?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full';
    closable?: boolean;
    closeOnOverlay?: boolean;
    closeOnEscape?: boolean;
    persistent?: boolean;
    scrollable?: boolean; // Enable scrollable body with max-height
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
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
    close: [];
}>();

// Performance mode
const { isReducedMotion, backdropBlurClass, springConfig } = usePerformanceMode();

// Support both :show and v-model
const isOpen = computed(() => props.modelValue ?? props.show ?? false);

const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
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

const modalClasses = computed(() => {
    return [
        'relative w-full transform rounded-2xl bg-white text-left shadow-2xl ring-1 ring-zinc-900/5 transition-all dark:bg-zinc-900 dark:ring-white/10',
        sizeClasses[props.size],
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
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-modal="true"
            role="dialog"
        >
            <!-- Overlay -->
            <Motion
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :exit="{ opacity: 0 }"
                :transition="{ duration: 0.15 }"
                :class="['fixed inset-0 bg-black/50 dark:bg-black/70', backdropBlurClass]"
                @click="handleOverlayClick"
            />

            <!-- Modal Container -->
            <div class="flex min-h-full items-center justify-center p-4">
                <Motion
                    :initial="isReducedMotion ? { opacity: 0 } : { opacity: 0, scale: 0.97, y: 16 }"
                    :animate="isReducedMotion ? { opacity: 1 } : { opacity: 1, scale: 1, y: 0 }"
                    :exit="isReducedMotion ? { opacity: 0 } : { opacity: 0, scale: 0.97, y: 16 }"
                    :transition="isReducedMotion ? { duration: 0.15 } : springConfig"
                    :class="modalClasses"
                >
                            <!-- Header -->
                            <div
                                v-if="title || $slots.header || closable"
                                class="flex shrink-0 items-center justify-between gap-4 border-b border-zinc-200 bg-zinc-50/80 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-800/50"
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
                                <button
                                    v-if="closable"
                                    type="button"
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-zinc-400 transition-all hover:bg-zinc-200 hover:text-zinc-600 active:scale-95 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                                    @click="close"
                                >
                                    <span class="sr-only">Close</span>
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div :class="[
                                'p-5',
                                scrollable ? 'flex-1 overflow-y-auto' : ''
                            ]">
                                <slot />
                            </div>

                            <!-- Footer -->
                    <div
                        v-if="$slots.footer"
                        class="flex shrink-0 items-center justify-end gap-3 border-t border-zinc-200 bg-zinc-50/80 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-800/50"
                    >
                        <slot name="footer" />
                    </div>
                </Motion>
            </div>
        </Motion>
    </Teleport>
</template>
