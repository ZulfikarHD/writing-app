<script setup lang="ts">
import Alert from '@/components/ui/feedback/Alert.vue';
import Button from '@/components/ui/buttons/Button.vue';
import Card from '@/components/ui/layout/Card.vue';
import ConfirmDialog from '@/components/ui/overlays/ConfirmDialog.vue';
import Input from '@/components/ui/forms/Input.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { computed, ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

defineProps<{
    user: User;
}>();

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success as string | undefined);
const showFlash = ref(true);

const profileForm = useForm({
    name: page.props.auth?.user?.name || '',
    email: page.props.auth?.user?.email || '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showDeleteModal = ref(false);
const deleteForm = useForm({
    password: '',
});

const updateProfile = () => {
    profileForm.patch('/profile', {
        preserveScroll: true,
    });
};

const updatePassword = () => {
    passwordForm.put('/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

const deleteAccount = () => {
    deleteForm.delete('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};
</script>

<template>
    <AuthenticatedLayout title="Profile Settings">
        <Head title="Profile Settings" />

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
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Profile Settings</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Manage your account settings and preferences.</p>
            </Motion>

            <!-- Success Flash Message -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <Alert v-if="flashSuccess && showFlash" variant="success" dismissible class="mb-6" @dismiss="showFlash = false">
                    {{ flashSuccess }}
                </Alert>
            </Transition>

            <!-- Profile Information -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.05 }"
                class="mb-8"
            >
                <Card variant="default">
                    <div class="mb-6">
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white">Profile Information</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Update your account's profile information and email address.</p>
                    </div>

                    <form class="space-y-5" @submit.prevent="updateProfile">
                        <Input v-model="profileForm.name" label="Name" :error="profileForm.errors.name" required />
                        <Input v-model="profileForm.email" type="email" label="Email" :error="profileForm.errors.email" required />

                        <div class="flex justify-end pt-2">
                            <Button type="submit" :loading="profileForm.processing" :disabled="profileForm.processing"> Save Changes </Button>
                        </div>
                    </form>
                </Card>
            </Motion>

            <!-- Update Password -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
                class="mb-8"
            >
                <Card variant="default">
                    <div class="mb-6">
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white">Update Password</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Ensure your account is using a secure password.</p>
                    </div>

                    <form class="space-y-5" @submit.prevent="updatePassword">
                        <Input
                            v-model="passwordForm.current_password"
                            type="password"
                            label="Current Password"
                            :error="passwordForm.errors.current_password"
                            required
                        />
                        <Input v-model="passwordForm.password" type="password" label="New Password" :error="passwordForm.errors.password" required />
                        <Input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            label="Confirm New Password"
                            :error="passwordForm.errors.password_confirmation"
                            required
                        />

                        <div class="flex justify-end pt-2">
                            <Button type="submit" :loading="passwordForm.processing" :disabled="passwordForm.processing"> Update Password </Button>
                        </div>
                    </form>
                </Card>
            </Motion>

            <!-- Delete Account -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.15 }"
            >
                <Card variant="default">
                    <div class="mb-4">
                        <h2 class="text-base font-bold text-red-600 dark:text-red-400">Delete Account</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Once your account is deleted, all of its resources and data will be permanently deleted.
                        </p>
                    </div>

                    <Button variant="danger" @click="showDeleteModal = true">Delete Account</Button>
                </Card>
            </Motion>
        </div>

        <!-- Delete Account Confirmation -->
        <ConfirmDialog
            v-model="showDeleteModal"
            title="Delete Account"
            message="Are you sure you want to delete your account? This action cannot be undone. All of your data will be permanently removed."
            confirm-text="Delete Account"
            cancel-text="Cancel"
            variant="danger"
            :loading="deleteForm.processing"
            @confirm="deleteAccount"
        >
            <form class="mt-4" @submit.prevent="deleteAccount">
                <Input
                    v-model="deleteForm.password"
                    type="password"
                    label="Confirm Password"
                    placeholder="Enter your password to confirm"
                    :error="deleteForm.errors.password"
                    required
                />
            </form>
        </ConfirmDialog>
    </AuthenticatedLayout>
</template>
