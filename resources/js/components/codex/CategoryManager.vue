<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Modal from '@/components/ui/Modal.vue';
import Select from '@/components/ui/Select.vue';
import axios from 'axios';
import { ref, computed, watch } from 'vue';

interface Tag {
    id: number;
    name: string;
    color: string | null;
}

interface DetailDefinition {
    id: number;
    name: string;
    type: string;
    options: string[] | null;
}

interface Category {
    id: number;
    name: string;
    color: string | null;
    parent_id: number | null;
    sort_order: number;
    entry_count?: number;
    total_entry_count?: number;
    children?: Category[];
    // Sprint 16: Tag integration fields
    linked_tag_id?: number | null;
    linked_tag?: Tag | null;
    linked_detail_definition_id?: number | null;
    linked_detail_definition?: DetailDefinition | null;
    linked_detail_value?: string | null;
    has_auto_linking?: boolean;
    auto_linked_count?: number;
}

interface PreviewEntry {
    id: number;
    name: string;
    type: string;
}

const props = defineProps<{
    entryId: number;
    novelId: number;
    assignedCategories: { id: number; name: string; color: string | null }[];
}>();

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

// State
const allCategories = ref<Category[]>([]);
const selectedIds = ref<Set<number>>(new Set(props.assignedCategories.map((c) => c.id)));
const loading = ref(false);
const showModal = ref(false);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const error = ref<string | null>(null);

// New category form
const newCategoryName = ref('');
const newCategoryColor = ref('#8b5cf6');

// Sprint 16: Tag integration state
const availableTags = ref<Tag[]>([]);
const availableDefinitions = ref<DetailDefinition[]>([]);
const newLinkedTagId = ref<number | null>(null);
const newLinkedDefinitionId = ref<number | null>(null);
const newLinkedDetailValue = ref<string | null>(null);

// Edit category state
const editingCategory = ref<Category | null>(null);
const editName = ref('');
const editColor = ref('#8b5cf6');
const editLinkedTagId = ref<number | null>(null);
const editLinkedDefinitionId = ref<number | null>(null);
const editLinkedDetailValue = ref<string | null>(null);
const previewEntries = ref<PreviewEntry[]>([]);
const loadingPreview = ref(false);

// Preset colors
const presetColors = ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#6366f1', '#14b8a6'];

// Fetch categories
const fetchCategories = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(`/api/novels/${props.novelId}/codex/categories`);
        allCategories.value = response.data.categories;
    } catch {
        error.value = 'Failed to load categories';
    } finally {
        loading.value = false;
    }
};

// Sprint 16: Fetch tags and detail definitions for linking
const fetchLinkingOptions = async () => {
    try {
        const [tagsRes, definitionsRes] = await Promise.all([
            axios.get(`/api/novels/${props.novelId}/codex/tags`),
            axios.get(`/api/novels/${props.novelId}/codex/detail-definitions`),
        ]);
        availableTags.value = tagsRes.data.tags || [];
        // Only show dropdown definitions for linking
        availableDefinitions.value = (definitionsRes.data.definitions || []).filter(
            (d: DetailDefinition) => d.type === 'dropdown'
        );
    } catch {
        // Silently fail - linking options are optional
    }
};

// Sprint 16: Preview entries that would auto-link
const fetchPreviewEntries = async (categoryId: number) => {
    loadingPreview.value = true;
    try {
        const response = await axios.get(`/api/codex/categories/${categoryId}/preview-entries`);
        previewEntries.value = response.data.entries || [];
    } catch {
        previewEntries.value = [];
    } finally {
        loadingPreview.value = false;
    }
};

// Get dropdown options for selected definition
const selectedDefinitionOptions = computed(() => {
    if (!editLinkedDefinitionId.value) return [];
    const def = availableDefinitions.value.find(d => d.id === editLinkedDefinitionId.value);
    return def?.options || [];
});

const newDefinitionOptions = computed(() => {
    if (!newLinkedDefinitionId.value) return [];
    const def = availableDefinitions.value.find(d => d.id === newLinkedDefinitionId.value);
    return def?.options || [];
});

// Open modal and fetch categories
const openModal = async () => {
    showModal.value = true;
    selectedIds.value = new Set(props.assignedCategories.map((c) => c.id));
    await Promise.all([fetchCategories(), fetchLinkingOptions()]);
};

// Toggle category selection
const toggleCategory = (id: number) => {
    if (selectedIds.value.has(id)) {
        selectedIds.value.delete(id);
    } else {
        selectedIds.value.add(id);
    }
    selectedIds.value = new Set(selectedIds.value); // Trigger reactivity
};

