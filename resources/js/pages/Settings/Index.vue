<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';

const settingsCategories = [
    {
        name: 'AI Connections',
        description: 'Manage your AI provider connections and API keys',
        href: '/settings/ai',
        icon: 'ai',
    },
    {
        name: 'Profile',
        description: 'Update your account information and password',
        href: '/profile',
        icon: 'user',
    },
];
</script>

<template>
    <AuthenticatedLayout title="Settings">
        <Head title="Settings" />

        <div class="mx-auto max-w-2xl">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                class="mb-8"
            >
                <Link
                    href="/dashboard"
                    class="mb-4 inline-flex items-center gap-1 text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Dashboard
                </Link>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Settings</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Manage your application settings and preferences.</p>
            </Motion>

            <!-- Settings Categories -->
            <div class="space-y-4">
                <Motion
                    v-for="(category, index) in settingsCategories"
                    :key="category.name"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.05 * (index + 1) }"
                >
                    <Link
                        :href="category.href"
                        class="group flex items-center gap-4 rounded-xl border border-zinc-200 bg-white p-4 transition-all hover:border-violet-300 hover:shadow-md active:scale-[0.99] dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-violet-700"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 text-white"
                        >
                            <svg v-if="category.icon === 'ai'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                            <svg v-else-if="category.icon === 'user'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="font-semibold text-zinc-900 dark:text-white">{{ category.name }}</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ category.description }}</p>
                        </div>
                        <svg
                            class="h-5 w-5 text-zinc-400 transition-transform group-hover:translate-x-1 dark:text-zinc-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </Motion>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
