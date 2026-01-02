<script setup lang="ts">
import Alert from '@/components/ui/feedback/Alert.vue';
import Button from '@/components/ui/buttons/Button.vue';
import Card from '@/components/ui/layout/Card.vue';
import Checkbox from '@/components/ui/forms/Checkbox.vue';
import Input from '@/components/ui/forms/Input.vue';
import { useToast } from '@/composables/useToast';
import type { AIConnection, Provider } from '@/pages/Settings/AIConnections.vue';
import { router } from '@inertiajs/vue3';
import { animate } from 'motion';
import axios from 'axios';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps<{
    providers: Record<string, Provider>;
    editing?: AIConnection | null;
}>();

const emit = defineEmits<{
    saved: [];
    cancel: [];
}>();

const { success } = useToast();

const selectedProvider = ref<string>(props.editing?.provider || '');
const showAdvanced = ref(false);
const isSubmitting = ref(false);
const errorMessage = ref<string | null>(null);

const form = reactive({
    provider: props.editing?.provider || '',
    name: props.editing?.name || '',
    api_key: '',
    base_url: props.editing?.base_url || '',
    is_default: props.editing?.is_default || false,
});

const errors = reactive<Record<string, string>>({});

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

const clearErrors = () => {
    Object.keys(errors).forEach((key) => delete errors[key]);
    errorMessage.value = null;
};

const submit = async () => {
    clearErrors();
    isSubmitting.value = true;

    try {
        if (props.editing) {
            await axios.patch(`/api/ai-connections/${props.editing.id}`, {
                name: form.name,
                api_key: form.api_key || undefined,
                base_url: form.base_url || undefined,
                is_default: form.is_default,
            });
        } else {
            await axios.post('/api/ai-connections', {
                provider: form.provider,
                name: form.name,
                api_key: form.api_key,
                base_url: form.base_url || undefined,
                is_default: form.is_default,
            });
        }

        // Show success toast
        success(props.editing ? 'Connection updated successfully.' : 'Connection added successfully.', {
            title: props.editing ? 'Updated' : 'Added',
        });

        // Reload the connections data via Inertia
        router.reload({ only: ['connections'] });
        emit('saved');
    } catch (error: unknown) {
        if (axios.isAxiosError(error) && error.response?.data?.errors) {
            // Validation errors
            const validationErrors = error.response.data.errors as Record<string, string[]>;
            Object.entries(validationErrors).forEach(([field, messages]) => {
                errors[field] = messages[0];
            });
        } else if (axios.isAxiosError(error) && error.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = 'An unexpected error occurred. Please try again.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const providersList = computed(() => {
    return Object.entries(props.providers).map(([key, value]) => ({
        key,
        ...value,
    }));
});

// Animate form sections
const onEnter = (el: Element) => {
    animate(
        el,
        { opacity: [0, 1], transform: ['translateY(10px)', 'translateY(0)'] },
        { duration: 0.4, easing: [0.16, 1, 0.3, 1] }
    );
};

const onLeave = (el: Element, done: () => void) => {
    animate(
        el,
        { opacity: [1, 0], transform: ['translateY(0)', 'translateY(-10px)'] },
        { duration: 0.3, easing: [0.16, 1, 0.3, 1] }
    ).finished.then(done);
};
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

        <!-- Error Alert -->
        <Alert v-if="errorMessage" variant="danger" dismissible class="mb-4" @dismiss="errorMessage = null">
            {{ errorMessage }}
        </Alert>

        <form class="space-y-5" @submit.prevent="submit">
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
                <p v-if="errors.provider" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ errors.provider }}
                </p>
            </div>

            <!-- Provider-specific form fields -->
            <Transition
                @enter="onEnter"
                @leave="onLeave"
                :css="false"
            >
                <div v-if="selectedProvider || editing" class="space-y-4">
                    <!-- Connection Name -->
                    <Input v-model="form.name" label="Connection Name" placeholder="My OpenAI Connection" :error="errors.name" required />

                    <!-- API Key (if required) -->
                    <div v-if="currentProvider?.requires_api_key || editing?.has_api_key">
                        <Input
                            v-model="form.api_key"
                            type="password"
                            label="API Key"
                            :placeholder="editing ? '••••••••••••••••' : 'sk-...'"
                            :error="errors.api_key"
                            :required="!editing"
                        />
                        <p v-if="editing?.has_api_key" class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Leave blank to keep the existing API key.
                        </p>
                    </div>

                    <!-- Local LLM Setup Guide -->
                    <Alert v-if="selectedProvider === 'ollama' || selectedProvider === 'lm_studio'" variant="warning" title="Setup Instructions">
                        <template v-if="selectedProvider === 'ollama'">
                            Make sure Ollama is running and accessible. You may need to configure CORS by setting:
                            <code class="mx-1 rounded bg-amber-100 px-1 dark:bg-amber-900/30">OLLAMA_ORIGINS=*</code>
                        </template>
                        <template v-if="selectedProvider === 'lm_studio'">
                            Ensure LM Studio's local server is running. Go to Local Server tab in LM Studio and click "Start Server".
                        </template>
                    </Alert>

                    <!-- Advanced Settings Toggle -->
                    <button
                        type="button"
                        class="flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300 active:scale-[0.97] transition-transform"
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
                        @enter="onEnter"
                        @leave="onLeave"
                        :css="false"
                    >
                        <div v-if="showAdvanced" class="space-y-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <Input
                                v-model="form.base_url"
                                label="Base URL"
                                :placeholder="currentProvider?.default_base_url || 'https://api.example.com/v1'"
                                :error="errors.base_url"
                            />

                            <Checkbox v-model="form.is_default" label="Set as default connection" />
                        </div>
                    </Transition>
                </div>
            </Transition>

            <!-- Actions -->
            <div class="flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <Button type="button" variant="ghost" @click="emit('cancel')">Cancel</Button>
                <Button type="submit" :loading="isSubmitting" :disabled="isSubmitting || (!selectedProvider && !editing)">
                    {{ editing ? 'Save Changes' : 'Add Connection' }}
                </Button>
            </div>
        </form>
    </Card>
</template>