// Save category assignments
const saveAssignments = async () => {
    loading.value = true;
    error.value = null;

    try {
        await axios.post(`/api/codex/${props.entryId}/categories`, {
            category_ids: Array.from(selectedIds.value),
        });
        showModal.value = false;
        emit('updated');
    } catch {
        error.value = 'Failed to save categories';
    } finally {
        loading.value = false;
    }
};

// Create new category
const createCategory = async () => {
    if (!newCategoryName.value.trim()) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post(`/api/novels/${props.novelId}/codex/categories`, {
            name: newCategoryName.value.trim(),
            color: newCategoryColor.value,
            // Sprint 16: Tag integration
            linked_tag_id: newLinkedTagId.value,
            linked_detail_definition_id: newLinkedDefinitionId.value,
            linked_detail_value: newLinkedDetailValue.value,
        });
        
        allCategories.value.push(response.data.category);
        selectedIds.value.add(response.data.category.id);
        selectedIds.value = new Set(selectedIds.value);
        
        // Reset form
        newCategoryName.value = '';
        newCategoryColor.value = '#8b5cf6';
        newLinkedTagId.value = null;
        newLinkedDefinitionId.value = null;
        newLinkedDetailValue.value = null;
        showCreateModal.value = false;
    } catch {
        error.value = 'Failed to create category';
    } finally {
        loading.value = false;
    }
};

// Sprint 16: Open edit modal for a category
const openEditModal = async (category: Category) => {
    editingCategory.value = category;
    editName.value = category.name;
    editColor.value = category.color || '#8b5cf6';
    editLinkedTagId.value = category.linked_tag_id || null;
    editLinkedDefinitionId.value = category.linked_detail_definition_id || null;
    editLinkedDetailValue.value = category.linked_detail_value || null;
    showEditModal.value = true;
    
    await fetchLinkingOptions();
    if (category.id && category.has_auto_linking) {
        await fetchPreviewEntries(category.id);
    }
};

// Sprint 16: Update category
const updateCategory = async () => {
    if (!editingCategory.value || !editName.value.trim()) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.patch(`/api/codex/categories/${editingCategory.value.id}`, {
            name: editName.value.trim(),
            color: editColor.value,
            linked_tag_id: editLinkedTagId.value,
            linked_detail_definition_id: editLinkedDefinitionId.value,
            linked_detail_value: editLinkedDetailValue.value,
        });
        
        // Update local state
        const idx = allCategories.value.findIndex(c => c.id === editingCategory.value?.id);
        if (idx !== -1) {
            allCategories.value[idx] = response.data.category;
        }
        
        showEditModal.value = false;
        editingCategory.value = null;
        emit('updated');
    } catch {
        error.value = 'Failed to update category';
    } finally {
        loading.value = false;
    }
};

// Watch for definition changes to reset value
watch(editLinkedDefinitionId, () => {
    editLinkedDetailValue.value = null;
});

watch(newLinkedDefinitionId, () => {
    newLinkedDetailValue.value = null;
});

// Flatten categories for display
const flatCategories = computed(() => {
    const result: (Category & { depth: number })[] = [];
    
    const flatten = (cats: Category[], depth: number) => {
        for (const cat of cats) {
            result.push({ ...cat, depth });
            if (cat.children?.length) {
                flatten(cat.children, depth + 1);
            }
        }
    };
    
    flatten(allCategories.value, 0);
    return result;
});
</script>

