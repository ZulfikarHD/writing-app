<script setup lang="ts">
import { ref } from 'vue';

interface PromptMessage {
    id: string;
    role: 'user' | 'assistant';
    content: string;
}

interface Variable {
    name: string;
    description: string;
}

interface Props {
    message: PromptMessage;
    index: number;
    isEditable: boolean;
    availableVariables: Variable[];
}

defineProps<Props>();

const emit = defineEmits<{
    'update:content': [value: string];
    toggleRole: [];
    duplicate: [];
    remove: [];
    insertVariable: [variableName: string];
}>();

const showVariables = ref(false);

function formatVariable(name: string): string {
    return '{' + name + '}';
}
</script>

<template>
    <div
        class="group rounded-lg border transition-colors"
        :class="[
            message.role === 'user'
                ? 'border-blue-200 bg-blue-50/50 dark:border-blue-900 dark:bg-blue-950/20'
                : 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-900 dark:bg-emerald-950/20',
        ]"
    >
        <!-- Message Header -->
        <div class="flex items-center justify-between border-b px-3 py-1.5"
             :class="[
                 message.role === 'user'
                     ? 'border-blue-200 dark:border-blue-900'
                     : 'border-emerald-200 dark:border-emerald-900',
             ]"
        >
            <div class="flex items-center gap-2">
                <!-- Drag Handle -->
                <div
                    v-if="isEditable"
                    class="cursor-grab text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                    </svg>
                </div>

                <!-- Role Badge -->
                <button
                    type="button"
                    :disabled="!isEditable"
                    class="flex items-center gap-1 rounded px-2 py-0.5 text-xs font-medium transition-colors"
                    :class="[
                        message.role === 'user'
                            ? 'bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-300'
                            : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900 dark:text-emerald-300',
                        !isEditable && 'cursor-default',
                    ]"
                    :title="isEditable ? 'Click to toggle role' : undefined"
                    @click="emit('toggleRole')"
                >
                    <svg v-if="message.role === 'user'" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <svg v-else class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ message.role === 'user' ? 'User' : 'AI' }}
                    <svg v-if="isEditable" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                    </svg>
                </button>

                <span class="text-xs text-zinc-400">Message {{ index + 1 }}</span>
            </div>

            <!-- Actions -->
            <div v-if="isEditable" class="flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                <!-- Insert Variable -->
                <div class="relative">
                    <button
                        type="button"
                        class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        title="Insert variable"
                        @click="showVariables = !showVariables"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </button>

                    <!-- Variable Dropdown -->
                    <div
                        v-if="showVariables"
                        class="absolute right-0 top-full z-10 mt-1 max-h-48 w-48 overflow-y-auto rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                    >
                        <button
                            v-for="variable in availableVariables"
                            :key="variable.name"
                            type="button"
                            class="block w-full px-3 py-1.5 text-left text-xs hover:bg-zinc-100 dark:hover:bg-zinc-700"
                            @click="emit('insertVariable', variable.name); showVariables = false"
                        >
                            <div class="font-mono text-violet-600 dark:text-violet-400">{{ formatVariable(variable.name) }}</div>
                            <div class="text-zinc-500 dark:text-zinc-400">{{ variable.description }}</div>
                        </button>
                    </div>
                </div>

                <!-- Duplicate -->
                <button
                    type="button"
                    class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                    title="Duplicate message"
                    @click="emit('duplicate')"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>

                <!-- Remove -->
                <button
                    type="button"
                    class="rounded p-1 text-zinc-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400"
                    title="Remove message"
                    @click="emit('remove')"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Message Content -->
        <div class="p-3">
            <textarea
                :value="message.content"
                :disabled="!isEditable"
                rows="3"
                :placeholder="message.role === 'user' ? 'Enter user message...' : 'Enter AI response template...'"
                class="w-full resize-y rounded-lg border-0 bg-transparent p-0 font-mono text-sm focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:text-zinc-500"
                :class="[
                    message.role === 'user'
                        ? 'placeholder-blue-400 dark:placeholder-blue-600'
                        : 'placeholder-emerald-400 dark:placeholder-emerald-600',
                ]"
                @input="emit('update:content', ($event.target as HTMLTextAreaElement).value)"
            ></textarea>
        </div>
    </div>
</template>
