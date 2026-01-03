<script setup lang="ts">
import { ref, watch, computed, nextTick } from 'vue';
import Modal from '../layout/Modal.vue';
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

const isValid = computed(() => inputValue.value.trim().length > 0);

// Icon colors for header
const iconColors: Record<string, string> = {
    primary: 'bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400',
    success: 'bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400',
    warning: 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400',
    danger: 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400',
};

watch(
    () => props.modelValue,
    async (isOpen) => {
        if (isOpen) {
            inputValue.value = props.initialValue;
            await nextTick();
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
</script>

<template>
    <Modal
        :model-value="modelValue"
        size="sm"
        :closable="true"
        :close-on-overlay="!loading"
        :close-on-escape="!loading"
        @update:model-value="$emit('update:modelValue', $event)"
        @close="close"
    >
        <!-- Custom Header -->
        <template #header>
            <div class="flex items-center gap-3">
                <div :class="['flex h-10 w-10 items-center justify-center rounded-xl', iconColors[variant]]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ title }}</h3>
                    <p v-if="description" class="text-sm text-zinc-500 dark:text-zinc-400">{{ description }}</p>
                </div>
            </div>
        </template>

        <!-- Body -->
        <form @submit.prevent="handleSubmit">
            <Input
                v-model="inputValue"
                :label="inputLabel"
                :placeholder="inputPlaceholder"
                data-form-modal-input
                @keyup.enter="handleSubmit"
            />
        </form>

        <!-- Footer -->
        <template #footer>
            <Button variant="ghost" :disabled="loading" @click="handleCancel">
                {{ cancelText }}
            </Button>
            <Button
                :variant="variant === 'danger' ? 'danger' : 'primary'"
                :loading="loading"
                :disabled="!isValid"
                @click="handleSubmit"
            >
                {{ submitText }}
            </Button>
        </template>
    </Modal>
</template>
