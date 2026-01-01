<script setup lang="ts">
import { computed, onMounted, onUnmounted, watch } from 'vue';

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
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-modal="true"
                role="dialog"
            >
                <!-- Overlay -->
                <div
                    class="fixed inset-0 bg-black/50 backdrop-blur-sm dark:bg-black/70"
                    @click="handleOverlayClick"
                />

                <!-- Modal Container -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-4"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 translate-y-4"
                    >
                        <div v-if="isOpen" :class="modalClasses">
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
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
