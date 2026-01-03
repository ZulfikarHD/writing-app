<script setup lang="ts">
import { ref, computed } from 'vue';
import { animate, stagger } from 'motion';

interface BrainstormingPrompt {
    id: string;
    label: string;
    prompt: string;
    icon?: string;
}

interface BrainstormingCategory {
    id: string;
    name: string;
    icon: string;
    color: string;
    prompts: BrainstormingPrompt[];
}

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
    selectPrompt: [prompt: string];
}>();

const activeCategory = ref<string | null>(null);

// Brainstorming categories with prompts
const categories: BrainstormingCategory[] = [
    {
        id: 'character',
        name: 'Character',
        icon: '👤',
        color: 'violet',
        prompts: [
            {
                id: 'char-backstory',
                label: 'Generate backstory',
                prompt: 'Help me create a compelling backstory for a character. Consider their childhood, formative experiences, and key relationships that shaped who they are today.',
            },
            {
                id: 'char-motivation',
                label: 'Explore motivations',
                prompt: 'What are the deep motivations driving this character? Explore their desires, fears, and what they would sacrifice everything for.',
            },
            {
                id: 'char-conflict',
                label: 'Internal conflicts',
                prompt: 'What internal conflicts does this character struggle with? Explore their contradictions, moral dilemmas, and emotional battles.',
            },
            {
                id: 'char-relationship',
                label: 'Relationship dynamics',
                prompt: 'How does this character relate to others? Describe potential relationship dynamics, conflicts, and how they change people around them.',
            },
            {
                id: 'char-voice',
                label: 'Character voice',
                prompt: 'Help me develop a unique voice for this character. How do they speak? What phrases do they use? What makes their dialogue distinctive?',
            },
            {
                id: 'char-arc',
                label: 'Character arc',
                prompt: 'Design a compelling character arc. Where does this character start emotionally/mentally, and how should they transform by the end of the story?',
            },
        ],
    },
    {
        id: 'plot',
        name: 'Plot',
        icon: '📖',
        color: 'blue',
        prompts: [
            {
                id: 'plot-twist',
                label: 'Plot twist ideas',
                prompt: 'Suggest some unexpected plot twists that would surprise readers while still feeling earned and logical within the story.',
            },
            {
                id: 'plot-conflict',
                label: 'Escalate conflict',
                prompt: 'How can I escalate the conflict in my story? What obstacles, complications, or setbacks would raise the stakes?',
            },
            {
                id: 'plot-pacing',
                label: 'Improve pacing',
                prompt: 'Help me analyze and improve the pacing of my story. Where might it be dragging? Where might it be rushing?',
            },
            {
                id: 'plot-subplot',
                label: 'Subplot ideas',
                prompt: 'Suggest meaningful subplots that could enrich the main narrative and add depth to the characters.',
            },
            {
                id: 'plot-resolution',
                label: 'Resolution options',
                prompt: 'What are some satisfying ways to resolve the main conflict? Consider different endings and their emotional impact.',
            },
            {
                id: 'plot-foreshadow',
                label: 'Foreshadowing',
                prompt: 'Help me plant subtle foreshadowing for upcoming events. What hints can I drop that readers will appreciate on re-reads?',
            },
        ],
    },
    {
        id: 'setting',
        name: 'Setting',
        icon: '🏰',
        color: 'emerald',
        prompts: [
            {
                id: 'setting-describe',
                label: 'Describe location',
                prompt: 'Help me write a vivid, sensory description of this location. Include sights, sounds, smells, and the overall atmosphere.',
            },
            {
                id: 'setting-history',
                label: 'Location history',
                prompt: 'What is the history of this place? What events shaped it? What secrets might it hold?',
            },
            {
                id: 'setting-mood',
                label: 'Atmosphere & mood',
                prompt: 'How can I use this setting to enhance the mood of the scene? What environmental details would reinforce the emotional tone?',
            },
            {
                id: 'setting-unique',
                label: 'Unique details',
                prompt: 'What unique, memorable details could make this setting distinctive? What would a character notice first?',
            },
            {
                id: 'setting-culture',
                label: 'Cultural elements',
                prompt: 'What cultural elements exist in this setting? Consider customs, traditions, social norms, and daily life.',
            },
            {
                id: 'setting-contrast',
                label: 'Setting contrast',
                prompt: 'How does this setting contrast with other locations in the story? What does moving between places reveal about characters?',
            },
        ],
    },
    {
        id: 'world',
        name: 'World',
        icon: '🌍',
        color: 'amber',
        prompts: [
            {
                id: 'world-rules',
                label: 'World rules',
                prompt: 'Help me establish the fundamental rules of this world. What makes it different from our reality? What are its limitations?',
            },
            {
                id: 'world-magic',
                label: 'Magic/Technology system',
                prompt: 'Design or refine the magic system or technology in this world. What are its costs, limitations, and societal impacts?',
            },
            {
                id: 'world-politics',
                label: 'Political landscape',
                prompt: 'What are the political forces at play in this world? Who holds power? What tensions exist between factions?',
            },
            {
                id: 'world-economy',
                label: 'Economy & trade',
                prompt: 'How does the economy work in this world? What do people trade? What resources are valuable and why?',
            },
            {
                id: 'world-religion',
                label: 'Beliefs & religion',
                prompt: 'What do people believe in this world? What religions, philosophies, or superstitions shape their worldview?',
            },
            {
                id: 'world-conflict',
                label: 'World conflicts',
                prompt: 'What large-scale conflicts exist in this world? What historical events led to current tensions?',
            },
        ],
    },
];

