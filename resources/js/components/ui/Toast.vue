<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface Props {
    variant?: 'info' | 'success' | 'warning' | 'danger';
    title?: string;
    duration?: number;
    position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center';
    dismissible?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'info',
    duration: 5000,
    position: 'top-right',
    dismissible: true,
});

const emit = defineEmits<{
    close: [];
}>();

const isVisible = ref(true);
let timeout: ReturnType<typeof setTimeout> | null = null;

const variantClasses = {
    info: 'border-blue-200 bg-white dark:border-blue-800 dark:bg-zinc-900',
    success: 'border-emerald-200 bg-white dark:border-emerald-800 dark:bg-zinc-900',
    warning: 'border-amber-200 bg-white dark:border-amber-800 dark:bg-zinc-900',
    danger: 'border-red-200 bg-white dark:border-red-800 dark:bg-zinc-900',
};

const iconColors = {
    info: 'text-blue-500',
    success: 'text-emerald-500',
    warning: 'text-amber-500',
    danger: 'text-red-500',
};

const iconBgColors = {
    info: 'bg-blue-100 dark:bg-blue-900/30',
    success: 'bg-emerald-100 dark:bg-emerald-900/30',
    warning: 'bg-amber-100 dark:bg-amber-900/30',
    danger: 'bg-red-100 dark:bg-red-900/30',
};

const icons = {
    info: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />`,
    success: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />`,
    warning: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />`,
    danger: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />`,
};

const positionClasses = {
    'top-right': 'top-4 right-4',
    'top-left': 'top-4 left-4',
    'bottom-right': 'bottom-4 right-4',
    'bottom-left': 'bottom-4 left-4',
    'top-center': 'top-4 left-1/2 -translate-x-1/2',
    'bottom-center': 'bottom-4 left-1/2 -translate-x-1/2',
};

const close = () => {
    isVisible.value = false;
    setTimeout(() => {
        emit('close');
    }, 200);
};

const toastClasses = computed(() => {
    return [
        'pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg border shadow-lg',
        variantClasses[props.variant],
    ].join(' ');
});

onMounted(() => {
    if (props.duration > 0) {
        timeout = setTimeout(close, props.duration);
    }
});

onUnmounted(() => {
    if (timeout) {
        clearTimeout(timeout);
    }
});
</script>

<template>
    <Teleport to="body">
        <div :class="['pointer-events-none fixed z-50', positionClasses[position]]">
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-2 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 translate-y-2 scale-95"
            >
                <div v-if="isVisible" :class="toastClasses">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="shrink-0">
                                <div :class="['flex h-10 w-10 items-center justify-center rounded-full', iconBgColors[variant]]">
                                    <svg
                                        :class="['h-5 w-5', iconColors[variant]]"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        v-html="icons[variant]"
                                    />
                                </div>
                            </div>
                            <div class="ml-3 flex-1 pt-0.5">
                                <p v-if="title" class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ title }}
                                </p>
                                <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    <slot />
                                </div>
                            </div>
                            <div v-if="dismissible" class="ml-4 flex shrink-0">
                                <button
                                    type="button"
                                    class="inline-flex rounded-lg text-zinc-400 hover:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 dark:hover:text-zinc-300"
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
                        </div>
                    </div>
                    <!-- Progress bar -->
                    <div v-if="duration > 0" class="h-1 w-full bg-zinc-100 dark:bg-zinc-800">
                        <div
                            :class="['h-full', iconColors[variant].replace('text-', 'bg-')]"
                            :style="{
                                animation: `shrink ${duration}ms linear forwards`,
                            }"
                        />
                    </div>
                </div>
            </Transition>
        </div>
    </Teleport>
</template>

<style scoped>
@keyframes shrink {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}
</style>
