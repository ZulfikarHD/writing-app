<script setup lang="ts">
import Alert from '@/components/ui/Alert.vue';
import Badge from '@/components/ui/Badge.vue';
import Button from '@/components/ui/Button.vue';
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue';
import { useToast } from '@/composables/useToast';
import type { AIConnection } from '@/pages/Settings/AIConnections.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

import ConnectionStatus from './ConnectionStatus.vue';

const props = defineProps<{
    connection: AIConnection;
}>();

const emit = defineEmits<{
    edit: [];
}>();

const { success, error: showError } = useToast();

const testing = ref(false);
const testResult = ref<{ success: boolean; message: string; model_count?: number } | null>(null);
const deleting = ref(false);
const showDeleteConfirm = ref(false);
const errorMessage = ref<string | null>(null);

const testConnection = async () => {
    testing.value = true;
    testResult.value = null;
    errorMessage.value = null;

    try {
        const response = await axios.post(`/api/ai-connections/${props.connection.id}/test`);
        testResult.value = response.data;

        // Reload to update connection status
        router.reload({ only: ['connections'] });

        // Clear result after 5 seconds
        setTimeout(() => {
            testResult.value = null;
        }, 5000);
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.data?.message) {
            testResult.value = {
                success: false,
                message: error.response.data.message,
            };
        } else {
            testResult.value = {
                success: false,
                message: 'Failed to test connection. Please try again.',
            };
        }
    } finally {
        testing.value = false;
    }
};

const deleteConnection = async () => {
    deleting.value = true;
    errorMessage.value = null;

    try {
        await axios.delete(`/api/ai-connections/${props.connection.id}`);
        success('Connection deleted successfully.', { title: 'Deleted' });
        router.reload({ only: ['connections'] });
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.data?.message) {
            showError(error.response.data.message, { title: 'Delete Failed' });
        } else {
            showError('Failed to delete connection. Please try again.', { title: 'Delete Failed' });
        }
    } finally {
        deleting.value = false;
        showDeleteConfirm.value = false;
    }
};

const toggleDefault = async () => {
    errorMessage.value = null;
    const newDefault = !props.connection.is_default;

    try {
        await axios.patch(`/api/ai-connections/${props.connection.id}`, {
            is_default: newDefault,
        });
        if (newDefault) {
            success(`${props.connection.name} is now the default connection.`, { title: 'Default Updated' });
        }
        router.reload({ only: ['connections'] });
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.data?.message) {
            showError(error.response.data.message, { title: 'Update Failed' });
        } else {
            showError('Failed to update connection. Please try again.', { title: 'Update Failed' });
        }
    }
};

const toggleActive = async () => {
    errorMessage.value = null;
    const newActive = !props.connection.is_active;

    try {
        await axios.patch(`/api/ai-connections/${props.connection.id}`, {
            is_active: newActive,
        });
        success(`Connection ${newActive ? 'enabled' : 'disabled'} successfully.`, {
            title: newActive ? 'Enabled' : 'Disabled',
        });
        router.reload({ only: ['connections'] });
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.data?.message) {
            showError(error.response.data.message, { title: 'Update Failed' });
        } else {
            showError('Failed to update connection. Please try again.', { title: 'Update Failed' });
        }
    }
};
</script>

<template>
    <div
        class="rounded-xl border bg-white transition-all dark:bg-zinc-900"
        :class="[connection.is_active ? 'border-zinc-200 dark:border-zinc-800' : 'border-zinc-200/50 opacity-60 dark:border-zinc-800/50']"
    >
        <div class="p-4">
            <!-- Error Alert -->
            <Alert v-if="errorMessage" variant="danger" dismissible class="mb-4" @dismiss="errorMessage = null">
                {{ errorMessage }}
            </Alert>

            <!-- Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg"
                        :class="
                            connection.is_active
                                ? 'bg-gradient-to-br from-violet-500 to-purple-600 text-white'
                                : 'bg-zinc-200 text-zinc-400 dark:bg-zinc-700 dark:text-zinc-500'
                        "
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ connection.name }}</h3>
                            <Badge v-if="connection.is_default" variant="primary" size="sm">Default</Badge>
                        </div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ connection.provider_name }}</p>
                    </div>
                </div>
                <ConnectionStatus :status="connection.last_test_status" :last-tested-at="connection.last_tested_at" />
            </div>

            <!-- Details -->
            <div class="mt-4 space-y-2 text-sm">
                <div v-if="connection.base_url" class="flex items-center gap-2">
                    <span class="text-zinc-400 dark:text-zinc-500">URL:</span>
                    <span class="font-mono text-xs text-zinc-600 dark:text-zinc-300">{{ connection.base_url }}</span>
                </div>
                <div v-if="connection.masked_api_key" class="flex items-center gap-2">
                    <span class="text-zinc-400 dark:text-zinc-500">Key:</span>
                    <span class="font-mono text-xs text-zinc-600 dark:text-zinc-300">{{ connection.masked_api_key }}</span>
                </div>
            </div>

            <!-- Test Result -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <Alert v-if="testResult" :variant="testResult.success ? 'success' : 'danger'" class="mt-4">
                    <div class="flex items-center gap-2">
                        <span>{{ testResult.message }}</span>
                        <span v-if="testResult.model_count" class="text-xs opacity-75"> ({{ testResult.model_count }} models available) </span>
                    </div>
                </Alert>
            </Transition>

            <!-- Actions -->
            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                <Button size="sm" variant="ghost" :loading="testing" @click="testConnection">
                    <svg v-if="!testing" class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Test
                </Button>
                <Button size="sm" variant="ghost" @click="emit('edit')">
                    <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                        />
                    </svg>
                    Edit
                </Button>
                <Button v-if="!connection.is_default" size="sm" variant="ghost" @click="toggleDefault">
                    <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                        />
                    </svg>
                    Set Default
                </Button>
                <Button size="sm" variant="ghost" @click="toggleActive">
                    {{ connection.is_active ? 'Disable' : 'Enable' }}
                </Button>
                <div class="flex-1"></div>
                <Button
                    size="sm"
                    variant="ghost"
                    class="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                    @click="showDeleteConfirm = true"
                >
                    <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                    </svg>
                    Delete
                </Button>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            v-model="showDeleteConfirm"
            title="Delete Connection"
            :message="`Are you sure you want to delete '${connection.name}'? This action cannot be undone.`"
            confirm-text="Delete"
            cancel-text="Cancel"
            variant="danger"
            :loading="deleting"
            @confirm="deleteConnection"
        />
    </div>
</template>
