<script setup lang="ts">
import Modal from '@/components/ui/layout/Modal.vue';
import Button from '@/components/ui/buttons/Button.vue';
import Toast from '@/components/ui/feedback/Toast.vue';
import ConfirmDialog from '@/components/ui/overlays/ConfirmDialog.vue';
import { AliasManager, CategoryManager, DetailManager, ProgressionManager, RelationGraph, RelationManager, ResearchTab, TagManager } from '@/components/codex';
import { router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import axios from 'axios';
import { ref, watch, computed } from 'vue';
import { usePerformanceMode } from '@/composables/usePerformanceMode';

interface Alias {
    id: number;
    alias: string;
}

interface DetailDefinition {
    id: number;
    name: string;
    type: string;
    options: string[] | null;
    show_in_sidebar: boolean;
}

interface Detail {
    id: number;
    key_name: string;
    value: string;
    sort_order: number;
    definition_id: number | null;
    ai_visibility: 'always' | 'never' | 'nsfw_only';
    type: string;
    definition: DetailDefinition | null;
}

interface Tag {
    id: number;
    name: string;
    color: string | null;
    is_predefined?: boolean;
}

interface RelationEntry {
    id: number;
    name: string;
    type: string;
}

interface Relation {
    id: number;
    relation_type: string;
    label: string | null;
    is_bidirectional: boolean;
    target?: RelationEntry;
    source?: RelationEntry;
}

interface Scene {
    id: number;
    title: string | null;
    chapter: { id: number; title: string } | null;
}

interface Progression {
    id: number;
    story_timestamp: string | null;
    note: string;
    new_value: string | null;
    mode: 'addition' | 'replace';
    scene: Scene | null;
    detail: { id: number; key_name: string } | null;
}

interface Category {
    id: number;
    name: string;
    color: string | null;
}

interface ExternalLink {
    id: number;
    title: string;
    url: string;
    notes: string | null;
    sort_order: number;
}

interface Mention {
    id: number;
    mention_count: number;
    scene: Scene;
}

interface CodexEntry {
    id: number;
    type: string;
    name: string;
    description: string | null;
    research_notes: string | null;
    thumbnail_path: string | null;
    ai_context_mode: string;
    is_archived: boolean;
    is_tracking_enabled: boolean;
    created_at: string;
    updated_at: string;
    aliases: Alias[];
    details: Detail[];
    outgoing_relations: Relation[];
    incoming_relations: Relation[];
    progressions: Progression[];
    categories: Category[];
    tags: Tag[];
    external_links: ExternalLink[];
    mentions: Mention[];
}

const props = defineProps<{
    show: boolean;
    entryId: number | null;
    novelId: number;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'updated'): void;
}>();

// Performance mode for animations
const { isReducedMotion, quickSpringConfig } = usePerformanceMode();

// State
const loading = ref(false);
const error = ref<string | null>(null);
const entry = ref<CodexEntry | null>(null);
const availableTags = ref<Tag[]>([]);
const scenes = ref<Scene[]>([]);
const activeTab = ref<'details' | 'relations' | 'progressions' | 'mentions' | 'research'>('details');

const confirmDialog = ref({
    show: false,
    title: '',
    message: '',
    variant: 'danger' as 'danger' | 'warning' | 'info',
    loading: false,
    onConfirm: () => {},
});

const toast = ref({
    show: false,
    variant: 'success' as 'info' | 'success' | 'warning' | 'danger',
    title: '',
    message: '',
});

