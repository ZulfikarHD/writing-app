<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import Input from '@/components/ui/forms/Input.vue';
import Badge from '@/components/ui/Badge.vue';
import { usePrompts } from '@/composables/usePrompts';
import { usePersonas } from '@/composables/usePersonas';
import type { Prompt } from '@/composables/usePrompts';
import type { Persona } from '@/composables/usePersonas';
import PersonaEditor from '@/components/prompts/PersonaEditor.vue';

const emit = defineEmits<{
    (e: 'select', prompt: Prompt): void;
    (e: 'selectPersona', persona: Persona): void;
}>();

const { fetchPromptsByType } = usePrompts();
const { fetchPersonas, personas } = usePersonas();

const prompts = ref<Prompt[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const searchQuery = ref('');
const selectedType = ref('');
const activeSection = ref<'prompts' | 'personas'>('prompts');

// Persona editor state
const showPersonaEditor = ref(false);
const editingPersona = ref<Persona | null>(null);
const isCreatingPersona = ref(false);

const typeConfig: Record<string, { label: string; icon: string; color: string }> = {
    chat: { label: 'Chat', icon: '💬', color: 'text-blue-600 dark:text-blue-400' },
    prose: { label: 'Prose', icon: '✍️', color: 'text-purple-600 dark:text-purple-400' },
    replacement: { label: 'Replace', icon: '🔄', color: 'text-amber-600 dark:text-amber-400' },
    summary: { label: 'Summary', icon: '📝', color: 'text-emerald-600 dark:text-emerald-400' },
};

const types = Object.keys(typeConfig);
const getTypeIcon = (type: string) => typeConfig[type]?.icon || '📄';

const fetchAllPrompts = async () => {
    loading.value = true;
    error.value = null;
    try {
        // Fetch all types
        const results = await Promise.all([
            fetchPromptsByType('chat'),
            fetchPromptsByType('prose'),
            fetchPromptsByType('replacement'),
            fetchPromptsByType('summary'),
        ]);
        prompts.value = results.flat();
    } catch {
        error.value = 'Failed to load prompts';
    } finally {
        loading.value = false;
    }
};

const fetchAllPersonas = async () => {
    try {
        await fetchPersonas();
    } catch {
        // Silent fail
    }
};

// Filtered personas
const filteredPersonas = computed(() => {
    if (!searchQuery.value) return personas.value.filter(p => !p.is_archived);
    
    const query = searchQuery.value.toLowerCase();
    return personas.value.filter(
        (p) =>
            !p.is_archived &&
            (p.name.toLowerCase().includes(query) ||
            p.description?.toLowerCase().includes(query))
    );
});

// Open persona editor
function openNewPersona() {
    editingPersona.value = null;
    isCreatingPersona.value = true;
    showPersonaEditor.value = true;
}

function openEditPersona(persona: Persona) {
    editingPersona.value = persona;
    isCreatingPersona.value = false;
    showPersonaEditor.value = true;
}

function handlePersonaCreated() {
    showPersonaEditor.value = false;
    fetchAllPersonas();
}

function handlePersonaUpdated() {
    fetchAllPersonas();
}

function handlePersonaDeleted() {
    showPersonaEditor.value = false;
    fetchAllPersonas();
}

const filteredPrompts = computed(() => {
    let result = prompts.value;

    if (selectedType.value) {
        result = result.filter((p) => p.type === selectedType.value);
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(
            (p) =>
                p.name.toLowerCase().includes(query) ||
                p.description?.toLowerCase().includes(query)
        );
    }

    return result.slice(0, 20); // Limit for performance
});

const promptsByType = computed(() => {
    const grouped: Record<string, Prompt[]> = {};
    for (const prompt of filteredPrompts.value) {
        if (!grouped[prompt.type]) {
            grouped[prompt.type] = [];
        }
        grouped[prompt.type].push(prompt);
    }
    return grouped;
});

onMounted(() => {
    fetchAllPrompts();
    fetchAllPersonas();
});

// Expose refresh method for parent to call
defineExpose({ refresh: () => { fetchAllPrompts(); fetchAllPersonas(); } });
</script>

<template>
    <div class="space-y-2">
        <!-- Section Tabs -->
        <div class="flex gap-1 border-b border-zinc-200 dark:border-zinc-700">
            <button
                type="button"
                class="flex-1 border-b-2 px-2 py-1.5 text-xs font-medium transition-colors"
                :class="[
                    activeSection === 'prompts'
                        ? 'border-pink-500 text-pink-600 dark:text-pink-400'
                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300',
                ]"
                @click="activeSection = 'prompts'"
            >
                Prompts
            </button>
            <button
                type="button"
                class="flex-1 border-b-2 px-2 py-1.5 text-xs font-medium transition-colors"
                :class="[
                    activeSection === 'personas'
                        ? 'border-violet-500 text-violet-600 dark:text-violet-400'
                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300',
                ]"
                @click="activeSection = 'personas'"
            >
                Personas
                <Badge v-if="personas.filter(p => !p.is_archived).length > 0" variant="secondary" size="sm" class="ml-1">
                    {{ personas.filter(p => !p.is_archived).length }}
                </Badge>
            </button>
        </div>

        <!-- Search -->
        <Input
            v-model="searchQuery"
            :placeholder="activeSection === 'prompts' ? 'Search prompts...' : 'Search personas...'"
            size="sm"
            class="text-xs"
        >
            <template #prefix>
                <svg class="h-3 w-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </template>
        </Input>

        <!-- Prompts Section -->
        <template v-if="activeSection === 'prompts'">
        <!-- Type Filter -->
        <div class="flex flex-wrap gap-1">
            <button
                type="button"
                :class="[
                    'rounded-full px-1.5 py-0.5 text-xs transition-colors',
                    selectedType === ''
                        ? 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300'
                        : 'bg-zinc-100 text-zinc-500 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-400',
                ]"
                @click="selectedType = ''"
            >
                All
            </button>
            <button
                v-for="type in types"
                :key="type"
                type="button"
                :class="[
                    'rounded-full px-1.5 py-0.5 text-xs transition-colors',
                    selectedType === type
                        ? 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300'
                        : 'bg-zinc-100 text-zinc-500 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-400',
                ]"
                :title="typeConfig[type]?.label"
                @click="selectedType = type"
            >
                {{ getTypeIcon(type) }}
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-4">
            <div class="h-4 w-4 animate-spin rounded-full border-2 border-pink-500 border-t-transparent" />
        </div>

        <!-- Error -->
        <div v-else-if="error" class="py-2 text-center text-xs text-red-500">
            {{ error }}
            <button class="ml-1 text-pink-600 hover:underline" @click="fetchAllPrompts">Retry</button>
        </div>

        <!-- Empty -->
        <div v-else-if="filteredPrompts.length === 0" class="py-2 text-center text-xs text-zinc-500 dark:text-zinc-400">
            {{ searchQuery || selectedType ? 'No matches' : 'No prompts yet' }}
        </div>

        <!-- Prompts List -->
        <div v-else class="max-h-48 space-y-2 overflow-y-auto">
            <div v-for="(typePrompts, type) in promptsByType" :key="type">
                <div class="mb-1 flex items-center gap-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                    <span>{{ getTypeIcon(type as string) }}</span>
                    <span>{{ typeConfig[type as string]?.label }}</span>
                </div>
                <div class="space-y-0.5">
                    <button
                        v-for="prompt in typePrompts"
                        :key="prompt.id"
                        type="button"
                        class="flex w-full items-center gap-2 rounded px-2 py-1 text-left text-xs transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-700"
                        @click="emit('select', prompt)"
                    >
                        <span class="truncate font-medium text-zinc-700 dark:text-zinc-300">
                            {{ prompt.name }}
                        </span>
                        <span
                            v-if="prompt.is_system"
                            class="shrink-0 rounded bg-zinc-200 px-1 text-[10px] text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400"
                        >
                            System
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Manage Prompts Link -->
        <Link
            href="/prompts"
            class="flex w-full items-center justify-center gap-1 rounded-md border border-dashed border-zinc-300 px-2 py-1.5 text-xs text-zinc-500 transition-colors hover:border-pink-400 hover:text-pink-600 dark:border-zinc-600 dark:text-zinc-400 dark:hover:border-pink-500 dark:hover:text-pink-400"
        >
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Manage Prompts
        </Link>
        </template>

        <!-- Personas Section -->
        <template v-if="activeSection === 'personas'">
            <!-- Empty -->
            <div v-if="filteredPersonas.length === 0" class="py-4 text-center">
                <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-violet-100 dark:bg-violet-900/30">
                    <svg class="h-5 w-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ searchQuery ? 'No matching personas' : 'No personas yet' }}
                </p>
                <button
                    v-if="!searchQuery"
                    type="button"
                    class="mt-2 text-xs font-medium text-violet-600 hover:text-violet-700 dark:text-violet-400"
                    @click="openNewPersona"
                >
                    Create your first persona
                </button>
            </div>

            <!-- Personas List -->
            <div v-else class="max-h-48 space-y-1.5 overflow-y-auto">
                <button
                    v-for="persona in filteredPersonas"
                    :key="persona.id"
                    type="button"
                    class="group flex w-full items-center gap-2 rounded-lg border border-zinc-200 bg-white p-2 text-left transition-colors hover:border-violet-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-violet-600"
                    @click="emit('selectPersona', persona)"
                >
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-violet-100 dark:bg-violet-900/30">
                        <svg class="h-3 w-3 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1">
                            <span class="truncate text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                {{ persona.name }}
                            </span>
                            <Badge v-if="persona.is_default" variant="info" size="sm">Default</Badge>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 rounded p-1 opacity-0 transition-opacity hover:bg-zinc-100 group-hover:opacity-100 dark:hover:bg-zinc-700"
                        title="Edit persona"
                        @click.stop="openEditPersona(persona)"
                    >
                        <svg class="h-3 w-3 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                </button>
            </div>

            <!-- Create Persona Button -->
            <button
                type="button"
                class="flex w-full items-center justify-center gap-1 rounded-md border border-dashed border-zinc-300 px-2 py-1.5 text-xs text-zinc-500 transition-colors hover:border-violet-400 hover:text-violet-600 dark:border-zinc-600 dark:text-zinc-400 dark:hover:border-violet-500 dark:hover:text-violet-400"
                @click="openNewPersona"
            >
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                New Persona
            </button>
        </template>
    </div>

    <!-- Persona Editor Modal -->
    <PersonaEditor
        :show="showPersonaEditor"
        :persona="editingPersona"
        :is-creating="isCreatingPersona"
        @close="showPersonaEditor = false"
        @created="handlePersonaCreated"
        @updated="handlePersonaUpdated"
        @deleted="handlePersonaDeleted"
        @archived="handlePersonaDeleted"
    />
</template>
