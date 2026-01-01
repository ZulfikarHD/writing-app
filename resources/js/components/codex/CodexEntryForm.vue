<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { ref, watch } from 'vue';

interface FormData {
    type: string;
    name: string;
    description: string;
    ai_context_mode: string;
}

const props = withDefaults(
    defineProps<{
        initialData?: Partial<FormData>;
        types: string[];
        contextModes: string[];
        submitLabel?: string;
        loading?: boolean;
        errors?: Record<string, string>;
    }>(),
    {
        submitLabel: 'Save',
        loading: false,
        errors: () => ({}),
    },
);

const emit = defineEmits<{
    (e: 'submit', data: FormData): void;
    (e: 'cancel'): void;
}>();

const typeConfig: Record<string, { label: string; icon: string; description: string }> = {
    character: { label: 'Character', icon: '👤', description: 'People, creatures, or beings' },
    location: { label: 'Location', icon: '📍', description: 'Places and environments' },
    item: { label: 'Item', icon: '⚔️', description: 'Objects and artifacts' },
    lore: { label: 'Lore', icon: '📜', description: 'History and world-building' },
    organization: { label: 'Organization', icon: '🏛️', description: 'Groups and factions' },
    subplot: { label: 'Subplot', icon: '📖', description: 'Secondary storylines' },
};

const contextModeLabels: Record<string, { label: string; description: string }> = {
    always: { label: 'Always', description: 'Always include in AI context' },
    detected: { label: 'Detected', description: 'Include when mentioned in text' },
    manual: { label: 'Manual', description: 'Only when manually selected' },
    never: { label: 'Never', description: 'Never include in AI context' },
};

const formData = ref<FormData>({
    type: props.initialData?.type || '',
    name: props.initialData?.name || '',
    description: props.initialData?.description || '',
    ai_context_mode: props.initialData?.ai_context_mode || 'detected',
});

// Watch for external data changes
watch(
    () => props.initialData,
    (newData) => {
        if (newData) {
            formData.value = {
                type: newData.type || formData.value.type,
                name: newData.name || formData.value.name,
                description: newData.description || formData.value.description,
                ai_context_mode: newData.ai_context_mode || formData.value.ai_context_mode,
            };
        }
    },
    { deep: true },
);

const selectType = (type: string) => {
    formData.value.type = type;
};

const handleSubmit = () => {
    emit('submit', { ...formData.value });
};

const isValid = () => {
    return formData.value.type && formData.value.name.trim();
};
</script>

<template>
    <form @submit.prevent="handleSubmit">
        <!-- Entry Type -->
        <Card class="mb-6">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Entry Type</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <button
                    v-for="type in types"
                    :key="type"
                    type="button"
                    class="flex flex-col items-center rounded-lg border p-4 text-center transition-all active:scale-[0.98]"
                    :class="
                        formData.type === type
                            ? 'border-violet-500 bg-violet-50 ring-2 ring-violet-500/20 dark:border-violet-500 dark:bg-violet-900/20'
                            : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                    "
                    @click="selectType(type)"
                >
                    <span class="text-2xl">{{ typeConfig[type]?.icon }}</span>
                    <span class="mt-2 font-medium text-zinc-900 dark:text-white">{{ typeConfig[type]?.label }}</span>
                    <span class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ typeConfig[type]?.description }}</span>
                </button>
            </div>
            <p v-if="errors.type" class="mt-2 text-sm text-red-500">{{ errors.type }}</p>
        </Card>

        <!-- Basic Info -->
        <Card class="mb-6">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Basic Information</h2>
            <div class="space-y-4">
                <Input v-model="formData.name" label="Name" placeholder="Enter entry name" required :error="errors.name" />

                <Textarea
                    v-model="formData.description"
                    label="Description"
                    placeholder="Describe this entry..."
                    rows="5"
                    :error="errors.description"
                />
            </div>
        </Card>

        <!-- AI Settings -->
        <Card class="mb-6">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">AI Context Settings</h2>
            <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">Control how this entry is included when AI generates content.</p>
            <div class="space-y-2">
                <label
                    v-for="mode in contextModes"
                    :key="mode"
                    class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition-all"
                    :class="
                        formData.ai_context_mode === mode
                            ? 'border-violet-500 bg-violet-50 dark:border-violet-500 dark:bg-violet-900/20'
                            : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                    "
                >
                    <input
                        v-model="formData.ai_context_mode"
                        type="radio"
                        :value="mode"
                        class="mt-1 h-4 w-4 border-zinc-300 text-violet-600 focus:ring-violet-500"
                    />
                    <div>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ contextModeLabels[mode]?.label }}</span>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ contextModeLabels[mode]?.description }}</p>
                    </div>
                </label>
            </div>
        </Card>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <Button type="button" variant="ghost" @click="emit('cancel')"> Cancel </Button>
            <Button type="submit" :loading="loading" :disabled="!isValid() || loading">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