<template>
    <div>
        <!-- Display assigned categories -->
        <div v-if="assignedCategories.length > 0" class="flex flex-wrap gap-2">
            <span
                v-for="cat in assignedCategories"
                :key="cat.id"
                class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                :style="cat.color ? { backgroundColor: cat.color + '20', color: cat.color } : {}"
                :class="!cat.color ? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' : ''"
            >
                {{ cat.name }}
            </span>
        </div>
        <p v-else class="text-sm text-zinc-400 italic dark:text-zinc-500">No categories assigned</p>

        <Button size="sm" variant="ghost" class="mt-3" @click="openModal">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Manage Categories
        </Button>

        <!-- Category Selection Modal -->
        <Modal :show="showModal" title="Manage Categories" @close="showModal = false">
            <div class="space-y-4">
                <!-- Error -->
                <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                    {{ error }}
                </div>

                <!-- Loading -->
                <div v-if="loading && allCategories.length === 0" class="flex items-center justify-center py-8">
                    <div class="h-6 w-6 animate-spin rounded-full border-2 border-violet-500 border-t-transparent" />
                </div>

                <!-- Category List -->
                <div v-else class="max-h-64 space-y-1 overflow-y-auto">
                    <div
                        v-for="cat in flatCategories"
                        :key="cat.id"
                        class="flex items-center gap-1"
                        :style="{ paddingLeft: `${cat.depth * 16}px` }"
                    >
                        <button
                            type="button"
                            :class="[
                                'flex flex-1 items-center gap-3 rounded-lg px-3 py-2 text-left transition-colors',
                                selectedIds.has(cat.id)
                                    ? 'bg-violet-100 text-violet-900 dark:bg-violet-900/30 dark:text-violet-200'
                                    : 'hover:bg-zinc-100 dark:hover:bg-zinc-800',
                            ]"
                            @click="toggleCategory(cat.id)"
                        >
                            <!-- Checkbox -->
                            <span
                                :class="[
                                    'flex h-4 w-4 shrink-0 items-center justify-center rounded border transition-colors',
                                    selectedIds.has(cat.id)
                                        ? 'border-violet-600 bg-violet-600 text-white'
                                        : 'border-zinc-300 dark:border-zinc-600',
                                ]"
                            >
                                <svg v-if="selectedIds.has(cat.id)" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>

                            <!-- Color dot -->
                            <span
                                v-if="cat.color"
                                class="h-3 w-3 shrink-0 rounded-full"
                                :style="{ backgroundColor: cat.color }"
                            />

                            <!-- Name -->
                            <span class="flex-1 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ cat.name }}
                            </span>

                            <!-- Sprint 16: Auto-link indicator -->
                            <span
                                v-if="cat.has_auto_linking"
                                class="rounded bg-blue-100 px-1.5 py-0.5 text-xs text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                title="This category has auto-linking enabled"
                            >
                                Auto
                            </span>

                            <!-- Entry count (total including auto-linked) -->
                            <span v-if="cat.total_entry_count" class="text-xs text-zinc-400 dark:text-zinc-500">
                                {{ cat.total_entry_count }}
                            </span>
                        </button>

                        <!-- Sprint 16: Edit button -->
                        <button
                            type="button"
                            class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                            title="Edit category"
                            @click.stop="openEditModal(cat)"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Empty state -->
                    <div v-if="flatCategories.length === 0 && !loading" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        No categories yet. Create one to get started.
                    </div>
                </div>

                <!-- Create new category button -->
                <button
                    type="button"
                    class="flex w-full items-center gap-2 rounded-lg border-2 border-dashed border-zinc-300 px-3 py-2 text-sm text-zinc-600 transition-colors hover:border-violet-400 hover:text-violet-600 dark:border-zinc-600 dark:text-zinc-400 dark:hover:border-violet-500 dark:hover:text-violet-400"
                    @click="showCreateModal = true"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Create New Category
                </button>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button variant="ghost" @click="showModal = false">Cancel</Button>
                    <Button :loading="loading" @click="saveAssignments">Save</Button>
                </div>
            </template>
        </Modal>

        <!-- Create Category Modal -->
        <Modal :show="showCreateModal" title="Create Category" @close="showCreateModal = false">
            <div class="space-y-4">
                <Input
                    v-model="newCategoryName"
                    label="Category Name"
                    placeholder="e.g., Main Characters, Locations"
                    required
                />

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Color
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            v-model="newCategoryColor"
                            type="color"
                            class="h-10 w-10 cursor-pointer rounded border border-zinc-300 p-0.5 dark:border-zinc-600"
                        />
                        <div class="flex gap-1">
                            <button
                                v-for="color in presetColors"
                                :key="color"
                                type="button"
                                class="h-6 w-6 rounded transition-transform hover:scale-110"
                                :class="newCategoryColor === color ? 'ring-2 ring-violet-500 ring-offset-2' : ''"
                                :style="{ backgroundColor: color }"
                                @click="newCategoryColor = color"
                            />
                        </div>
                    </div>
                </div>

                <!-- Sprint 16: Tag Integration - Link to Tag -->
                <div v-if="availableTags.length > 0">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Auto-link by Tag
                        <span class="ml-1 text-xs font-normal text-zinc-500">(optional)</span>
                    </label>
                    <select
                        v-model="newLinkedTagId"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800"
                    >
                        <option :value="null">None</option>
                        <option v-for="tag in availableTags" :key="tag.id" :value="tag.id">
                            {{ tag.name }}
                        </option>
                    </select>
                    <p class="mt-1 text-xs text-zinc-500">
                        Entries with this tag will automatically appear in this category.
                    </p>
                </div>

                <!-- Sprint 16: Tag Integration - Link to Detail Value -->
                <div v-if="availableDefinitions.length > 0">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Auto-link by Detail Value
                        <span class="ml-1 text-xs font-normal text-zinc-500">(optional)</span>
                    </label>
                    <div class="flex gap-2">
                        <select
                            v-model="newLinkedDefinitionId"
                            class="flex-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800"
                        >
                            <option :value="null">Select detail...</option>
                            <option v-for="def in availableDefinitions" :key="def.id" :value="def.id">
                                {{ def.name }}
                            </option>
                        </select>
                        <select
                            v-if="newLinkedDefinitionId && newDefinitionOptions.length > 0"
                            v-model="newLinkedDetailValue"
                            class="flex-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800"
                        >
                            <option :value="null">Select value...</option>
                            <option v-for="opt in newDefinitionOptions" :key="opt" :value="opt">
                                {{ opt }}
                            </option>
                        </select>
                    </div>
                    <p class="mt-1 text-xs text-zinc-500">
                        Entries with this detail value will automatically appear in this category.
                    </p>
                </div>

                <!-- Preview -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Preview
                    </label>
                    <span
                        class="inline-flex rounded-full px-3 py-1 text-sm font-medium"
                        :style="{ backgroundColor: newCategoryColor + '20', color: newCategoryColor }"
                    >
                        {{ newCategoryName || 'Category Name' }}
                    </span>
                </div>

                <!-- Error -->
                <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                    {{ error }}
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button variant="ghost" @click="showCreateModal = false">Cancel</Button>
                    <Button :loading="loading" :disabled="!newCategoryName.trim()" @click="createCategory">
                        Create Category
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Sprint 16: Edit Category Modal -->
        <Modal :show="showEditModal" title="Edit Category" @close="showEditModal = false">
            <div class="space-y-4">
                <Input
                    v-model="editName"
                    label="Category Name"
                    placeholder="e.g., Main Characters, Locations"
                    required
                />

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Color
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            v-model="editColor"
                            type="color"
                            class="h-10 w-10 cursor-pointer rounded border border-zinc-300 p-0.5 dark:border-zinc-600"
                        />
                        <div class="flex gap-1">
                            <button
                                v-for="color in presetColors"
                                :key="color"
                                type="button"
                                class="h-6 w-6 rounded transition-transform hover:scale-110"
                                :class="editColor === color ? 'ring-2 ring-violet-500 ring-offset-2' : ''"
                                :style="{ backgroundColor: color }"
                                @click="editColor = color"
                            />
                        </div>
                    </div>
                </div>

                <!-- Tag Integration - Link to Tag -->
                <div v-if="availableTags.length > 0">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Auto-link by Tag
                        <span class="ml-1 text-xs font-normal text-zinc-500">(optional)</span>
                    </label>
                    <select
                        v-model="editLinkedTagId"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800"
                    >
                        <option :value="null">None</option>
                        <option v-for="tag in availableTags" :key="tag.id" :value="tag.id">
                            {{ tag.name }}
                        </option>
                    </select>
                    <p class="mt-1 text-xs text-zinc-500">
                        Entries with this tag will automatically appear in this category.
                    </p>
                </div>

                <!-- Tag Integration - Link to Detail Value -->
                <div v-if="availableDefinitions.length > 0">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Auto-link by Detail Value
                        <span class="ml-1 text-xs font-normal text-zinc-500">(optional)</span>
                    </label>
                    <div class="flex gap-2">
                        <select
                            v-model="editLinkedDefinitionId"
                            class="flex-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800"
                        >
                            <option :value="null">Select detail...</option>
                            <option v-for="def in availableDefinitions" :key="def.id" :value="def.id">
                                {{ def.name }}
                            </option>
                        </select>
                        <select
                            v-if="editLinkedDefinitionId && selectedDefinitionOptions.length > 0"
                            v-model="editLinkedDetailValue"
                            class="flex-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800"
                        >
                            <option :value="null">Select value...</option>
                            <option v-for="opt in selectedDefinitionOptions" :key="opt" :value="opt">
                                {{ opt }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Preview of auto-linked entries -->
                <div v-if="previewEntries.length > 0">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Auto-linked Entries ({{ previewEntries.length }})
                    </label>
                    <div class="max-h-32 space-y-1 overflow-y-auto rounded-lg border border-zinc-200 bg-zinc-50 p-2 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div
                            v-for="entry in previewEntries"
                            :key="entry.id"
                            class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500" />
                            {{ entry.name }}
                            <span class="text-xs text-zinc-400">({{ entry.type }})</span>
                        </div>
                    </div>
                </div>
                <div v-else-if="loadingPreview" class="text-sm text-zinc-500">
                    Loading preview...
                </div>

                <!-- Error -->
                <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                    {{ error }}
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button variant="ghost" @click="showEditModal = false">Cancel</Button>
                    <Button :loading="loading" :disabled="!editName.trim()" @click="updateCategory">
                        Save Changes
                    </Button>
                </div>
            </template>
        </Modal>
    </div>
</template>
