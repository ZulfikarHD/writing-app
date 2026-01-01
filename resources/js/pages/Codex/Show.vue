<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue';
import Toast from '@/components/ui/Toast.vue';
import { AliasManager, CategoryManager, DetailManager, MentionHeatmap, ProgressionManager, RelationGraph, RelationManager, ResearchTab, TagManager, TrackingToggle } from '@/components/codex';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, computed, onMounted, onUnmounted } from 'vue';

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

interface DetailPreset {
    index: number;
    name: string;
    type: string;
    options: string[] | null;
    ai_visibility: string;
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

interface Mention {
    id: number;
    mention_count: number;
    scene: Scene;
}

interface ExternalLink {
    id: number;
    title: string;
    url: string;
    notes: string | null;
    sort_order: number;
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
    tags: Tag[]; // Sprint 14
    mentions: Mention[];
    external_links: ExternalLink[];
}

interface SceneOption {
    id: number;
    title: string | null;
    chapter: { id: number; title: string } | null;
}

const props = defineProps<{
    novel: { id: number; title: string };
    entry: CodexEntry;
    types: string[];
    contextModes: string[];
    scenes: SceneOption[];
    // Sprint 14
    availableTags?: Tag[];
    detailDefinitions?: DetailDefinition[];
    detailPresets?: DetailPreset[];
    aiVisibilityModes?: string[];
    detailTypes?: string[];
}>();

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

// State
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

// Tab state for Description/Research tabs (Sprint 13)
const activeTab = ref<'description' | 'research'>('description');

const showToast = (variant: 'info' | 'success' | 'warning' | 'danger', title: string, message: string) => {
    toast.value = { show: true, variant, title, message };
};

const getTypeLabel = (type: string) => typeConfig[type]?.label || type;
const getTypeIcon = (type: string) => typeConfig[type]?.icon || '📄';
const getTypeColor = (type: string) => typeConfig[type]?.color || 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300';

const handleArchive = () => {
    confirmDialog.value = {
        show: true,
        title: props.entry.is_archived ? 'Restore Entry' : 'Archive Entry',
        message: props.entry.is_archived
            ? `Are you sure you want to restore "${props.entry.name}"?`
            : `Are you sure you want to archive "${props.entry.name}"? It will be hidden from the Codex list.`,
        variant: 'warning',
        loading: false,
        onConfirm: async () => {
            confirmDialog.value.loading = true;
            try {
                const endpoint = props.entry.is_archived ? 'restore' : 'archive';
                await axios.post(`/api/codex/${props.entry.id}/${endpoint}`);
                router.reload();
                showToast('success', props.entry.is_archived ? 'Entry Restored' : 'Entry Archived', '');
            } catch {
                showToast('danger', 'Error', 'Failed to update entry status');
            } finally {
                confirmDialog.value.loading = false;
                confirmDialog.value.show = false;
            }
        },
    };
};

const handleDelete = () => {
    confirmDialog.value = {
        show: true,
        title: 'Delete Entry',
        message: `Are you sure you want to delete "${props.entry.name}"? This action cannot be undone.`,
        variant: 'danger',
        loading: false,
        onConfirm: async () => {
            confirmDialog.value.loading = true;
            try {
                await axios.delete(`/api/codex/${props.entry.id}`);
                router.visit(`/novels/${props.novel.id}/codex`);
            } catch {
                showToast('danger', 'Error', 'Failed to delete entry');
                confirmDialog.value.loading = false;
                confirmDialog.value.show = false;
            }
        },
    };
};

const handleDataUpdated = () => {
    router.reload({ only: ['entry'] });
};

/**
 * Duplicate the current entry (Sprint 15: F-12.7.2).
 * Deep clones the entry including aliases, details, and progressions.
 */
const duplicating = ref(false);

const handleDuplicate = async () => {
    duplicating.value = true;
    try {
        const response = await axios.post(`/api/codex/${props.entry.id}/duplicate`);
        if (response.data.redirect) {
            showToast('success', 'Entry Duplicated', `Created "${response.data.entry.name}"`);
            router.visit(response.data.redirect);
        }
    } catch {
        showToast('danger', 'Error', 'Failed to duplicate entry');
    } finally {
        duplicating.value = false;
    }
};

const rescanning = ref(false);

const handleRescanMentions = async () => {
    rescanning.value = true;
    try {
        await axios.post(`/api/codex/${props.entry.id}/rescan-mentions`);
        router.reload({ only: ['entry'] });
        showToast('success', 'Scan Complete', 'Mentions have been updated');
    } catch {
        showToast('danger', 'Error', 'Failed to rescan mentions');
    } finally {
        rescanning.value = false;
    }
};

const totalMentions = computed(() => props.entry.mentions.reduce((sum, m) => sum + m.mention_count, 0));

// Navigate to related entry
const handleSelectRelatedEntry = (entryId: number) => {
    router.visit(`/codex/${entryId}`);
};

// Auto-refresh mentions polling (Sprint 13: US-12.1)
// Automatically updates mentions when scenes are saved in the editor
const POLL_INTERVAL = 5000; // 5 seconds - fast enough to feel "live"
let pollTimer: ReturnType<typeof setInterval> | null = null;
let lastMentionHash = ''; // Track changes to avoid unnecessary updates

const getMentionHash = (mentions: Mention[]) => {
    return mentions.map(m => `${m.scene.id}:${m.mention_count}`).sort().join(',');
};

const pollMentions = async () => {
    // Don't poll if tab is hidden (save resources)
    if (document.hidden) return;

    try {
        const response = await axios.get(`/api/codex/${props.entry.id}`);
        const newMentions = response.data.mentions || [];
        const newHash = getMentionHash(newMentions);

        // Only reload if mentions actually changed
        if (newHash !== lastMentionHash) {
            // Sprint 16: Show toast notification for new mentions
            showToast('info', 'Mentions Updated', 'New mentions detected in your scenes');
            lastMentionHash = newHash;
            router.reload({ only: ['entry'] });
        }
    } catch {
        // Silently fail - don't disrupt the user
    }
};

const startPolling = () => {
    lastMentionHash = getMentionHash(props.entry.mentions);
    pollTimer = setInterval(pollMentions, POLL_INTERVAL);
};

const stopPolling = () => {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
};

// Handle visibility change - pause polling when tab is hidden
const handleVisibilityChange = () => {
    if (document.hidden) {
        stopPolling();
    } else {
        startPolling();
    }
};

onMounted(() => {
    startPolling();
    document.addEventListener('visibilitychange', handleVisibilityChange);
});

onUnmounted(() => {
    stopPolling();
    document.removeEventListener('visibilitychange', handleVisibilityChange);
});
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <Head :title="`${entry.name} - Codex`" />

