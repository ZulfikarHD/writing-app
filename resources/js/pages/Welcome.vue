<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { computed } from 'vue';

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
        <Head title="AI-Assisted Novel Writing">
            <link rel="preconnect" href="https://rsms.me/" />
            <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
        </Head>

        <!-- Navigation -->
        <Motion
            :initial="{ opacity: 0, y: -20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
            as="nav"
            class="fixed top-0 z-50 w-full border-b border-zinc-200/50 bg-white/70 backdrop-blur-xl dark:border-zinc-800/50 dark:bg-zinc-900/70"
        >
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600">
                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <span class="text-lg font-semibold text-zinc-900 dark:text-white">NovelWrite</span>
                </div>

                <div v-if="isAuthenticated" class="flex items-center gap-2">
                    <Link
                        href="/dashboard"
                        class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-violet-700 active:scale-[0.97]"
                    >
                        Go to Dashboard
                    </Link>
                </div>
                <div v-else class="flex items-center gap-2">
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
                </div>
            </div>
        </Motion>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-14">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8">
                <div class="text-center">
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
                    >
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-100 px-3 py-1 text-sm font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-400">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                            </svg>
                            AI-Powered Writing
                        </span>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.15 }"
                    >
                        <h1 class="mt-6 text-4xl font-bold tracking-tight text-zinc-900 sm:text-6xl dark:text-white">
                            Write Your Novel<br />
                            <span class="bg-gradient-to-r from-violet-600 to-purple-600 bg-clip-text text-transparent">With AI Assistance</span>
                        </h1>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.2 }"
                    >
                        <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400">
                            Plan, write, and manage your novels with powerful AI tools. From world-building to character development,
                            we've got you covered.
                        </p>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.25 }"
                        class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row"
                    >
                        <Link
                            :href="isAuthenticated ? '/dashboard' : '/register'"
                            class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-violet-500/25 transition-all hover:bg-violet-700 hover:shadow-xl hover:shadow-violet-500/30 active:scale-[0.97]"
                        >
                            Start Writing Free
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </Link>
                        <a
                            href="#features"
                            class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-6 py-3 text-base font-semibold text-zinc-900 transition-all hover:bg-zinc-50 active:scale-[0.97] dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700"
                        >
                            Learn More
                        </a>
                    </Motion>
                </div>
            </div>

            <!-- Background gradient -->
            <div class="absolute inset-0 -z-10 overflow-hidden">
                <div class="absolute top-0 left-1/2 h-[600px] w-[600px] -translate-x-1/2 rounded-full bg-gradient-to-br from-violet-400/20 to-purple-400/20 blur-3xl" />
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                    class="text-center"
                >
                    <h2 class="text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-white">Everything You Need to Write</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">
                        Powerful features designed specifically for novel writers.
                    </p>
                </Motion>

                <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <Motion
                        v-for="(feature, index) in [
                            { icon: 'edit', title: 'Distraction-Free Editor', description: 'Write in a clean, focused environment with AI suggestions at your fingertips.' },
                            { icon: 'map', title: 'Story Planning', description: 'Outline your plot with acts, chapters, and scenes. Visualize your story structure.' },
                            { icon: 'book', title: 'Codex System', description: 'Build and maintain your world bible with characters, locations, and lore.' },
                            { icon: 'chat', title: 'AI Chat Assistant', description: 'Brainstorm ideas, develop characters, and overcome writer\'s block with AI.' },
                            { icon: 'snippet', title: 'Snippets Library', description: 'Save and organize reusable text blocks for quick access while writing.' },
                            { icon: 'export', title: 'Export Options', description: 'Export your work in multiple formats including DOCX, PDF, and EPUB.' },
                        ]"
                        :key="feature.title"
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: index * 0.05 }"
                        class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900"
                    >
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900/30 dark:to-purple-900/30">
                            <svg class="h-6 w-6 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="feature.icon === 'edit'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                <path v-else-if="feature.icon === 'map'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                <path v-else-if="feature.icon === 'book'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                <path v-else-if="feature.icon === 'chat'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                <path v-else-if="feature.icon === 'snippet'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                <path v-else-if="feature.icon === 'export'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-zinc-900 dark:text-white">{{ feature.title }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ feature.description }}</p>
                    </Motion>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <Motion
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 to-purple-700 px-6 py-16 text-center sm:px-12 sm:py-24"
                >
                    <h2 class="text-3xl font-bold text-white sm:text-4xl">Ready to Start Your Novel?</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-violet-100">
                        Join thousands of writers who are bringing their stories to life with NovelWrite.
                    </p>
                    <Link
                        :href="isAuthenticated ? '/dashboard' : '/register'"
                        class="mt-8 inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 text-base font-semibold text-violet-700 shadow-xl transition-all hover:bg-violet-50 active:scale-[0.97]"
                    >
                        Get Started for Free
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>

                    <!-- Background decoration -->
                    <div class="absolute top-0 right-0 -z-10 h-64 w-64 translate-x-1/3 -translate-y-1/3 rounded-full bg-white/10 blur-3xl" />
                    <div class="absolute bottom-0 left-0 -z-10 h-64 w-64 -translate-x-1/3 translate-y-1/3 rounded-full bg-white/10 blur-3xl" />
                </Motion>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 py-8 dark:border-zinc-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-md bg-gradient-to-br from-violet-500 to-purple-600">
                            <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-zinc-900 dark:text-white">NovelWrite</span>
                    </div>
                    <p class="text-sm text-zinc-500">© {{ new Date().getFullYear() }} NovelWrite. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
