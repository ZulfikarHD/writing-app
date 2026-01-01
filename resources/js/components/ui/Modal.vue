<script setup lang="ts">
import { computed, onMounted, onUnmounted, watch } from 'vue';

interface Props {
    modelValue: boolean;
    title?: string;
    description?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full';
    closable?: boolean;
    closeOnOverlay?: boolean;
    closeOnEscape?: boolean;
    persistent?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    closable: true,
    closeOnOverlay: true,
    closeOnEscape: true,
    persistent: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
    close: [];
}>();

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
        'relative w-full transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all dark:bg-zinc-900',
        sizeClasses[props.size],
    ].join(' ');
});

// Prevent body scroll when modal is open
watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
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
    if (props.modelValue) {
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
                v-if="modelValue"
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
                        <div v-if="modelValue" :class="modalClasses">
                            <!-- Header -->
                            <div
                                v-if="title || closable"
                                class="flex items-start justify-between border-b border-zinc-200 p-4 dark:border-zinc-700 sm:p-6"
                            >
                                <div v-if="title || description">
                                    <h3 v-if="title" class="text-lg font-semibold text-zinc-900 dark:text-white">
                                        {{ title }}
                                    </h3>
                                    <p v-if="description" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ description }}
                                    </p>
                                </div>
                                <button
                                    v-if="closable"
                                    type="button"
                                    class="-mr-1 -mt-1 ml-auto flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
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
                            <div class="p-4 sm:p-6">
                                <slot />
                            </div>

                            <!-- Footer -->
                            <div
                                v-if="$slots.footer"
                                class="flex items-center justify-end gap-3 border-t border-zinc-200 p-4 dark:border-zinc-700 sm:p-6"
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
