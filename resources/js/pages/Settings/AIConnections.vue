<script setup lang="ts">
import AIConnectionCard from '@/components/ai/AIConnectionCard.vue';
import AIConnectionForm from '@/components/ai/AIConnectionForm.vue';
import Button from '@/components/ui/Button.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { ref } from 'vue';

export interface AIConnection {
    id: number;
    provider: string;
    provider_name: string;
    name: string;
    base_url: string | null;
    has_api_key: boolean;
    masked_api_key: string | null;
    is_active: boolean;
    is_default: boolean;
    last_tested_at: string | null;
    last_test_status: 'success' | 'failed' | 'pending';
    created_at: string;
}

export interface Provider {
    name: string;
    requires_api_key: boolean;
    default_base_url: string | null;
}

defineProps<{
    connections: AIConnection[];
    providers: Record<string, Provider>;
}>();

const showAddForm = ref(false);
const editingConnection = ref<AIConnection | null>(null);

const handleConnectionSaved = () => {
    showAddForm.value = false;
    editingConnection.value = null;
    router.reload({ only: ['connections'] });
};

const handleEdit = (connection: AIConnection) => {
    editingConnection.value = connection;
    showAddForm.value = true;
};

const handleCancel = () => {
    showAddForm.value = false;
    editingConnection.value = null;
};
</script>

<template>
    <AuthenticatedLayout title="AI Connections">
        <Head title="AI Connections" />

        <div class="mx-auto max-w-3xl">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                class="mb-8"
            >
                <Link
                    href="/settings"
                    class="mb-4 inline-flex items-center gap-1 text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Settings
                </Link>
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">AI Connections</h1>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                            Connect your AI providers to enable AI-powered writing features.
                        </p>
                    </div>
                    <Button v-if="!showAddForm" @click="showAddForm = true">
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Connection
                    </Button>
                </div>
            </Motion>

            <!-- Add/Edit Form -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <Motion
                    v-if="showAddForm"
                    :initial="{ opacity: 0, y: -10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                    class="mb-8"
                >
                    <AIConnectionForm
                        :providers="providers"
                        :editing="editingConnection"
                        @saved="handleConnectionSaved"
                        @cancel="handleCancel"
                    />
                </Motion>
            </Transition>

            <!-- Connections List -->
            <div v-if="connections.length > 0" class="space-y-4">
                <Motion
                    v-for="(connection, index) in connections"
                    :key="connection.id"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.05 * (index + 1) }"
                >
                    <AIConnectionCard :connection="connection" @edit="handleEdit(connection)" />
                </Motion>
            </div>

            <!-- Empty State -->
            <Motion
                v-else-if="!showAddForm"
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
                class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-900/50"
            >
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-violet-100 dark:bg-violet-900/30">
                    <svg class="h-8 w-8 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                        />
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">No AI connections yet</h3>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    Add your first AI provider connection to unlock AI-powered writing features.
                </p>
                <Button class="mt-6" @click="showAddForm = true">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Your First Connection
                </Button>
            </Motion>

            <!-- Help Section -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.2 }"
                class="mt-8 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900"
            >
                <h3 class="font-semibold text-zinc-900 dark:text-white">Supported Providers</h3>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    <div
                        v-for="(provider, key) in providers"
                        :key="key"
                        class="flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700"
                    >
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-zinc-700 dark:text-zinc-300">{{ provider.name }}</span>
                    </div>
                </div>
                <p class="mt-4 text-xs text-zinc-500 dark:text-zinc-400">
                    Your API keys are encrypted and stored securely. They are never exposed to the frontend or shared with third parties.
                </p>
            </Motion>
        </div>
    </AuthenticatedLayout>
</template>
