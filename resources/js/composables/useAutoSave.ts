import { ref } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';

export type SaveStatus = 'saved' | 'saving' | 'unsaved' | 'error';

interface UseAutoSaveOptions {
    sceneId: number;
    debounceMs?: number;
    onSaved?: (wordCount: number, savedAt: string) => void;
    onError?: (error: unknown) => void;
}

export function useAutoSave(options: UseAutoSaveOptions) {
    const { sceneId, debounceMs = 500, onSaved, onError } = options;
    const saveStatus = ref<SaveStatus>('saved');
    const lastSavedAt = ref<string | null>(null);
    const isSaving = ref(false);

    const saveContent = async (content: object) => {
        if (isSaving.value) return;
        isSaving.value = true;
        saveStatus.value = 'saving';

        try {
            const response = await axios.patch('/api/scenes/' + sceneId + '/content', { content });
            saveStatus.value = 'saved';
            lastSavedAt.value = response.data.saved_at;
            if (onSaved) onSaved(response.data.word_count, response.data.saved_at);
        } catch (error) {
            saveStatus.value = 'error';
            if (onError) onError(error);
        } finally {
            isSaving.value = false;
        }
    };

    const debouncedSave = useDebounceFn(saveContent, debounceMs);
    const triggerSave = (content: object) => { saveStatus.value = 'unsaved'; debouncedSave(content); };
    const forceSave = async (content: object) => { await saveContent(content); };

    return { saveStatus, lastSavedAt, isSaving, triggerSave, forceSave };
}
