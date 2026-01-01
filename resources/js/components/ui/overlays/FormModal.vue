<script setup lang="ts">
import { ref, watch, computed, nextTick } from 'vue';
import { Motion } from 'motion-v';
import Button from '../buttons/Button.vue';
import Input from '../forms/Input.vue';

interface Props {
    modelValue: boolean;
    title?: string;
    description?: string;
    inputLabel?: string;
    inputPlaceholder?: string;
    initialValue?: string;
    submitText?: string;
    cancelText?: string;
    loading?: boolean;
    variant?: 'primary' | 'success' | 'warning' | 'danger';
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Enter Value',
    inputLabel: 'Name',
    inputPlaceholder: 'Enter name...',
    initialValue: '',
    submitText: 'Create',
    cancelText: 'Cancel',
    loading: false,
    variant: 'primary',
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
    submit: [value: string];
    cancel: [];
}>();

const inputValue = ref(props.initialValue);
const inputRef = ref<InstanceType<typeof Input> | null>(null);

const isValid = computed(() => inputValue.value.trim().length > 0);

watch(
    () => props.modelValue,
    async (isOpen) => {
        if (isOpen) {
            inputValue.value = props.initialValue;
            await nextTick();
            // Focus input when modal opens
            const inputEl = document.querySelector<HTMLInputElement>('[data-form-modal-input]');
            inputEl?.focus();
        }
    }
);

const handleSubmit = () => {
    if (!isValid.value || props.loading) return;
    emit('submit', inputValue.value.trim());
};

const handleCancel = () => {
    emit('cancel');
    emit('update:modelValue', false);
};

const close = () => {
    emit('update:modelValue', false);
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        handleCancel();
    }
};

// Prevent body scroll when modal is open
watch(
    () => props.modelValue,
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

const iconColors = {
    primary: 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400',
    success: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
    warning: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
    danger: 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
};
</script>

<template>
    <Teleport to="body">
        <Motion
            v-if="modelValue"
            :initial="{ opacity: 0 }"
            :animate="{ opacity: 1 }"
            :exit="{ opacity: 0 }"
            :transition="{ duration: 0.15 }"
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
                class="fixed inset-0 bg-black/50 backdrop-blur-sm dark:bg-black/70"
                @click="handleCancel"
            />

            <!-- Modal Container -->
            <div class="flex min-h-full items-center justify-center p-4">
                <Motion
                    :initial="{ opacity: 0, scale: 0.97, y: 16 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :exit="{ opacity: 0, scale: 0.97, y: 16 }"
                    :transition="{ type: 'spring', stiffness: 400, damping: 30, duration: 0.25 }"
                    class="relative w-full max-w-sm transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl ring-1 ring-zinc-900/5 dark:bg-zinc-900 dark:ring-white/10"
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between gap-4 border-b border-zinc-200 bg-zinc-50/80 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="flex items-center gap-3">
                            <div :class="['flex h-10 w-10 items-center justify-center rounded-full', iconColors[variant]]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ title }}</h3>
                                <p v-if="description" class="text-sm text-zinc-500 dark:text-zinc-400">{{ description }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-zinc-400 transition-all hover:bg-zinc-200 hover:text-zinc-600 active:scale-95 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                            @click="close"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <form class="p-5" @submit.prevent="handleSubmit">
                        <Input
                            ref="inputRef"
                            v-model="inputValue"
                            :label="inputLabel"
                            :placeholder="inputPlaceholder"
                            data-form-modal-input
                            @keyup.enter="handleSubmit"
                        />
                    </form>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 border-t border-zinc-200 bg-zinc-50/80 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <Button variant="ghost" :disabled="loading" @click="handleCancel">
                            {{ cancelText }}
                        </Button>
                        <Button :variant="variant === 'danger' ? 'danger' : 'primary'" :loading="loading" :disabled="!isValid" @click="handleSubmit">
                            {{ submitText }}
                        </Button>
                    </div>
                </Motion>
            </div>
        </Motion>
    </Teleport>
</template>