        <!-- Header -->
        <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto max-w-5xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="`/novels/${novel.id}/codex`"
                            class="flex items-center gap-2 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            Back to Codex
                        </Link>
                        <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-700" />
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ novel.title }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button :href="`/codex/${entry.id}/edit`" as="a" variant="secondary" size="sm">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </Button>
                        <!-- Duplicate Button (Sprint 15: F-12.7.2) -->
                        <Button variant="ghost" size="sm" :loading="duplicating" @click="handleDuplicate">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Duplicate
                        </Button>
                        <Button variant="ghost" size="sm" @click="handleArchive">
                            {{ entry.is_archived ? 'Restore' : 'Archive' }}
                        </Button>
                        <Button variant="danger" size="sm" @click="handleDelete">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </Button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Entry Header -->
            <div class="mb-8 flex items-start gap-6">
                <!-- Thumbnail -->
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl text-4xl" :class="getTypeColor(entry.type)">
                    {{ getTypeIcon(entry.type) }}
                </div>

                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ entry.name }}</h1>
                        <span v-if="entry.is_archived" class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                            Archived
                        </span>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-sm font-medium" :class="getTypeColor(entry.type)">
                            {{ getTypeIcon(entry.type) }} {{ getTypeLabel(entry.type) }}
                        </span>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">
                            AI: {{ contextModeLabels[entry.ai_context_mode] }}
                        </span>
                        <span v-if="totalMentions > 0" class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ totalMentions }} {{ totalMentions === 1 ? 'mention' : 'mentions' }}
                        </span>
                    </div>

                    <!-- Aliases Preview -->
                    <div v-if="entry.aliases.length > 0" class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">Also known as:</span>
                        <span
                            v-for="alias in entry.aliases.slice(0, 5)"
                            :key="alias.id"
                            class="inline-flex rounded-full bg-zinc-100 px-2.5 py-0.5 text-sm text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300"
                        >
                            {{ alias.alias }}
                        </span>
                        <span v-if="entry.aliases.length > 5" class="text-sm text-zinc-400 dark:text-zinc-500">
                            +{{ entry.aliases.length - 5 }} more
                        </span>
                    </div>

                    <!-- Mention Heatmap -->
                    <div class="mt-4">
                        <MentionHeatmap :mentions="entry.mentions" :novel-id="novel.id" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Column -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Description & Research Tabs (Sprint 13) -->
                    <Card>
                        <!-- Tab Header -->
                        <div class="mb-4 flex items-center gap-1 border-b border-zinc-200 dark:border-zinc-700">
                            <button
                                type="button"
                                class="relative px-4 py-2 text-sm font-medium transition-colors"
                                :class="activeTab === 'description'
                                    ? 'text-violet-600 dark:text-violet-400'
                                    : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                @click="activeTab = 'description'"
                            >
                                Description
                                <span v-if="activeTab === 'description'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-violet-600 dark:bg-violet-400" />
                            </button>
                            <button
                                type="button"
                                class="relative flex items-center gap-1.5 px-4 py-2 text-sm font-medium transition-colors"
                                :class="activeTab === 'research'
                                    ? 'text-violet-600 dark:text-violet-400'
                                    : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                @click="activeTab = 'research'"
                            >
                                Research
                                <span class="rounded bg-amber-100 px-1.5 py-0.5 text-xs text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                    Private
                                </span>
                                <span v-if="activeTab === 'research'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-violet-600 dark:bg-violet-400" />
                            </button>
                        </div>

                        <!-- Description Tab Content -->
                        <div v-show="activeTab === 'description'">
                            <div v-if="entry.description" class="prose prose-zinc dark:prose-invert max-w-none">
                                <p class="whitespace-pre-wrap text-zinc-600 dark:text-zinc-300">{{ entry.description }}</p>
                            </div>
                            <p v-else class="text-sm text-zinc-400 italic dark:text-zinc-500">No description provided</p>
                            <p class="mt-4 text-xs text-zinc-500 dark:text-zinc-400">
                                <span class="font-medium">Note:</span> This description is sent to AI for context. Write in 3rd person for best results.
                            </p>
                        </div>

                        <!-- Research Tab Content (Sprint 13: US-12.3) -->
                        <div v-show="activeTab === 'research'">
                            <ResearchTab
                                :entry-id="entry.id"
                                :research-notes="entry.research_notes"
                                :external-links="entry.external_links"
                                @updated="handleDataUpdated"
                            />
                        </div>
                    </Card>

                    <!-- Details -->
                    <Card>
                        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Details</h2>
                        <DetailManager
                            :entry-id="entry.id"
                            :entry-type="entry.type"
                            :details="entry.details"
                            :detail-presets="detailPresets"
                            @updated="handleDataUpdated"
                        />
                    </Card>

                    <!-- Relations -->
                    <Card>
                        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Relations</h2>
                        
                        <!-- Relation Graph Visualization -->
                        <div class="mb-6">
                            <RelationGraph
                                :entry="{ id: entry.id, name: entry.name, type: entry.type }"
                                :outgoing-relations="entry.outgoing_relations"
                                :incoming-relations="entry.incoming_relations"
                                @select-entry="handleSelectRelatedEntry"
                            />
                        </div>

                        <RelationManager
                            :entry-id="entry.id"
                            :novel-id="novel.id"
                            :outgoing-relations="entry.outgoing_relations"
                            :incoming-relations="entry.incoming_relations"
                            @updated="handleDataUpdated"
                        />
                    </Card>

                    <!-- Progressions -->
                    <Card>
                        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Progressions</h2>
                        <ProgressionManager
                            :entry-id="entry.id"
                            :novel-id="novel.id"
                            :progressions="entry.progressions"
                            :details="entry.details"
                            :scenes="scenes"
                            @updated="handleDataUpdated"
                        />
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Tags (Sprint 14: US-12.4) -->
                    <TagManager
                        :entry-id="entry.id"
                        :novel-id="novel.id"
                        :assigned-tags="entry.tags"
                        :available-tags="availableTags || []"
                        @updated="handleDataUpdated"
                    />

                    <!-- Aliases -->
                    <Card>
                        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Aliases</h2>
                        <AliasManager
                            :entry-id="entry.id"
                            :aliases="entry.aliases"
                            @updated="handleDataUpdated"
                        />
                    </Card>

                    <!-- Tracking Toggle (Sprint 13: US-12.2) -->
                    <TrackingToggle
                        :entry-id="entry.id"
                        :is-tracking-enabled="entry.is_tracking_enabled"
                        @updated="handleDataUpdated"
                    />

                    <!-- Mentions -->
                    <Card>
                        <div class="mb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Mentions</h2>
                                <span
                                    v-if="entry.is_tracking_enabled"
                                    class="flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                                    title="Auto-updates every 10 seconds when you're editing scenes"
                                >
                                    <!-- Pulsing dot indicator for live updates -->
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
                        <div v-if="entry.mentions.length > 0" class="space-y-2">
                            <Link
                                v-for="mention in entry.mentions"
                                :key="mention.id"
                                :href="`/novels/${novel.id}/write/${mention.scene.id}`"
                                class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 transition-colors hover:border-violet-300 dark:border-zinc-700 dark:hover:border-violet-600"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ mention.scene.title || 'Untitled' }}
                                    </p>
                                    <p v-if="mention.scene.chapter" class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ mention.scene.chapter.title }}
                                    </p>
                                </div>
                                <span class="ml-2 shrink-0 rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                                    {{ mention.mention_count }}
                                </span>
                            </Link>
                        </div>
                        <p v-else class="text-sm text-zinc-400 italic dark:text-zinc-500">Not mentioned in any scenes yet</p>
                    </Card>

                    <!-- Categories -->
                    <Card>
                        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Categories</h2>
                        <CategoryManager
                            :entry-id="entry.id"
                            :novel-id="novel.id"
                            :assigned-categories="entry.categories"
                            @updated="handleDataUpdated"
                        />
                    </Card>

                    <!-- Metadata -->
                    <Card>
                        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Info</h2>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">Created</dt>
                                <dd class="text-zinc-900 dark:text-white">
                                    {{ new Date(entry.created_at).toLocaleDateString() }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">Updated</dt>
                                <dd class="text-zinc-900 dark:text-white">
                                    {{ new Date(entry.updated_at).toLocaleDateString() }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">Aliases</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ entry.aliases.length }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">Details</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ entry.details.length }}</dd>
                            </div>
                        </dl>
                    </Card>
                </div>
            </div>
        </main>

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
        <Toast v-if="toast.show" :variant="toast.variant" :title="toast.title" :duration="5000" position="top-right" @close="toast.show = false">
            {{ toast.message }}
        </Toast>
    </div>
</template>
