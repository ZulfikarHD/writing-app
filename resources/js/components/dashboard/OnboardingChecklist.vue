<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';

interface OnboardingState {
    welcome_completed: boolean;
    first_novel_created: boolean;
    editor_toured: boolean;
    onboarding_skipped: boolean;
}

defineProps<{
    state: OnboardingState;
}>();

const skipOnboarding = () => {
    router.post('/onboarding/skip');
};

const tasks = [
    {
        key: 'welcome_completed',
        title: 'Welcome to NovelWrite',
        description: 'Learn about the app features',
        completed: false,
    },
    {
        key: 'first_novel_created',
        title: 'Create your first novel',
        description: 'Set up your writing project',
        completed: false,
        action: '/novels/create',
    },
];
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 20 }"
        :animate="{ opacity: 1, y: 0 }"
        :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
        class="mb-8 overflow-hidden rounded-xl border border-violet-200 bg-gradient-to-br from-violet-50 to-purple-50 dark:border-violet-800/50 dark:from-violet-900/20 dark:to-purple-900/20"
    >
        <div class="p-6">
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Getting Started</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Complete these steps to get the most out of NovelWrite</p>
                </div>
                <button
                    @click="skipOnboarding"
                    class="text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300"
                >
                    Skip
                </button>
            </div>

            <div class="space-y-3">
                <div
                    v-for="(task, index) in tasks"
                    :key="task.key"
                    class="flex items-center gap-4 rounded-lg bg-white/80 p-4 dark:bg-zinc-800/50"
                >
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                        :class="
                            state[task.key as keyof OnboardingState]
                                ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400'
                                : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-700 dark:text-zinc-500'
                        "
                    >
                        <svg v-if="state[task.key as keyof OnboardingState]" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span v-else class="text-sm font-medium">{{ index + 1 }}</span>
                    </div>
                    <div class="flex-1">
                        <h3
                            class="text-sm font-medium"
                            :class="state[task.key as keyof OnboardingState] ? 'text-zinc-500 line-through dark:text-zinc-500' : 'text-zinc-900 dark:text-white'"
                        >
                            {{ task.title }}
                        </h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ task.description }}</p>
                    </div>
                    <Link
                        v-if="task.action && !state[task.key as keyof OnboardingState]"
                        :href="task.action"
                        class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white transition-all hover:bg-violet-700 active:scale-[0.97]"
                    >
                        Start
                    </Link>
                </div>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="h-1 bg-zinc-200 dark:bg-zinc-700">
            <div
                class="h-full bg-gradient-to-r from-violet-500 to-purple-600 transition-all duration-500"
                :style="{
                    width: `${(Object.values(state).filter((v) => v === true).length / tasks.length) * 100}%`,
                }"
            />
        </div>
    </Motion>
</template>
