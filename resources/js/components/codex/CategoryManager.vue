<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Modal from '@/components/ui/Modal.vue';
import axios from 'axios';
import { ref, computed } from 'vue';

interface Category {
    id: number;
    name: string;
    color: string | null;
    parent_id: number | null;
    sort_order: number;
    entry_count?: number;
    children?: Category[];
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
const error = ref<string | null>(null);

// New category form
const newCategoryName = ref('');
const newCategoryColor = ref('#8b5cf6');

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

// Open modal and fetch categories
const openModal = async () => {
    showModal.value = true;
    selectedIds.value = new Set(props.assignedCategories.map((c) => c.id));
    await fetchCategories();
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
        });
        
        allCategories.value.push(response.data.category);
        selectedIds.value.add(response.data.category.id);
        selectedIds.value = new Set(selectedIds.value);
        
        newCategoryName.value = '';
        newCategoryColor.value = '#8b5cf6';
        showCreateModal.value = false;
    } catch {
        error.value = 'Failed to create category';
    } finally {
        loading.value = false;
    }
};

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
                    <button
                        v-for="cat in flatCategories"
                        :key="cat.id"
                        type="button"
                        :class="[
                            'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-colors',
                            selectedIds.has(cat.id)
                                ? 'bg-violet-100 text-violet-900 dark:bg-violet-900/30 dark:text-violet-200'
                                : 'hover:bg-zinc-100 dark:hover:bg-zinc-800',
                        ]"
                        :style="{ paddingLeft: `${(cat.depth * 16) + 12}px` }"
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

                        <!-- Entry count -->
                        <span v-if="cat.entry_count" class="text-xs text-zinc-400 dark:text-zinc-500">
                            {{ cat.entry_count }}
                        </span>
                    </button>

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
    </div>
</template>
