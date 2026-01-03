<script setup lang="ts">
import ConfirmProvider from '@/components/ui/overlays/ConfirmProvider.vue';
import ToastContainer from '@/components/ui/feedback/ToastContainer.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { computed, ref } from 'vue';

defineProps<{
    title?: string;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user as { name: string; email: string } | undefined);
const showUserMenu = ref(false);

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
        <Head :title="title" />

        <!-- Navigation -->
        <Motion
            :initial="{ opacity: 0, y: -20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
            as="nav"
            class="fixed top-0 z-50 w-full border-b border-zinc-200/50 bg-white/70 backdrop-blur-xl dark:border-zinc-800/50 dark:bg-zinc-900/70"
        >
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-6">
                    <Link href="/dashboard" class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600">
                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <span class="hidden text-lg font-semibold text-zinc-900 sm:block dark:text-white">NovelWrite</span>
                    </Link>

                    <div class="hidden items-center gap-1 md:flex">
                        <Link
                            href="/dashboard"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                            :class="{ 'bg-zinc-100 dark:bg-zinc-800': $page.url === '/dashboard' }"
                        >
                            Dashboard
                        </Link>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/novels/create"
                        class="flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-sm font-medium text-white transition-all hover:bg-violet-700 active:scale-[0.97]"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden sm:inline">New Novel</span>
                    </Link>

                    <!-- User Menu -->
                    <div class="relative">
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                        >
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-violet-400 to-purple-500 text-xs font-semibold text-white">
                                {{ user?.name?.charAt(0).toUpperCase() }}
                            </div>
                            <span class="hidden sm:inline">{{ user?.name }}</span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <Transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <div
                                v-if="showUserMenu"
                                @click="showUserMenu = false"
                                class="absolute right-0 mt-2 w-48 origin-top-right rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                <Link
                                    href="/settings/ai"
                                    class="block px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                >
                                    AI Settings
                                </Link>
                                <Link
                                    href="/prompts"
                                    class="block px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                >
                                    Prompt Library
                                </Link>
                                <Link
                                    href="/profile"
                                    class="block px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                >
                                    Profile Settings
                                </Link>
                                <hr class="my-1 border-zinc-200 dark:border-zinc-700" />
                                <button
                                    @click="logout"
                                    class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                >
                                    Log Out
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </Motion>

        <!-- Page Content -->
        <main class="mx-auto max-w-7xl px-4 pt-20 pb-8 sm:px-6 lg:px-8">
            <slot />
        </main>

        <!-- Global Toast Container -->
        <ToastContainer />

        <!-- Global Confirm Dialog Provider -->
        <ConfirmProvider />
    </div>
</template>
