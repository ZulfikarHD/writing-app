<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';

defineProps<{
    title?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-zinc-50 dark:bg-zinc-950">
        <Head :title="title" />

        <!-- Header -->
        <Motion
            :initial="{ opacity: 0, y: -20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
            as="header"
            class="fixed top-0 z-50 w-full border-b border-zinc-200/50 bg-white/70 backdrop-blur-xl dark:border-zinc-800/50 dark:bg-zinc-900/70"
        >
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600">
                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <span class="text-lg font-semibold text-zinc-900 dark:text-white">NovelWrite</span>
                </Link>

                <nav class="flex items-center gap-2">
                    <Link
                        href="/login"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                    >
                        Log in
                    </Link>
                    <Link
                        href="/register"
                        class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-zinc-800 active:scale-[0.97] dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100"
                    >
                        Get Started
                    </Link>
                </nav>
            </div>
        </Motion>

        <!-- Main Content -->
        <main class="flex flex-1 items-center justify-center px-4 pt-14">
            <slot />
        </main>
    </div>
</template>
