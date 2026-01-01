<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import type { AIConnection } from '@/Pages/Settings/AIConnections.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

import ConnectionStatus from './ConnectionStatus.vue';

const props = defineProps<{
    connection: AIConnection;
}>();

const emit = defineEmits<{
    edit: [];
}>();

const testing = ref(false);
const testResult = ref<{ success: boolean; message: string; model_count?: number } | null>(null);
const deleting = ref(false);
const showDeleteConfirm = ref(false);

const testConnection = async () => {
    testing.value = true;
    testResult.value = null;

    try {
        const response = await fetch(`/api/ai-connections/${props.connection.id}/test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        const data = await response.json();
        testResult.value = data;

        // Reload to update connection status
        router.reload({ only: ['connections'] });

        // Clear result after 5 seconds
        setTimeout(() => {
            testResult.value = null;
        }, 5000);
    } catch {
        testResult.value = {
            success: false,
            message: 'Failed to test connection. Please try again.',
        };
    } finally {
        testing.value = false;
    }
};

const deleteConnection = async () => {
    deleting.value = true;

    try {
        await fetch(`/api/ai-connections/${props.connection.id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        router.reload({ only: ['connections'] });
    } catch {
        // Handle error
    } finally {
        deleting.value = false;
        showDeleteConfirm.value = false;
    }
};

const toggleDefault = async () => {
    await fetch(`/api/ai-connections/${props.connection.id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({ is_default: !props.connection.is_default }),
    });

    router.reload({ only: ['connections'] });
};

const toggleActive = async () => {
    await fetch(`/api/ai-connections/${props.connection.id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({ is_active: !props.connection.is_active }),
    });

    router.reload({ only: ['connections'] });
};
</script>

<template>
    <div
        class="rounded-xl border bg-white transition-all dark:bg-zinc-900"
        :class="[
            connection.is_active
                ? 'border-zinc-200 dark:border-zinc-800'
                : 'border-zinc-200/50 opacity-60 dark:border-zinc-800/50',
        ]"
    >
        <div class="p-4">
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
                            <span
                                v-if="connection.is_default"
                                class="rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-400"
                            >
                                Default
                            </span>
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
                <div
                    v-if="testResult"
                    class="mt-4 rounded-lg px-3 py-2 text-sm"
                    :class="testResult.success ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400'"
                >
                    <div class="flex items-center gap-2">
                        <svg v-if="testResult.success" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>{{ testResult.message }}</span>
                        <span v-if="testResult.model_count" class="text-xs opacity-75">
                            ({{ testResult.model_count }} models available)
                        </span>
                    </div>
                </div>
            </Transition>

            <!-- Actions -->
            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                <Button size="sm" variant="ghost" :loading="testing" @click="testConnection">
                    <svg v-if="!testing" class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
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
                <Button v-if="!showDeleteConfirm" size="sm" variant="ghost" class="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20" @click="showDeleteConfirm = true">
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
                <div v-else class="flex items-center gap-2">
                    <span class="text-xs text-red-600 dark:text-red-400">Confirm delete?</span>
                    <Button size="sm" variant="danger" :loading="deleting" @click="deleteConnection">Yes</Button>
                    <Button size="sm" variant="ghost" @click="showDeleteConfirm = false">No</Button>
                </div>
            </div>
        </div>
    </div>
</template>
