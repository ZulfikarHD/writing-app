<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Card from '@/components/ui/layout/Card.vue';
import Input from '@/components/ui/forms/Input.vue';
import Textarea from '@/components/ui/forms/Textarea.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { computed, ref } from 'vue';

interface PenName {
    id: number;
    name: string;
    is_default: boolean;
}

const props = defineProps<{
    penNames: PenName[];
    genres: Record<string, string>;
    povOptions: Record<string, string>;
    tenseOptions: Record<string, string>;
}>();

const currentStep = ref(1);
const totalSteps = 3;

const form = useForm({
    title: '',
    description: '',
    genre: '',
    pov: '',
    tense: '',
    pen_name_id: props.penNames.find((p) => p.is_default)?.id || null,
});

const canProceed = computed(() => {
    if (currentStep.value === 1) {
        return form.title.trim().length > 0;
    }
    return true;
});

const nextStep = () => {
    if (currentStep.value < totalSteps && canProceed.value) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const submit = () => {
    form.post('/novels', {
        preserveScroll: true,
    });
};

const stepTitles = ['Basic Info', 'Writing Style', 'Review'];
</script>

<template>
    <AuthenticatedLayout title="Create Novel">
        <Head title="Create Novel" />

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
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Create New Novel</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Set up your novel in just a few steps.</p>
            </Motion>

            <!-- Progress Steps -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.05 }"
                class="mb-8"
            >
                <div class="flex items-center justify-between">
                    <template v-for="(title, index) in stepTitles" :key="index">
                        <div class="flex items-center">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium transition-colors"
                                :class="
                                    currentStep > index + 1
                                        ? 'bg-violet-600 text-white'
                                        : currentStep === index + 1
                                          ? 'bg-violet-600 text-white'
                                          : 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400'
                                "
                            >
                                <svg v-if="currentStep > index + 1" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span v-else>{{ index + 1 }}</span>
                            </div>
                            <span
                                class="ml-2 hidden text-sm font-medium sm:block"
                                :class="currentStep >= index + 1 ? 'text-zinc-900 dark:text-white' : 'text-zinc-500 dark:text-zinc-400'"
                            >
                                {{ title }}
                            </span>
                        </div>
                        <div
                            v-if="index < stepTitles.length - 1"
                            class="mx-4 h-0.5 flex-1"
                            :class="currentStep > index + 1 ? 'bg-violet-600' : 'bg-zinc-200 dark:bg-zinc-700'"
                        />
                    </template>
                </div>
            </Motion>

            <!-- Form Card -->
            <Motion
                :key="currentStep"
                :initial="{ opacity: 0, x: 20 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
            >
                <Card>
                    <form @submit.prevent="currentStep === totalSteps ? submit() : nextStep()">
                        <!-- Step 1: Basic Info -->
                        <div v-if="currentStep === 1" class="space-y-5">
                            <Input
                                v-model="form.title"
                                label="Novel Title"
                                placeholder="Enter your novel's title"
                                :error="form.errors.title"
                                required
                                autofocus
                            />

                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Description
                                    <span class="text-zinc-400">(optional)</span>
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    placeholder="A brief description of your novel..."
                                    class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors placeholder:text-zinc-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-violet-500"
                                />
                                <p v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Genre</label>
                                <select
                                    v-model="form.genre"
                                    class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-violet-500"
                                >
                                    <option value="">Select a genre</option>
                                    <option v-for="(label, value) in genres" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="penNames.length > 0" class="space-y-1.5">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Pen Name</label>
                                <select
                                    v-model="form.pen_name_id"
                                    class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-violet-500"
                                >
                                    <option :value="null">No pen name</option>
                                    <option v-for="penName in penNames" :key="penName.id" :value="penName.id">
                                        {{ penName.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Step 2: Writing Style -->
                        <div v-if="currentStep === 2" class="space-y-5">
                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Point of View</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        v-for="(label, value) in povOptions"
                                        :key="value"
                                        type="button"
                                        @click="form.pov = value"
                                        class="rounded-lg border px-4 py-3 text-left text-sm transition-all active:scale-[0.98]"
                                        :class="
                                            form.pov === value
                                                ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-500 dark:bg-violet-900/20 dark:text-violet-400'
                                                : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                                        "
                                    >
                                        {{ label }}
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Tense</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        v-for="(label, value) in tenseOptions"
                                        :key="value"
                                        type="button"
                                        @click="form.tense = value"
                                        class="rounded-lg border px-4 py-3 text-left text-sm transition-all active:scale-[0.98]"
                                        :class="
                                            form.tense === value
                                                ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-500 dark:bg-violet-900/20 dark:text-violet-400'
                                                : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                                        "
                                    >
                                        {{ label }}
                                    </button>
                                </div>
                            </div>

                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                These settings help the AI understand your writing style. You can change them later.
                            </p>
                        </div>

                        <!-- Step 3: Review -->
                        <div v-if="currentStep === 3" class="space-y-5">
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <h3 class="mb-4 font-semibold text-zinc-900 dark:text-white">Review Your Novel</h3>
                                <dl class="space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-zinc-500 dark:text-zinc-400">Title</dt>
                                        <dd class="font-medium text-zinc-900 dark:text-white">{{ form.title }}</dd>
                                    </div>
                                    <div v-if="form.description" class="flex justify-between">
                                        <dt class="text-zinc-500 dark:text-zinc-400">Description</dt>
                                        <dd class="max-w-xs truncate text-right font-medium text-zinc-900 dark:text-white">
                                            {{ form.description }}
                                        </dd>
                                    </div>
                                    <div v-if="form.genre" class="flex justify-between">
                                        <dt class="text-zinc-500 dark:text-zinc-400">Genre</dt>
                                        <dd class="font-medium text-zinc-900 dark:text-white">{{ genres[form.genre] }}</dd>
                                    </div>
                                    <div v-if="form.pov" class="flex justify-between">
                                        <dt class="text-zinc-500 dark:text-zinc-400">POV</dt>
                                        <dd class="font-medium text-zinc-900 dark:text-white">{{ povOptions[form.pov] }}</dd>
                                    </div>
                                    <div v-if="form.tense" class="flex justify-between">
                                        <dt class="text-zinc-500 dark:text-zinc-400">Tense</dt>
                                        <dd class="font-medium text-zinc-900 dark:text-white">{{ tenseOptions[form.tense] }}</dd>
                                    </div>
                                    <div v-if="form.pen_name_id" class="flex justify-between">
                                        <dt class="text-zinc-500 dark:text-zinc-400">Pen Name</dt>
                                        <dd class="font-medium text-zinc-900 dark:text-white">
                                            {{ penNames.find((p) => p.id === form.pen_name_id)?.name }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Everything looks good? Click "Create Novel" to get started on your writing journey!
                            </p>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="mt-8 flex items-center justify-between border-t border-zinc-200 pt-6 dark:border-zinc-700">
                            <Button v-if="currentStep > 1" type="button" variant="ghost" @click="prevStep">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Back
                            </Button>
                            <div v-else />

                            <Button
                                v-if="currentStep < totalSteps"
                                type="submit"
                                :disabled="!canProceed"
                            >
                                Next
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Button>
                            <Button v-else type="submit" :loading="form.processing" :disabled="form.processing">
                                Create Novel
                            </Button>
                        </div>
                    </form>
                </Card>
            </Motion>
        </div>
    </AuthenticatedLayout>
</template>
