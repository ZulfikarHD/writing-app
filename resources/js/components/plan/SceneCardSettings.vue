<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Toggle from '@/components/ui/forms/Toggle.vue';
import Modal from '@/components/ui/layout/Modal.vue';
import { ref, watch } from 'vue';

interface CardSettings {
    size: 'compact' | 'normal' | 'large';
    showSummary: boolean;
    showLabels: boolean;
    showWordCount: boolean;
    showPov: boolean;
    showCodexMentions: boolean;
    gridAxis: 'vertical' | 'horizontal';
    cardWidth: 'small' | 'medium' | 'large';
    cardHeight: 'full' | 'small' | 'medium' | 'large';
}

const props = defineProps<{
    modelValue: boolean;
    settings: CardSettings;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
    (e: 'update:settings', value: CardSettings): void;
}>();

const localSettings = ref<CardSettings>({ ...props.settings });

// Sync local settings when props change
watch(
    () => props.settings,
    (newSettings) => {
        localSettings.value = { ...newSettings };
    },
    { deep: true }
);

const gridAxisOptions = [
    { value: 'vertical', label: 'Vertical' },
    { value: 'horizontal', label: 'Horizontal' },
];

const cardWidthOptions = [
    { value: 'small', label: 'Small' },
    { value: 'medium', label: 'Medium' },
    { value: 'large', label: 'Large' },
];

const cardHeightOptions = [
    { value: 'full', label: 'Full' },
    { value: 'small', label: 'Small' },
    { value: 'medium', label: 'Medium' },
    { value: 'large', label: 'Large' },
];

const applySettings = () => {
    emit('update:settings', { ...localSettings.value });
    emit('update:modelValue', false);
};

const resetToDefaults = () => {
    localSettings.value = {
        size: 'normal',
        showSummary: true,
        showLabels: true,
        showWordCount: true,
        showPov: true,
        showCodexMentions: true,
        gridAxis: 'vertical',
        cardWidth: 'medium',
        cardHeight: 'medium',
    };
};
</script>

<template>
    <Modal :model-value="modelValue" title="View Settings" size="md" @update:model-value="emit('update:modelValue', $event)">
        <div class="space-y-6">
            <!-- Auto-detect References -->
            <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <div>
                    <span class="text-sm font-medium text-zinc-900 dark:text-white">Show auto-detected references</span>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Display codex mentions found in scene content</p>
                </div>
                <Toggle v-model="localSettings.showCodexMentions" />
            </div>

            <!-- Grid Axis -->
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Grid axis</label>
                <div class="flex gap-2">
                    <button
                        v-for="option in gridAxisOptions"
                        :key="option.value"
                        type="button"
                        :class="[
                            'flex items-center gap-2 rounded-lg border-2 px-4 py-2 text-sm font-medium transition-all',
                            localSettings.gridAxis === option.value
                                ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                                : 'border-zinc-200 text-zinc-700 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-600',
                        ]"
                        @click="localSettings.gridAxis = option.value as 'vertical' | 'horizontal'"
                    >
                        <svg v-if="localSettings.gridAxis === option.value" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ option.label }}
                    </button>
                </div>
            </div>

            <!-- Card Width -->
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Card width</label>
                <div class="flex gap-2">
                    <button
                        v-for="option in cardWidthOptions"
                        :key="option.value"
                        type="button"
                        :class="[
                            'flex items-center gap-2 rounded-lg border-2 px-4 py-2 text-sm font-medium transition-all',
                            localSettings.cardWidth === option.value
                                ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                                : 'border-zinc-200 text-zinc-700 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-600',
                        ]"
                        @click="localSettings.cardWidth = option.value as 'small' | 'medium' | 'large'"
                    >
                        <svg v-if="localSettings.cardWidth === option.value" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ option.label }}
                    </button>
                </div>
            </div>

            <!-- Card Height -->
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Card Height</label>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="option in cardHeightOptions"
                        :key="option.value"
                        type="button"
                        :class="[
                            'flex items-center gap-2 rounded-lg border-2 px-4 py-2 text-sm font-medium transition-all',
                            localSettings.cardHeight === option.value
                                ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                                : 'border-zinc-200 text-zinc-700 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-600',
                        ]"
                        @click="localSettings.cardHeight = option.value as 'full' | 'small' | 'medium' | 'large'"
                    >
                        <svg v-if="localSettings.cardHeight === option.value" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ option.label }}
                    </button>
                </div>
            </div>

            <!-- Display Options -->
            <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <label class="mb-3 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Card Content</label>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-900 dark:text-white">Show Summary</span>
                        <Toggle v-model="localSettings.showSummary" />
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-900 dark:text-white">Show Labels</span>
                        <Toggle v-model="localSettings.showLabels" />
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-900 dark:text-white">Show Word Count</span>
                        <Toggle v-model="localSettings.showWordCount" />
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-900 dark:text-white">Show POV Character</span>
                        <Toggle v-model="localSettings.showPov" />
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center justify-between">
                <button type="button" class="text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200" @click="resetToDefaults">
                    Reset to Defaults
                </button>
                <div class="flex gap-2">
                    <Button variant="secondary" @click="emit('update:modelValue', false)">Cancel</Button>
                    <Button @click="applySettings">Apply</Button>
                </div>
            </div>
        </template>
    </Modal>
</template>