// Get color classes for category
const getCategoryColors = (color: string) => {
    const colors: Record<string, { bg: string; border: string; text: string; hover: string }> = {
        violet: {
            bg: 'bg-violet-50 dark:bg-violet-900/20',
            border: 'border-violet-200 dark:border-violet-800',
            text: 'text-violet-700 dark:text-violet-300',
            hover: 'hover:bg-violet-100 dark:hover:bg-violet-900/30',
        },
        blue: {
            bg: 'bg-blue-50 dark:bg-blue-900/20',
            border: 'border-blue-200 dark:border-blue-800',
            text: 'text-blue-700 dark:text-blue-300',
            hover: 'hover:bg-blue-100 dark:hover:bg-blue-900/30',
        },
        emerald: {
            bg: 'bg-emerald-50 dark:bg-emerald-900/20',
            border: 'border-emerald-200 dark:border-emerald-800',
            text: 'text-emerald-700 dark:text-emerald-300',
            hover: 'hover:bg-emerald-100 dark:hover:bg-emerald-900/30',
        },
        amber: {
            bg: 'bg-amber-50 dark:bg-amber-900/20',
            border: 'border-amber-200 dark:border-amber-800',
            text: 'text-amber-700 dark:text-amber-300',
            hover: 'hover:bg-amber-100 dark:hover:bg-amber-900/30',
        },
    };
    return colors[color] || colors.violet;
};

// Current category prompts
const currentCategory = computed(() => {
    return categories.find((c) => c.id === activeCategory.value) || null;
});

// Select a prompt
const selectPrompt = (prompt: BrainstormingPrompt) => {
    emit('selectPrompt', prompt.prompt);
    emit('close');
};

// Toggle category
const toggleCategory = (categoryId: string) => {
    if (activeCategory.value === categoryId) {
        activeCategory.value = null;
    } else {
        activeCategory.value = categoryId;
    }
};

// Animation
const onPanelEnter = (el: Element) => {
    animate(
        el,
        { opacity: [0, 1], transform: ['translateY(10px)', 'translateY(0)'] },
        { duration: 0.25, easing: [0.16, 1, 0.3, 1] }
    );

    const items = el.querySelectorAll('.brainstorm-item');
    if (items.length > 0) {
        animate(
            items,
            { opacity: [0, 1], transform: ['scale(0.95)', 'scale(1)'] },
            { duration: 0.2, delay: stagger(0.03), easing: [0.16, 1, 0.3, 1] }
        );
    }
};

const onPanelLeave = (el: Element, done: () => void) => {
    animate(
        el,
        { opacity: [1, 0], transform: ['translateY(0)', 'translateY(10px)'] },
        { duration: 0.15, easing: [0.16, 1, 0.3, 1] }
    ).finished.then(done);
};
</script>

<template>
    <Transition @enter="onPanelEnter" @leave="onPanelLeave" :css="false">
        <div
            v-if="show"
            class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <div class="flex items-center gap-2">
                    <span class="text-lg">💡</span>
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Brainstorming</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Quick prompts to spark creativity</p>
                    </div>
                </div>
                <button
                    type="button"
                    class="rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                    @click="emit('close')"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Category Grid -->
            <div class="p-3">
                <div v-if="!activeCategory" class="grid grid-cols-2 gap-2">
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        type="button"
                        class="brainstorm-item group flex flex-col items-center gap-2 rounded-xl border p-4 transition-all active:scale-95"
                        :class="[
                            getCategoryColors(category.color).bg,
                            getCategoryColors(category.color).border,
                            getCategoryColors(category.color).hover,
                        ]"
                        @click="toggleCategory(category.id)"
                    >
                        <span class="text-2xl">{{ category.icon }}</span>
                        <span class="text-sm font-medium" :class="getCategoryColors(category.color).text">
                            {{ category.name }}
                        </span>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ category.prompts.length }} prompts
                        </span>
                    </button>
                </div>

                <!-- Prompts List -->
                <div v-else>
                    <!-- Back button -->
                    <button
                        type="button"
                        class="mb-3 flex items-center gap-1.5 text-sm text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300"
                        @click="activeCategory = null"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to categories
                    </button>

                    <!-- Category header -->
                    <div
                        class="mb-3 flex items-center gap-2 rounded-lg p-2"
                        :class="getCategoryColors(currentCategory!.color).bg"
                    >
                        <span class="text-xl">{{ currentCategory!.icon }}</span>
                        <span class="font-medium" :class="getCategoryColors(currentCategory!.color).text">
                            {{ currentCategory!.name }} Prompts
                        </span>
                    </div>

                    <!-- Prompts -->
                    <div class="max-h-64 space-y-2 overflow-y-auto">
                        <button
                            v-for="prompt in currentCategory!.prompts"
                            :key="prompt.id"
                            type="button"
                            class="brainstorm-item w-full rounded-lg border border-zinc-200 bg-white p-3 text-left transition-all hover:border-violet-300 hover:bg-violet-50 active:scale-[0.99] dark:border-zinc-700 dark:bg-zinc-800/50 dark:hover:border-violet-600 dark:hover:bg-violet-900/20"
                            @click="selectPrompt(prompt)"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ prompt.label }}
                                    </div>
                                    <div class="mt-1 line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ prompt.prompt }}
                                    </div>
                                </div>
                                <svg
                                    class="h-4 w-4 shrink-0 text-zinc-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer hint -->
            <div class="border-t border-zinc-200 px-4 py-2 dark:border-zinc-700">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Click a prompt to insert it into chat
                </p>
            </div>
        </div>
    </Transition>
</template>
