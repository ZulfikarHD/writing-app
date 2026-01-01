<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Textarea from '@/components/ui/forms/Textarea.vue';
import Input from '@/components/ui/forms/Input.vue';
import axios from 'axios';
import { ref, computed, watch } from 'vue';

interface ExternalLink {
    id: number;
    title: string;
    url: string;
    notes: string | null;
    sort_order: number;
}

const props = defineProps<{
    entryId: number;
    researchNotes: string | null;
    externalLinks: ExternalLink[];
}>();

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

// Research notes state
const notes = ref(props.researchNotes || '');
const notesUpdating = ref(false);
const notesDebounceTimer = ref<ReturnType<typeof setTimeout> | null>(null);

// External links state
const links = ref<ExternalLink[]>([...props.externalLinks]);
const showAddLinkForm = ref(false);
const newLink = ref({ title: '', url: '', notes: '' });
const addingLink = ref(false);
const editingLinkId = ref<number | null>(null);
const editLinkData = ref({ title: '', url: '', notes: '' });

// Word count for research notes
const wordCount = computed(() => {
    if (!notes.value) return 0;
    return notes.value.trim().split(/\s+/).filter(Boolean).length;
});

// Watch for external changes to research notes
watch(() => props.researchNotes, (newVal) => {
    if (newVal !== notes.value) {
        notes.value = newVal || '';
    }
});

// Watch for external changes to external links
watch(() => props.externalLinks, (newVal) => {
    links.value = [...newVal];
}, { deep: true });

// Auto-save research notes with debounce
const handleNotesChange = () => {
    if (notesDebounceTimer.value) {
        clearTimeout(notesDebounceTimer.value);
    }

    notesDebounceTimer.value = setTimeout(async () => {
        await saveNotes();
    }, 1000);
};

const saveNotes = async () => {
    notesUpdating.value = true;
    try {
        await axios.patch(`/api/codex/${props.entryId}`, {
            research_notes: notes.value || null,
        });
        emit('updated');
    } catch (error) {
        console.error('Failed to save research notes:', error);
    } finally {
        notesUpdating.value = false;
    }
};

// Add external link
const addLink = async () => {
    if (!newLink.value.title.trim() || !newLink.value.url.trim()) return;

    addingLink.value = true;
    try {
        const response = await axios.post(`/api/codex/${props.entryId}/external-links`, {
            title: newLink.value.title.trim(),
            url: newLink.value.url.trim(),
            notes: newLink.value.notes.trim() || null,
        });

        links.value.push(response.data.link);
        newLink.value = { title: '', url: '', notes: '' };
        showAddLinkForm.value = false;
        emit('updated');
    } catch (error) {
        console.error('Failed to add external link:', error);
    } finally {
        addingLink.value = false;
    }
};

// Edit external link
const startEditLink = (link: ExternalLink) => {
    editingLinkId.value = link.id;
    editLinkData.value = {
        title: link.title,
        url: link.url,
        notes: link.notes || '',
    };
};

const cancelEditLink = () => {
    editingLinkId.value = null;
    editLinkData.value = { title: '', url: '', notes: '' };
};

const saveEditLink = async () => {
    if (!editingLinkId.value) return;

    try {
        await axios.patch(`/api/codex/external-links/${editingLinkId.value}`, {
            title: editLinkData.value.title.trim(),
            url: editLinkData.value.url.trim(),
            notes: editLinkData.value.notes.trim() || null,
        });

        const index = links.value.findIndex(l => l.id === editingLinkId.value);
        if (index !== -1) {
            links.value[index] = {
                ...links.value[index],
                title: editLinkData.value.title.trim(),
                url: editLinkData.value.url.trim(),
                notes: editLinkData.value.notes.trim() || null,
            };
        }

        cancelEditLink();
        emit('updated');
    } catch (error) {
        console.error('Failed to update external link:', error);
    }
};

// Delete external link
const deleteLink = async (linkId: number) => {
    if (!confirm('Are you sure you want to delete this link?')) return;

    try {
        await axios.delete(`/api/codex/external-links/${linkId}`);
        links.value = links.value.filter(l => l.id !== linkId);
        emit('updated');
    } catch (error) {
        console.error('Failed to delete external link:', error);
    }
};

