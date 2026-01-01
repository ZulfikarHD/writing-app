<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import type { AIConnection, Provider } from '@/Pages/Settings/AIConnections.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    providers: Record<string, Provider>;
    editing?: AIConnection | null;
}>();

const emit = defineEmits<{
    saved: [];
    cancel: [];
}>();

const selectedProvider = ref<string>(props.editing?.provider || '');
const showAdvanced = ref(false);

const form = useForm({
    provider: props.editing?.provider || '',
    name: props.editing?.name || '',
    api_key: '',
    base_url: props.editing?.base_url || '',
    is_default: props.editing?.is_default || false,
});

const currentProvider = computed(() => {
    return props.providers[selectedProvider.value] || null;
});

// Watch for provider changes to update defaults
watch(selectedProvider, (provider) => {
    form.provider = provider;
    if (!props.editing) {
        const providerConfig = props.providers[provider];
        if (providerConfig) {
            form.name = providerConfig.name;
            form.base_url = providerConfig.default_base_url || '';
        }
    }
});

// Initialize if editing
if (props.editing) {
    selectedProvider.value = props.editing.provider;
}

const submit = () => {
    if (props.editing) {
        form.patch(`/api/ai-connections/${props.editing.id}`, {
            preserveScroll: true,
            onSuccess: () => emit('saved'),
        });
    } else {
        form.post('/api/ai-connections', {
            preserveScroll: true,
            onSuccess: () => emit('saved'),
        });
    }
};

const providersList = computed(() => {
    return Object.entries(props.providers).map(([key, value]) => ({
        key,
        ...value,
    }));
});
</script>

<template>
    <Card>
        <div class="mb-6">
            <h2 class="text-base font-bold text-zinc-900 dark:text-white">
                {{ editing ? 'Edit Connection' : 'Add New Connection' }}
            </h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ editing ? 'Update your AI provider connection settings.' : 'Connect a new AI provider to enable AI-powered features.' }}
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Provider Selection (only for new connections) -->
            <div v-if="!editing">
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Provider</label>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
                    <button
                        v-for="provider in providersList"
                        :key="provider.key"
                        type="button"
                        class="flex flex-col items-center rounded-lg border p-3 text-center transition-all active:scale-[0.97]"
                        :class="
                            selectedProvider === provider.key
                                ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-500 dark:bg-violet-900/20 dark:text-violet-400'
                                : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                        "
                        @click="selectedProvider = provider.key"
                    >
                        <span class="text-sm font-medium">{{ provider.name }}</span>
                        <span class="mt-0.5 text-xs opacity-60">
                            {{ provider.requires_api_key ? 'API Key' : 'No Key' }}
                        </span>
                    </button>
                </div>
                <p v-if="form.errors.provider" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.provider }}
                </p>
            </div>

            <!-- Provider-specific form fields -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="selectedProvider || editing" class="space-y-4">
                    <!-- Connection Name -->
                    <Input
                        v-model="form.name"
                        label="Connection Name"
                        placeholder="My OpenAI Connection"
                        :error="form.errors.name"
                        required
                    />

                    <!-- API Key (if required) -->
                    <div v-if="currentProvider?.requires_api_key || editing?.has_api_key">
                        <Input
                            v-model="form.api_key"
                            type="password"
                            label="API Key"
                            :placeholder="editing ? '••••••••••••••••' : 'sk-...'"
                            :error="form.errors.api_key"
                            :required="!editing"
                        />
                        <p v-if="editing?.has_api_key" class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Leave blank to keep the existing API key.
                        </p>
                    </div>

                    <!-- Local LLM Setup Guide -->
                    <div
                        v-if="selectedProvider === 'ollama' || selectedProvider === 'lm_studio'"
                        class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-900/20"
                    >
                        <h4 class="flex items-center gap-2 font-medium text-amber-800 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            Setup Instructions
                        </h4>
                        <div class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                            <p v-if="selectedProvider === 'ollama'">
                                Make sure Ollama is running and accessible. You may need to configure CORS by setting:
                                <code class="mx-1 rounded bg-amber-100 px-1 dark:bg-amber-900/30">OLLAMA_ORIGINS=*</code>
                            </p>
                            <p v-if="selectedProvider === 'lm_studio'">
                                Ensure LM Studio's local server is running. Go to Local Server tab in LM Studio and click "Start Server".
                            </p>
                        </div>
                    </div>

                    <!-- Advanced Settings Toggle -->
                    <button
                        type="button"
                        class="flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300"
                        @click="showAdvanced = !showAdvanced"
                    >
                        <svg
                            class="h-4 w-4 transition-transform"
                            :class="{ 'rotate-90': showAdvanced }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        Advanced Settings
                    </button>

                    <!-- Advanced Settings -->
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 -translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-2"
                    >
                        <div v-if="showAdvanced" class="space-y-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <Input
                                v-model="form.base_url"
                                label="Base URL"
                                :placeholder="currentProvider?.default_base_url || 'https://api.example.com/v1'"
                                :error="form.errors.base_url"
                            />

                            <div class="flex items-center gap-3">
                                <input
                                    id="is_default"
                                    v-model="form.is_default"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-800"
                                />
                                <label for="is_default" class="text-sm text-zinc-700 dark:text-zinc-300">
                                    Set as default connection
                                </label>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>

            <!-- Actions -->
            <div class="flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <Button type="button" variant="ghost" @click="emit('cancel')">Cancel</Button>
                <Button
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing || (!selectedProvider && !editing)"
                >
                    {{ editing ? 'Save Changes' : 'Add Connection' }}
                </Button>
            </div>
        </form>
    </Card>
</template>