const typeConfig: Record<string, { label: string; icon: string; color: string }> = {
    character: { label: 'Character', icon: '👤', color: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' },
    location: { label: 'Location', icon: '📍', color: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' },
    item: { label: 'Item', icon: '⚔️', color: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' },
    lore: { label: 'Lore', icon: '📜', color: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' },
    organization: { label: 'Organization', icon: '🏛️', color: 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' },
    subplot: { label: 'Subplot', icon: '📖', color: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' },
};

const contextModeLabels: Record<string, string> = {
    always: 'Always include',
    detected: 'When detected',
    manual: 'Manual only',
    never: 'Never include',
};

const getTypeLabel = (type: string) => typeConfig[type]?.label || type;
const getTypeIcon = (type: string) => typeConfig[type]?.icon || '📄';
const getTypeColor = (type: string) => typeConfig[type]?.color || 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300';

const showToast = (variant: 'info' | 'success' | 'warning' | 'danger', title: string, message: string) => {
    toast.value = { show: true, variant, title, message };
};

// Additional state
const duplicating = ref(false);
const rescanning = ref(false);
const availableCategories = ref<Category[]>([]);

// Description editing state
const isEditingDescription = ref(false);
const editingDescription = ref('');
const savingDescription = ref(false);

// Tracking toggle state
const togglingTracking = ref(false);

const fetchEntry = async () => {
    if (!props.entryId) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(`/api/codex/${props.entryId}`);
        entry.value = response.data.entry;

        // Fetch additional data
        const [tagsRes, categoriesRes, scenesRes] = await Promise.all([
            axios.get(`/api/novels/${props.novelId}/codex/tags`),
            axios.get(`/api/novels/${props.novelId}/codex/categories`),
            axios.get(`/api/novels/${props.novelId}/chapters`).then(async (res) => {
                // Extract scenes from chapters
                const allScenes: Scene[] = [];
                for (const chapter of res.data.chapters || []) {
                    for (const scene of chapter.scenes || []) {
                        allScenes.push({
                            id: scene.id,
                            title: scene.title,
                            chapter: { id: chapter.id, title: chapter.title },
                        });
                    }
                }
                return allScenes;
            }),
        ]);

        availableTags.value = tagsRes.data.tags || [];
        availableCategories.value = categoriesRes.data.categories || [];
        scenes.value = scenesRes;
    } catch {
        error.value = 'Failed to load entry';
    } finally {
        loading.value = false;
    }
};

// Computed for total mentions
const totalMentions = computed(() => {
    if (!entry.value?.mentions) return 0;
    return entry.value.mentions.reduce((sum, m) => sum + m.mention_count, 0);
});

// Watch for entry ID changes
watch(
    () => props.entryId,
    (newId) => {
        if (newId && props.show) {
            fetchEntry();
        }
    },
    { immediate: true }
);

watch(
    () => props.show,
    (show) => {
        if (show && props.entryId) {
            fetchEntry();
        } else if (!show) {
            entry.value = null;
            activeTab.value = 'details';
        }
    }
);

const handleClose = () => {
    emit('close');
};

// Computed for relations - ensure arrays exist before spreading
const allRelations = computed(() => {
    if (!entry.value) return [];
    const outgoing = entry.value.outgoing_relations || [];
    const incoming = entry.value.incoming_relations || [];
    return [...outgoing, ...incoming];
});

// Archive/Restore
const handleArchive = () => {
    if (!entry.value) return;

    confirmDialog.value = {
        show: true,
        title: entry.value.is_archived ? 'Restore Entry' : 'Archive Entry',
        message: entry.value.is_archived
            ? `Are you sure you want to restore "${entry.value.name}"?`
            : `Are you sure you want to archive "${entry.value.name}"?`,
        variant: 'warning',
        loading: false,
        onConfirm: async () => {
            confirmDialog.value.loading = true;
            try {
                const endpoint = entry.value!.is_archived ? 'restore' : 'archive';
                await axios.post(`/api/codex/${entry.value!.id}/${endpoint}`);
                showToast('success', 'Success', entry.value!.is_archived ? 'Entry restored' : 'Entry archived');
                emit('updated');
                emit('close');
            } catch {
                showToast('danger', 'Error', 'Failed to update entry');
            } finally {
                confirmDialog.value.loading = false;
                confirmDialog.value.show = false;
            }
        },
    };
};

// Delete
const handleDelete = () => {
    if (!entry.value) return;

    confirmDialog.value = {
        show: true,
        title: 'Delete Entry',
        message: `Are you sure you want to permanently delete "${entry.value.name}"? This cannot be undone.`,
        variant: 'danger',
        loading: false,
        onConfirm: async () => {
            confirmDialog.value.loading = true;
            try {
                await axios.delete(`/api/codex/${entry.value!.id}`);
                showToast('success', 'Deleted', 'Entry has been deleted');
                emit('updated');
                emit('close');
            } catch {
                showToast('danger', 'Error', 'Failed to delete entry');
            } finally {
                confirmDialog.value.loading = false;
                confirmDialog.value.show = false;
            }
        },
    };
};

// Duplicate entry
const handleDuplicate = async () => {
    if (!entry.value) return;
    duplicating.value = true;
    try {
        const response = await axios.post(`/api/codex/${entry.value.id}/duplicate`);
        showToast('success', 'Entry Duplicated', `Created "${response.data.entry.name}"`);
        emit('updated');
        // Optionally navigate to the new entry
        if (response.data.redirect) {
            emit('close');
            router.visit(response.data.redirect);
        }
    } catch {
        showToast('danger', 'Error', 'Failed to duplicate entry');
    } finally {
        duplicating.value = false;
    }
};

// Rescan mentions
const handleRescanMentions = async () => {
    if (!entry.value) return;
    rescanning.value = true;
    try {
        await axios.post(`/api/codex/${entry.value.id}/rescan-mentions`);
        await fetchEntry(); // Refresh to get updated mentions
        showToast('success', 'Scan Complete', 'Mentions have been updated');
    } catch {
        showToast('danger', 'Error', 'Failed to rescan mentions');
    } finally {
        rescanning.value = false;
    }
};

// Navigate to edit page
const handleEdit = () => {
    if (!entry.value) return;
    emit('close');
    router.visit(`/codex/${entry.value.id}/edit`);
};

// Navigate to related entry (for RelationGraph)
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const handleSelectRelatedEntry = (relatedEntryId: number) => {
    // Close current modal - parent component handles opening the new entry
    // In future, could emit event with relatedEntryId to open new entry without navigation
    emit('close');
};

// Navigate to scene (for mentions)
const handleMentionClick = (sceneId: number) => {
    emit('close');
    router.visit(`/novels/${props.novelId}/workspace/${sceneId}`);
};

// Format date helper
const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Start editing description
const startEditingDescription = () => {
    editingDescription.value = entry.value?.description || '';
    isEditingDescription.value = true;
};

// Cancel editing description
const cancelEditingDescription = () => {
    isEditingDescription.value = false;
    editingDescription.value = '';
};

// Save description
const saveDescription = async () => {
    if (!entry.value) return;
    savingDescription.value = true;
    try {
        await axios.patch(`/api/codex/${entry.value.id}`, {
            description: editingDescription.value.trim() || null,
        });
        entry.value.description = editingDescription.value.trim() || null;
        isEditingDescription.value = false;
        showToast('success', 'Saved', 'Description updated');
        emit('updated');
    } catch {
        showToast('danger', 'Error', 'Failed to save description');
    } finally {
        savingDescription.value = false;
    }
};

// Toggle tracking
const toggleTracking = async () => {
    if (!entry.value) return;
    togglingTracking.value = true;
    const newValue = !entry.value.is_tracking_enabled;
    try {
        await axios.patch(`/api/codex/${entry.value.id}`, {
            is_tracking_enabled: newValue,
        });
        entry.value.is_tracking_enabled = newValue;
        showToast('success', newValue ? 'Tracking Enabled' : 'Tracking Disabled', '');
        emit('updated');
    } catch {
        showToast('danger', 'Error', 'Failed to update tracking');
    } finally {
        togglingTracking.value = false;
    }
};
</script>

<template>
    <Modal :show="show" size="full" :closable="true" scrollable @close="handleClose">
        <!-- Custom Header -->
        <template v-if="entry" #header>
            <div class="flex items-center gap-3">
                <!-- Type Icon -->
                <div class="relative">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-xl"
                        :class="getTypeColor(entry.type)"
                    >
                        {{ getTypeIcon(entry.type) }}
                    </div>
                    <div
                        v-if="entry.is_archived"
                        class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[10px] text-white"
                        title="Archived"
                    >
                        📦
                    </div>
                </div>
                <div class="min-w-0">
                    <h2 class="truncate text-lg font-bold text-zinc-900 dark:text-white">{{ entry.name }}</h2>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="getTypeColor(entry.type)"
                        >
                            {{ getTypeIcon(entry.type) }} {{ getTypeLabel(entry.type) }}
                        </span>
                        <span class="text-xs text-zinc-400 dark:text-zinc-500">•</span>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">🤖 {{ contextModeLabels[entry.ai_context_mode] || entry.ai_context_mode }}</span>
                        <!-- Compact Tracking Toggle -->
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium transition-colors"
                            :class="entry.is_tracking_enabled
                                ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300'
                                : 'bg-zinc-100 text-zinc-500 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-400'"
                            :disabled="togglingTracking"
                            @click="toggleTracking"
                        >
                            <span v-if="togglingTracking" class="h-2 w-2 animate-spin rounded-full border border-current border-t-transparent"></span>
                            <span v-else-if="entry.is_tracking_enabled" class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span v-else class="h-2 w-2 rounded-full bg-zinc-400"></span>
                            {{ entry.is_tracking_enabled ? 'Tracking' : 'Off' }}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-16">
            <div class="flex flex-col items-center gap-3">
                <div class="h-10 w-10 animate-spin rounded-full border-4 border-violet-600 border-r-transparent" />
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Loading entry...</p>
            </div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="py-16 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <p class="text-red-600 dark:text-red-400">{{ error }}</p>
            <Button class="mt-4" variant="ghost" @click="fetchEntry">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Retry
            </Button>
        </div>

        <!-- Content -->
        <template v-else-if="entry">

            <!-- Enhanced Tabs -->
            <div class="mb-6">
                <nav class="flex gap-1 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                    <button
                        type="button"
                        :class="[
                            'flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all',
                            activeTab === 'details'
                                ? 'bg-white text-violet-700 shadow-sm dark:bg-zinc-700 dark:text-violet-300'
                                : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200',
                        ]"
                        @click="activeTab = 'details'"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Details</span>
                    </button>
                    <button
                        type="button"
                        :class="[
                            'flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all',
                            activeTab === 'relations'
                                ? 'bg-white text-violet-700 shadow-sm dark:bg-zinc-700 dark:text-violet-300'
                                : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200',
                        ]"
                        @click="activeTab = 'relations'"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        <span>Relations</span>
                        <span v-if="allRelations.length > 0" class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-600 dark:text-zinc-200">
                            {{ allRelations.length }}
                        </span>
                    </button>
                    <button
                        type="button"
                        :class="[
                            'flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all',
                            activeTab === 'progressions'
                                ? 'bg-white text-violet-700 shadow-sm dark:bg-zinc-700 dark:text-violet-300'
                                : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200',
                        ]"
                        @click="activeTab = 'progressions'"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <span>Progressions</span>
                        <span v-if="entry.progressions.length > 0" class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-600 dark:text-zinc-200">
                            {{ entry.progressions.length }}
                        </span>
                    </button>
                    <button
                        type="button"
                        :class="[
                            'flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all',
                            activeTab === 'mentions'
                                ? 'bg-white text-violet-700 shadow-sm dark:bg-zinc-700 dark:text-violet-300'
                                : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200',
                        ]"
                        @click="activeTab = 'mentions'"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Mentions</span>
                        <span v-if="totalMentions > 0" class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-600 dark:text-zinc-200">
                            {{ totalMentions }}
                        </span>
                    </button>
                    <button
                        type="button"
                        :class="[
                            'flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all',
                            activeTab === 'research'
                                ? 'bg-white text-violet-700 shadow-sm dark:bg-zinc-700 dark:text-violet-300'
                                : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200',
                        ]"
                        @click="activeTab = 'research'"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <span>Research</span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content with Motion animations -->
            <Motion
                :key="activeTab"
                :initial="isReducedMotion ? { opacity: 0 } : { opacity: 0, x: -12 }"
                :animate="isReducedMotion ? { opacity: 1 } : { opacity: 1, x: 0 }"
                :transition="quickSpringConfig"
                class="relative min-h-[320px]"
            >
                <!-- Details Tab -->
                <div
                    v-if="activeTab === 'details'"
                    class="grid grid-cols-1 gap-6 lg:grid-cols-2"
                >
                    <!-- Left Column: Description, Aliases, Tags -->
                    <div class="space-y-5">
                        <!-- Description Card -->
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                            <div class="mb-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                    <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Description</h3>
                                </div>
                                <button
                                    v-if="!isEditingDescription"
                                    type="button"
                                    class="flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                                    @click="startEditingDescription"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </button>
                            </div>

                            <!-- Edit Mode -->
                            <div v-if="isEditingDescription" class="space-y-3">
                                <textarea
                                    v-model="editingDescription"
                                    rows="5"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                                    placeholder="Write a description for the AI to understand this entry..."
                                    @keydown.escape="cancelEditingDescription"
                                />
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="font-medium">Tip:</span> Write in 3rd person for best AI results.
                                </p>
                                <div class="flex justify-end gap-2">
                                    <Button size="sm" variant="ghost" :disabled="savingDescription" @click="cancelEditingDescription">
                                        Cancel
                                    </Button>
                                    <Button size="sm" :loading="savingDescription" @click="saveDescription">
                                        Save Description
                                    </Button>
                                </div>
                            </div>

                            <!-- View Mode -->
                            <div v-else class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900/50">
                                <p v-if="entry.description" class="whitespace-pre-wrap text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                                    {{ entry.description }}
                                </p>
                                <button
                                    v-else
                                    type="button"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-zinc-200 py-4 text-sm text-zinc-400 transition-colors hover:border-violet-300 hover:text-violet-500 dark:border-zinc-700 dark:text-zinc-500 dark:hover:border-violet-600 dark:hover:text-violet-400"
                                    @click="startEditingDescription"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add description
                                </button>
                            </div>
                        </div>

                        <!-- Aliases Card -->
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                            <div class="mb-3 flex items-center gap-2">
                                <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Aliases</h3>
                                <span class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">Also known as</span>
                            </div>
                            <AliasManager :entry-id="entry.id" :aliases="entry.aliases" @updated="fetchEntry" />
                        </div>

                        <!-- Tags Card -->
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                            <div class="mb-3 flex items-center gap-2">
                                <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Tags</h3>
                            </div>
                            <TagManager
                                :entry-id="entry.id"
                                :novel-id="novelId"
                                :assigned-tags="entry.tags || []"
                                :available-tags="availableTags"
                                @updated="fetchEntry"
                            />
                        </div>

                        <!-- Categories Card -->
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                            <div class="mb-3 flex items-center gap-2">
                                <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Categories</h3>
                            </div>
                            <CategoryManager
                                :entry-id="entry.id"
                                :novel-id="novelId"
                                :assigned-categories="entry.categories || []"
                                @updated="fetchEntry"
                            />
                        </div>

                        <!-- Info/Metadata Card -->
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                            <div class="mb-3 flex items-center gap-2">
                                <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Info</h3>
                            </div>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Created</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ formatDate(entry.created_at) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Updated</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ formatDate(entry.updated_at) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Aliases</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ entry.aliases?.length || 0 }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Details</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ entry.details?.length || 0 }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Relations</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ allRelations.length }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Right Column: Details -->
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="mb-3 flex items-center gap-2">
                            <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Attributes</h3>
                        </div>
                        <DetailManager
                            :entry-id="entry.id"
                            :entry-type="entry.type"
                            :details="entry.details"
                            :novel-id="novelId"
                            @updated="fetchEntry"
                        />
                    </div>
                </div>

                <!-- Relations Tab -->
                <div
                    v-if="activeTab === 'relations'"
                    class="space-y-4"
                >
                    <!-- Relation Graph Visualization -->
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="mb-4 flex items-center gap-2">
                            <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Relation Graph</h3>
                        </div>
                        <RelationGraph
                            :entry="{ id: entry.id, name: entry.name, type: entry.type }"
                            :outgoing-relations="entry.outgoing_relations || []"
                            :incoming-relations="entry.incoming_relations || []"
                            @select-entry="handleSelectRelatedEntry"
                        />
                    </div>

                    <!-- Relation Manager -->
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="mb-4 flex items-center gap-2">
                            <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Manage Connections</h3>
                        </div>
                        <RelationManager
                            :entry-id="entry.id"
                            :entry-name="entry.name"
                            :entry-type="entry.type"
                            :outgoing-relations="entry.outgoing_relations || []"
                            :incoming-relations="entry.incoming_relations || []"
                            :novel-id="novelId"
                            @updated="fetchEntry"
                        />
                    </div>
                </div>

                <!-- Progressions Tab -->
                <div
                    v-if="activeTab === 'progressions'"
                    class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Story Progressions</h3>
                        <span class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">Track changes through your story</span>
                    </div>
                    <ProgressionManager
                        :entry-id="entry.id"
                        :entry-name="entry.name"
                        :progressions="entry.progressions"
                        :details="entry.details"
                        :scenes="scenes"
                        @updated="fetchEntry"
                    />
                </div>

                <!-- Mentions Tab -->
                <div
                    v-if="activeTab === 'mentions'"
                    class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Scene Mentions</h3>
                            <span
                                v-if="entry.is_tracking_enabled"
                                class="flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                                title="Auto-updates when you edit scenes"
                            >
                                <span class="relative flex h-2 w-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                </span>
                                Live
                            </span>
                        </div>
                        <button
                            type="button"
                            :disabled="rescanning"
                            class="flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 disabled:opacity-50 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                            title="Trigger a full rescan now"
                            @click="handleRescanMentions"
                        >
                            <svg
                                class="h-3.5 w-3.5"
                                :class="{ 'animate-spin': rescanning }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ rescanning ? 'Scanning...' : 'Rescan' }}
                        </button>
                    </div>

                    <!-- Mentions List -->
                    <div v-if="entry.mentions?.length > 0" class="space-y-2">
                        <button
                            v-for="mention in entry.mentions"
                            :key="mention.id"
                            type="button"
                            class="flex w-full items-center justify-between rounded-lg border border-zinc-200 p-3 text-left transition-colors hover:border-violet-300 hover:bg-violet-50 dark:border-zinc-700 dark:hover:border-violet-600 dark:hover:bg-violet-900/20"
                            @click="handleMentionClick(mention.scene.id)"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ mention.scene.title || 'Untitled Scene' }}
                                </p>
                                <p v-if="mention.scene.chapter" class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ mention.scene.chapter.title }}
                                </p>
                            </div>
                            <span class="ml-2 shrink-0 rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                                {{ mention.mention_count }} {{ mention.mention_count === 1 ? 'mention' : 'mentions' }}
                            </span>
                        </button>
                    </div>
                    <div v-else class="py-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Not mentioned in any scenes yet</p>
                        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                            Use this entry's name or aliases in your scenes to track mentions
                        </p>
                    </div>
                </div>

                <!-- Research Tab -->
                <div
                    v-if="activeTab === 'research'"
                    class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Research & References</h3>
                    </div>
                    <ResearchTab
                        :entry-id="entry.id"
                        :research-notes="entry.research_notes"
                        :external-links="entry.external_links || []"
                        @updated="fetchEntry"
                    />
                </div>
            </Motion>
        </template>

        <!-- Footer -->
        <template v-if="entry" #footer>
            <div class="flex w-full items-center justify-between">
                <!-- Left side: Danger actions -->
                <div class="flex items-center gap-2">
                    <Button
                        variant="ghost"
                        size="sm"
                        class="gap-1.5"
                        @click="handleArchive"
                    >
                        <svg v-if="entry.is_archived" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                        {{ entry.is_archived ? 'Restore' : 'Archive' }}
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        class="gap-1.5 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                        @click="handleDelete"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </Button>
                </div>

                <!-- Right side: Primary actions -->
                <div class="flex items-center gap-2">
                    <Button
                        variant="ghost"
                        size="sm"
                        class="gap-1.5"
                        :loading="duplicating"
                        @click="handleDuplicate"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Duplicate
                    </Button>
                    <Button
                        variant="secondary"
                        size="sm"
                        class="gap-1.5"
                        @click="handleEdit"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Full Page
                    </Button>
                    <Button variant="ghost" size="sm" @click="handleClose">Close</Button>
                </div>
            </div>
        </template>

        <!-- Confirm Dialog -->
        <ConfirmDialog
            v-model="confirmDialog.show"
            :title="confirmDialog.title"
            :message="confirmDialog.message"
            :variant="confirmDialog.variant"
            :loading="confirmDialog.loading"
            @confirm="confirmDialog.onConfirm"
        />

        <!-- Toast -->
        <Toast
            v-if="toast.show"
            :variant="toast.variant"
            :title="toast.title"
            :duration="3000"
            position="top-right"
            @close="toast.show = false"
        >
            {{ toast.message }}
        </Toast>
    </Modal>
</template>