// Format URL for display
const formatUrl = (url: string) => {
    try {
        const urlObj = new URL(url);
        return urlObj.hostname;
    } catch {
        return url;
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Research Notes Section -->
        <div>
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-medium text-zinc-900 dark:text-white">Research Notes</h3>
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                        Not sent to AI
                    </span>
                </div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                    <span>{{ wordCount }} words</span>
                    <span v-if="notesUpdating" class="flex items-center gap-1">
                        <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v4m0 12v4m-8-10h4m12 0h4m-3.2-6.8l-2.8 2.8m-8 8l-2.8 2.8m0-11.2l2.8 2.8m8 8l2.8 2.8" />
                        </svg>
                        Saving...
                    </span>
                </div>
            </div>
            <p class="mb-2 text-xs text-zinc-500 dark:text-zinc-400">
                Store development notes, inspiration sources, or spoilers here. This section is private and will never be included in AI prompts.
            </p>
            <Textarea
                v-model="notes"
                placeholder="Add research notes, character development ideas, backstory details, plot spoilers..."
                rows="6"
                @input="handleNotesChange"
            />
        </div>

        <!-- External Links Section -->
        <div>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-medium text-zinc-900 dark:text-white">External Links</h3>
                <Button v-if="!showAddLinkForm" variant="ghost" size="sm" @click="showAddLinkForm = true">
                    <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Link
                </Button>
            </div>

            <!-- Add Link Form -->
            <div v-if="showAddLinkForm" class="mb-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                <div class="space-y-3">
                    <Input
                        v-model="newLink.title"
                        label="Title"
                        placeholder="e.g., Character Reference Image"
                    />
                    <Input
                        v-model="newLink.url"
                        label="URL"
                        placeholder="https://..."
                        type="url"
                    />
                    <Input
                        v-model="newLink.notes"
                        label="Notes (optional)"
                        placeholder="Brief description..."
                    />
                    <div class="flex justify-end gap-2">
                        <Button variant="ghost" size="sm" @click="showAddLinkForm = false">
                            Cancel
                        </Button>
                        <Button
                            size="sm"
                            :loading="addingLink"
                            :disabled="!newLink.title.trim() || !newLink.url.trim()"
                            @click="addLink"
                        >
                            Add Link
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Links List -->
            <div v-if="links.length > 0" class="space-y-2">
                <div
                    v-for="link in links"
                    :key="link.id"
                    class="group rounded-lg border border-zinc-200 p-3 transition-colors hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600"
                >
                    <!-- View Mode -->
                    <div v-if="editingLinkId !== link.id">
                        <div class="flex items-start justify-between gap-2">
                            <a
                                :href="link.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex-1"
                            >
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    <span class="font-medium text-zinc-900 hover:text-violet-600 dark:text-white dark:hover:text-violet-400">
                                        {{ link.title }}
                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ formatUrl(link.url) }}
                                </p>
                            </a>
                            <div class="flex shrink-0 gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                <button
                                    type="button"
                                    class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                                    @click.stop="startEditLink(link)"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="rounded p-1 text-zinc-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                                    @click.stop="deleteLink(link.id)"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p v-if="link.notes" class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                            {{ link.notes }}
                        </p>
                    </div>

                    <!-- Edit Mode -->
                    <div v-else class="space-y-3">
                        <Input
                            v-model="editLinkData.title"
                            label="Title"
                            placeholder="Link title"
                        />
                        <Input
                            v-model="editLinkData.url"
                            label="URL"
                            placeholder="https://..."
                            type="url"
                        />
                        <Input
                            v-model="editLinkData.notes"
                            label="Notes"
                            placeholder="Brief description..."
                        />
                        <div class="flex justify-end gap-2">
                            <Button variant="ghost" size="sm" @click="cancelEditLink">
                                Cancel
                            </Button>
                            <Button size="sm" @click="saveEditLink">
                                Save
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <p v-else-if="!showAddLinkForm" class="text-sm text-zinc-400 italic dark:text-zinc-500">
                No external links added yet. Add reference images, research sources, or inspiration links.
            </p>
        </div>
    </div>
</template>
