<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import { Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
</script>

<template>
    <Motion
        :initial="{ opacity: 0, scale: 0.95 }"
        :animate="{ opacity: 1, scale: 1 }"
        :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
        class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-200 px-6 py-16 dark:border-zinc-800"
    >
        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900/30 dark:to-purple-900/30">
            <svg class="h-8 w-8 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <h3 class="mb-2 text-lg font-semibold text-zinc-900 dark:text-white">No novels yet</h3>
        <p class="mb-6 max-w-sm text-center text-sm text-zinc-600 dark:text-zinc-400">
            Start your writing journey by creating your first novel. Let your imagination flow!
        </p>
        <Link href="/novels/create">
            <Button size="lg">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Your First Novel
            </Button>
        </Link>
    </Motion>
</template>
