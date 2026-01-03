import { ref, computed } from 'vue';
import axios from 'axios';
import * as SectionActions from '@/actions/App/Http/Controllers/SectionController';

export interface Section {
    id: number;
    scene_id: number;
    type: 'content' | 'note' | 'alternative' | 'beat';
    title: string | null;
    content: object | null;
    color: string;
    is_collapsed: boolean;
    exclude_from_ai: boolean;
    sort_order: number;
    word_count: number;
    created_at: string | null;
    updated_at: string | null;
}

export interface CreateSectionData {
    type?: 'content' | 'note' | 'alternative' | 'beat';
    title?: string | null;
    content?: object | null;
    color?: string;
    sort_order?: number;
}

export interface UpdateSectionData {
    type?: 'content' | 'note' | 'alternative' | 'beat';
    title?: string | null;
    content?: object | null;
    color?: string;
    is_collapsed?: boolean;
    exclude_from_ai?: boolean;
}

export function useSections(sceneId: number | (() => number)) {
    const sections = ref<Section[]>([]);
    const loading = ref(false);
    const error = ref<string | null>(null);

    const getSceneId = () => typeof sceneId === 'function' ? sceneId() : sceneId;

    const fetchSections = async () => {
        const id = getSceneId();
        if (!id) return;

        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(SectionActions.index.url({ scene: id }));
            sections.value = response.data.sections;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch sections';
            console.error('Error fetching sections:', err);
        } finally {
            loading.value = false;
        }
    };

    const createSection = async (data: CreateSectionData = {}) => {
        const id = getSceneId();
        if (!id) return null;

        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post(SectionActions.store.url({ scene: id }), data);
            const newSection = response.data.section;
            sections.value = [...sections.value, newSection].sort(
                (a, b) => a.sort_order - b.sort_order
            );
            return newSection;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to create section';
            console.error('Error creating section:', err);
            return null;
        } finally {
            loading.value = false;
        }
    };

    const updateSection = async (sectionId: number, data: UpdateSectionData) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.patch(SectionActions.update.url({ section: sectionId }), data);
            const updated = response.data.section;
            sections.value = sections.value.map((s) =>
                s.id === sectionId ? updated : s
            );
            return updated;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to update section';
            console.error('Error updating section:', err);
            return null;
        } finally {
            loading.value = false;
        }
    };

    const deleteSection = async (sectionId: number) => {
        loading.value = true;
        error.value = null;

        try {
            await axios.delete(SectionActions.destroy.url({ section: sectionId }));
            sections.value = sections.value.filter((s) => s.id !== sectionId);
            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to delete section';
            console.error('Error deleting section:', err);
            return false;
        } finally {
            loading.value = false;
        }
    };

    const reorderSections = async (newOrder: { id: number; sort_order: number }[]) => {
        const id = getSceneId();
        if (!id) return false;

        // Optimistically update local state
        const oldSections = [...sections.value];
        sections.value = sections.value
            .map((s) => {
                const update = newOrder.find((o) => o.id === s.id);
                return update ? { ...s, sort_order: update.sort_order } : s;
            })
            .sort((a, b) => a.sort_order - b.sort_order);

        try {
            await axios.post(SectionActions.reorder.url({ scene: id }), { sections: newOrder });
            return true;
        } catch (err) {
            // Revert on error
            sections.value = oldSections;
            error.value = err instanceof Error ? err.message : 'Failed to reorder sections';
            console.error('Error reordering sections:', err);
            return false;
        }
    };

    const toggleCollapse = async (sectionId: number) => {
        try {
            const response = await axios.post(SectionActions.toggleCollapse.url({ section: sectionId }));
            const updated = response.data.section;
            sections.value = sections.value.map((s) =>
                s.id === sectionId ? updated : s
            );
            return updated;
        } catch (err) {
            console.error('Error toggling collapse:', err);
            return null;
        }
    };

    const toggleAiVisibility = async (sectionId: number) => {
        try {
            const response = await axios.post(SectionActions.toggleAiVisibility.url({ section: sectionId }));
            const updated = response.data.section;
            sections.value = sections.value.map((s) =>
                s.id === sectionId ? updated : s
            );
            return updated;
        } catch (err) {
            console.error('Error toggling AI visibility:', err);
            return null;
        }
    };

    const dissolveSection = async (sectionId: number) => {
        try {
            const response = await axios.post(SectionActions.dissolve.url({ section: sectionId }));
            sections.value = sections.value.filter((s) => s.id !== sectionId);
            return response.data.dissolved_content;
        } catch (err) {
            console.error('Error dissolving section:', err);
            return null;
        }
    };

    const duplicateSection = async (sectionId: number) => {
        try {
            const response = await axios.post(SectionActions.duplicate.url({ section: sectionId }));
            const newSection = response.data.section;
            sections.value = [...sections.value, newSection].sort(
                (a, b) => a.sort_order - b.sort_order
            );
            return newSection;
        } catch (err) {
            console.error('Error duplicating section:', err);
            return null;
        }
    };

    // Computed helpers
    const contentSections = computed(() =>
        sections.value.filter((s) => s.type === 'content')
    );

    const noteSections = computed(() =>
        sections.value.filter((s) => s.type === 'note')
    );

    const aiVisibleSections = computed(() =>
        sections.value.filter((s) => !s.exclude_from_ai)
    );

    const totalWordCount = computed(() =>
        sections.value.reduce((sum, s) => sum + (s.word_count || 0), 0)
    );

    const contentWordCount = computed(() =>
        contentSections.value.reduce((sum, s) => sum + (s.word_count || 0), 0)
    );

    return {
        sections,
        loading,
        error,
        // CRUD operations
        fetchSections,
        createSection,
        updateSection,
        deleteSection,
        reorderSections,
        // Actions
        toggleCollapse,
        toggleAiVisibility,
        dissolveSection,
        duplicateSection,
        // Computed
        contentSections,
        noteSections,
        aiVisibleSections,
        totalWordCount,
        contentWordCount,
    };
}

export default useSections;
