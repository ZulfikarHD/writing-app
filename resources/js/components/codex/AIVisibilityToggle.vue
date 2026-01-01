<script setup lang="ts">
/**
 * AIVisibilityToggle.vue - Sprint 14 (US-12.6)
 *
 * Per-detail AI visibility control.
 * Allows setting whether a detail is:
 * - Always sent to AI
 * - Never sent to AI
 * - Only sent with NSFW prompts
 *
 * Auto-saves on change.
 */
import { ref, watch, computed } from 'vue';

type AIVisibility = 'always' | 'never' | 'nsfw_only';

interface VisibilityOption {
    value: AIVisibility;
    label: string;
    icon: string;
    description: string;
    color: string;
}

const props = defineProps<{
    modelValue: AIVisibility;
    disabled?: boolean;
    compact?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: AIVisibility): void;
}>();

const localValue = ref<AIVisibility>(props.modelValue);
const isOpen = ref(false);

watch(() => props.modelValue, (newVal) => {
    localValue.value = newVal;
});

const options: VisibilityOption[] = [
    {
        value: 'always',
        label: 'Always',
        icon: '👁️',
        description: 'Always included in AI context',
        color: 'text-emerald-600 dark:text-emerald-400',
    },
    {
        value: 'never',
        label: 'Never',
        icon: '🔒',
        description: 'Never sent to AI (private)',
        color: 'text-red-600 dark:text-red-400',
    },
    {
        value: 'nsfw_only',
        label: 'NSFW Only',
        icon: '🔞',
        description: 'Only included with NSFW prompts',
        color: 'text-amber-600 dark:text-amber-400',
    },
];

const currentOption = computed(() =>
    options.find(o => o.value === localValue.value) || options[0]
);

const selectOption = (value: AIVisibility) => {
    localValue.value = value;
    emit('update:modelValue', value);
    isOpen.value = false;
};

const handleClickOutside = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (!target.closest('.ai-visibility-toggle')) {
        isOpen.value = false;
    }
};

// Close on escape
const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        isOpen.value = false;
    }
};
</script>

<template>
    <div
        class="ai-visibility-toggle relative"
        @keydown="handleKeydown"
    >
        <!-- Compact Mode: Icon Only Button -->
        <button
            v-if="compact"
            type="button"
            class="flex items-center justify-center rounded-md p-1.5 transition-colors"
            :class="[
                currentOption.color,
                disabled
                    ? 'cursor-not-allowed opacity-50'
                    : 'hover:bg-zinc-100 dark:hover:bg-zinc-700'
            ]"
            :disabled="disabled"
            :title="`AI Visibility: ${currentOption.label} - ${currentOption.description}`"
            @click="isOpen = !isOpen"
        >
            <span class="text-sm">{{ currentOption.icon }}</span>
        </button>

        <!-- Full Mode: Button with Label -->
        <button
            v-else
            type="button"
            class="flex items-center gap-2 rounded-md border border-zinc-200 px-2.5 py-1.5 text-sm transition-colors dark:border-zinc-700"
            :class="[
                disabled
                    ? 'cursor-not-allowed opacity-50'
                    : 'hover:bg-zinc-50 dark:hover:bg-zinc-800'
            ]"
            :disabled="disabled"
            @click="isOpen = !isOpen"
        >
            <span>{{ currentOption.icon }}</span>
            <span :class="currentOption.color">{{ currentOption.label }}</span>
            <svg
                class="h-4 w-4 text-zinc-400 transition-transform"
                :class="{ 'rotate-180': isOpen }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen && !disabled"
                v-click-outside="handleClickOutside"
                class="absolute right-0 z-50 mt-1 w-56 origin-top-right rounded-lg border border-zinc-200 bg-white p-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
            >
                <button
                    v-for="option in options"
                    :key="option.value"
                    type="button"
                    class="flex w-full items-start gap-3 rounded-md px-3 py-2 text-left transition-colors"
                    :class="[
                        localValue === option.value
                            ? 'bg-zinc-100 dark:bg-zinc-700'
                            : 'hover:bg-zinc-50 dark:hover:bg-zinc-700/50'
                    ]"
                    @click="selectOption(option.value)"
                >
                    <span class="mt-0.5 text-lg">{{ option.icon }}</span>
                    <div class="flex-1">
                        <div class="text-sm font-medium" :class="option.color">
                            {{ option.label }}
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ option.description }}
                        </div>
                    </div>
                    <svg
                        v-if="localValue === option.value"
                        class="mt-0.5 h-4 w-4 text-violet-600 dark:text-violet-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* Click outside directive fallback */
</style>
