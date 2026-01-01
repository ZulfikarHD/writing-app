<script setup lang="ts">
import { animate, spring, stagger } from 'motion';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

export interface AIModel {
    id: string;
    name: string;
    context_length?: number;
    pricing?: {
        prompt?: string;
        completion?: string;
    };
}

export interface Connection {
    id: number;
    provider: string;
    provider_name: string;
    name: string;
    is_active: boolean;
    is_default: boolean;
}

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        connectionId?: number;
        placeholder?: string;
        size?: 'sm' | 'md';
    }>(),
    {
        placeholder: 'Select a model',
        size: 'md',
    }
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
    'update:connectionId': [value: number];
}>();

const isOpen = ref(false);
const searchQuery = ref('');
const connections = ref<Connection[]>([]);
const models = ref<AIModel[]>([]);
const loading = ref(false);
const selectedConnectionId = ref<number | null>(props.connectionId || null);

// Fetch user's active connections
const fetchConnections = async () => {
    try {
        const response = await fetch('/api/ai-connections');
        const data = await response.json();
        connections.value = data.connections.filter((c: Connection) => c.is_active);

        // Set default connection if none selected
        if (!selectedConnectionId.value && connections.value.length > 0) {
            const defaultConn = connections.value.find((c) => c.is_default) || connections.value[0];
            selectedConnectionId.value = defaultConn.id;
        }
    } catch {
        connections.value = [];
    }
};

// Fetch models for selected connection
const fetchModels = async () => {
    if (!selectedConnectionId.value) {
        models.value = [];
        return;
    }

    loading.value = true;
    try {
        const response = await fetch(`/api/ai-connections/${selectedConnectionId.value}/models`);
        const data = await response.json();
        models.value = data.models || [];
    } catch {
        models.value = [];
    } finally {
        loading.value = false;
    }
};

// Watch connection changes
watch(selectedConnectionId, () => {
    fetchModels();
    emit('update:connectionId', selectedConnectionId.value!);
});

onMounted(() => {
    fetchConnections();
});

const selectedConnection = computed(() => {
    return connections.value.find((c) => c.id === selectedConnectionId.value);
});

const selectedModel = computed(() => {
    return models.value.find((m) => m.id === props.modelValue);
});

const filteredModels = computed(() => {
    if (!searchQuery.value) return models.value;
    const query = searchQuery.value.toLowerCase();
    return models.value.filter((m) => m.name.toLowerCase().includes(query) || m.id.toLowerCase().includes(query));
});

const selectModel = (model: AIModel) => {
    emit('update:modelValue', model.id);
    isOpen.value = false;
    searchQuery.value = '';
};

const sizeClasses = computed(() => {
    return props.size === 'sm' ? 'px-2.5 py-1.5 text-sm' : 'px-3 py-2';
});

const hasNoConnections = computed(() => {
    return connections.value.length === 0;
});

// Dropdown animation
const dropdownRef = ref<HTMLElement | null>(null);

const onDropdownEnter = async (el: Element) => {
    await nextTick();
    
    // Animate dropdown entrance
    animate(
        el,
        { opacity: [0, 1], transform: ['scale(0.95)', 'scale(1)'] },
        { duration: 0.2, easing: spring({ stiffness: 400, damping: 30 }) }
    );
    
    // Stagger animate model items
    const items = el.querySelectorAll('.model-item');
    if (items.length > 0) {
        animate(
            items,
            { opacity: [0, 1], transform: ['translateX(-10px)', 'translateX(0)'] },
            { duration: 0.3, delay: stagger(0.03), easing: spring({ stiffness: 300, damping: 25 }) }
        );
    }
};

const onDropdownLeave = (el: Element, done: () => void) => {
    animate(
        el,
        { opacity: [1, 0], transform: ['scale(1)', 'scale(0.95)'] },
        { duration: 0.15, easing: spring({ stiffness: 500, damping: 35 }) }
    ).finished.then(done);
};
</script>

<template>
    <div class="relative">
        <!-- No Connections State -->
        <a
            v-if="hasNoConnections"
            href="/settings/ai"
            class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-400 dark:hover:bg-amber-900/30 active:scale-[0.97] transition-transform"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Connect AI Provider
        </a>

        <!-- Model Selector -->
        <div v-else>
            <!-- Trigger Button -->
            <button
                type="button"
                :class="sizeClasses"
                class="inline-flex w-full items-center justify-between gap-2 rounded-lg border border-zinc-200 bg-white text-left transition-all hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-1 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 active:scale-[0.97]"
                @click="isOpen = !isOpen"
            >
                <div class="flex items-center gap-2 overflow-hidden">
                    <span v-if="selectedModel" class="truncate text-zinc-900 dark:text-white">
                        {{ selectedModel.name }}
                    </span>
                    <span v-else class="text-zinc-400 dark:text-zinc-500">{{ placeholder }}</span>
                </div>
                <svg
                    class="h-4 w-4 flex-shrink-0 text-zinc-400 transition-transform"
                    :class="{ 'rotate-180': isOpen }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown -->
            <Transition
                @enter="onDropdownEnter"
                @leave="onDropdownLeave"
                :css="false"
            >
                <div
                    v-if="isOpen"
                    ref="dropdownRef"
                    class="absolute left-0 z-50 mt-1 max-h-80 w-72 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                >
                    <!-- Connection Selector -->
                    <div v-if="connections.length > 1" class="border-b border-zinc-200 p-2 dark:border-zinc-700">
                        <select
                            v-model="selectedConnectionId"
                            class="w-full rounded-md border border-zinc-200 bg-zinc-50 px-2 py-1.5 text-sm dark:border-zinc-600 dark:bg-zinc-700"
                        >
                            <option v-for="conn in connections" :key="conn.id" :value="conn.id">
                                {{ conn.name }} ({{ conn.provider_name }})
                            </option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="border-b border-zinc-200 p-2 dark:border-zinc-700">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search models..."
                            class="w-full rounded-md border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-sm placeholder-zinc-400 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-700 dark:placeholder-zinc-500"
                        />
                    </div>

                    <!-- Models List -->
                    <div class="max-h-56 overflow-y-auto">
                        <div v-if="loading" class="flex items-center justify-center py-8">
                            <svg class="h-5 w-5 animate-spin text-violet-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                        </div>

                        <div v-else-if="filteredModels.length === 0" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No models found
                        </div>

                        <button
                            v-else
                            v-for="model in filteredModels"
                            :key="model.id"
                            type="button"
                            class="model-item flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 active:scale-[0.97] transition-transform"
                            :class="{
                                'bg-violet-50 dark:bg-violet-900/20': model.id === modelValue,
                            }"
                            @click="selectModel(model)"
                        >
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-white">{{ model.name }}</div>
                                <div v-if="model.context_length" class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ (model.context_length / 1000).toFixed(0) }}k context
                                </div>
                            </div>
                            <svg
                                v-if="model.id === modelValue"
                                class="h-4 w-4 text-violet-600 dark:text-violet-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Connection Info -->
                    <div v-if="selectedConnection" class="border-t border-zinc-200 px-3 py-2 dark:border-zinc-700">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            via {{ selectedConnection.name }}
                        </p>
                    </div>
                </div>
            </Transition>

            <!-- Backdrop to close dropdown -->
            <div v-if="isOpen" class="fixed inset-0 z-40" @click="isOpen = false"></div>
        </div>
    </div>
</template>
