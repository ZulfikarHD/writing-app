<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { animate, stagger } from 'motion';
import { usePrompts, type Prompt } from '@/composables/usePrompts';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
    selectPrompt: [prompt: Prompt];
}>();

const { fetchPromptsByType, recordUsage } = usePrompts();

const isLoading = ref(false);
const prompts = ref<Prompt[]>([]);
const searchQuery = ref('');

// Filter and sort prompts - most used first, then by name
const displayedPrompts = computed(() => {
    let filtered = prompts.value;
    
    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(
            (p) => p.name.toLowerCase().includes(query) || p.description?.toLowerCase().includes(query)
        );
    }
    
    // Sort: most used first, then by name
    return [...filtered].sort((a, b) => {
        if (b.usage_count !== a.usage_count) {
            return b.usage_count - a.usage_count;
        }
        return a.name.localeCompare(b.name);
    });
});

// Recent prompts (top 5 most used)
const recentPrompts = computed(() => {
    return [...prompts.value]
        .filter((p) => p.usage_count > 0)
        .sort((a, b) => b.usage_count - a.usage_count)
        .slice(0, 5);
});

// System prompts
const systemPrompts = computed(() => {
    return displayedPrompts.value.filter((p) => p.is_system);
});

// User prompts
const userPrompts = computed(() => {
    return displayedPrompts.value.filter((p) => !p.is_system);
});

// Load prompts when shown
watch(
    () => props.show,
    async (shown) => {
        if (shown && prompts.value.length === 0) {
            isLoading.value = true;
            prompts.value = await fetchPromptsByType('chat');
            isLoading.value = false;
        }
    },
    { immediate: true }
);

// Select a prompt
const handleSelect = (prompt: Prompt) => {
    recordUsage(prompt.id);
    emit('selectPrompt', prompt);
    emit('close');
};

// Animation
const onDrawerEnter = (el: Element) => {
    animate(
        el,
        { opacity: [0, 1], transform: ['translateY(10px)', 'translateY(0)'] },
        { duration: 0.2, easing: [0.16, 1, 0.3, 1] }
    );

    const items = el.querySelectorAll('.prompt-item');
    if (items.length > 0) {
        animate(
            items,
            { opacity: [0, 1], transform: ['translateX(-10px)', 'translateX(0)'] },
            { duration: 0.2, delay: stagger(0.02), easing: [0.16, 1, 0.3, 1] }
        );
    }
};

const onDrawerLeave = (el: Element, done: () => void) => {
    animate(
        el,
        { opacity: [1, 0], transform: ['translateY(0)', 'translateY(10px)'] },
        { duration: 0.15 }
    ).finished.then(done);
};
</script>

<template>
    <Transition @enter="onDrawerEnter" @leave="onDrawerLeave" :css="false">
        <div
            v-if="show"
            class="absolute bottom-full left-0 z-50 mb-2 w-80 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-800"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-zinc-200 px-3 py-2 dark:border-zinc-700">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-sm font-medium text-zinc-900 dark:text-white">Quick Prompts</span>
                </div>
                <button
                    type="button"
                    class="rounded p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                    @click="emit('close')"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Search -->
            <div class="border-b border-zinc-200 p-2 dark:border-zinc-700">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search prompts..."
                    class="w-full rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-sm placeholder-zinc-400 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-700 dark:placeholder-zinc-500"
                />
            </div>

            <!-- Content -->
            <div class="max-h-64 overflow-y-auto">
                <!-- Loading -->
                <div v-if="isLoading" class="flex items-center justify-center py-8">
                    <div class="h-5 w-5 animate-spin rounded-full border-2 border-violet-600 border-t-transparent"></div>
                </div>

                <!-- Empty state -->
                <div v-else-if="displayedPrompts.length === 0" class="py-8 text-center">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ searchQuery ? 'No prompts found' : 'No chat prompts available' }}
                    </p>
                </div>

                <div v-else>
                    <!-- Recent prompts (if not searching and has recent) -->
                    <div v-if="!searchQuery && recentPrompts.length > 0" class="border-b border-zinc-100 pb-1 dark:border-zinc-700">
                        <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Recently Used</span>
                        </div>
                        <button
                            v-for="prompt in recentPrompts"
                            :key="'recent-' + prompt.id"
                            type="button"
                            class="prompt-item flex w-full items-center gap-2 px-3 py-2 text-left transition-all hover:bg-violet-50 dark:hover:bg-violet-900/20"
                            @click="handleSelect(prompt)"
                        >
                            <div class="flex-1 truncate">
                                <div class="truncate text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ prompt.name }}
                                </div>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- System prompts -->
                    <div v-if="systemPrompts.length > 0">
                        <div class="bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                            System
                        </div>
                        <button
                            v-for="prompt in systemPrompts"
                            :key="'sys-' + prompt.id"
                            type="button"
                            class="prompt-item flex w-full items-center gap-2 px-3 py-2 text-left transition-all hover:bg-zinc-100 dark:hover:bg-zinc-700"
                            @click="handleSelect(prompt)"
                        >
                            <div class="flex-1 min-w-0">
                                <div class="truncate text-sm text-zinc-900 dark:text-white">
                                    {{ prompt.name }}
                                </div>
                                <div v-if="prompt.description" class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ prompt.description }}
                                </div>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- User prompts -->
                    <div v-if="userPrompts.length > 0">
                        <div class="bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                            Custom
                        </div>
                        <button
                            v-for="prompt in userPrompts"
                            :key="'user-' + prompt.id"
                            type="button"
                            class="prompt-item flex w-full items-center gap-2 px-3 py-2 text-left transition-all hover:bg-zinc-100 dark:hover:bg-zinc-700"
                            @click="handleSelect(prompt)"
                        >
                            <div class="flex-1 min-w-0">
                                <div class="truncate text-sm text-zinc-900 dark:text-white">
                                    {{ prompt.name }}
                                </div>
                                <div v-if="prompt.description" class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ prompt.description }}
                                </div>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-zinc-200 px-3 py-2 dark:border-zinc-700">
                <a
                    href="/prompts"
                    class="flex items-center justify-center gap-1 rounded px-2 py-1 text-xs text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                >
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Manage Prompts
                </a>
            </div>
        </div>
    </Transition>
</template>
