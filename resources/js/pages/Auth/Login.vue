<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Card from '@/components/ui/layout/Card.vue';
import Input from '@/components/ui/forms/Input.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Motion } from 'motion-v';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Sign In" />

        <Motion
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
            class="w-full max-w-md"
        >
            <Card class="p-8">
                <div class="mb-8 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Welcome back</h1>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Sign in to continue writing</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <Input
                        v-model="form.email"
                        type="email"
                        label="Email"
                        placeholder="you@example.com"
                        :error="form.errors.email"
                        required
                        autofocus
                    />

                    <Input
                        v-model="form.password"
                        type="password"
                        label="Password"
                        placeholder="••••••••"
                        :error="form.errors.password"
                        required
                    />

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-800"
                            />
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">Remember me</span>
                        </label>
                    </div>

                    <Button type="submit" class="w-full" size="lg" :loading="form.processing" :disabled="form.processing">
                        Sign In
                    </Button>
                </form>

                <p class="mt-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                    Don't have an account?
                    <Link href="/register" class="font-medium text-violet-600 hover:text-violet-700 dark:text-violet-400">
                        Create one
                    </Link>
                </p>
            </Card>
        </Motion>
    </GuestLayout>
</template>
