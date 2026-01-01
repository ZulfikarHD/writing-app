import type { CodexEntry } from '@/extensions/CodexHighlight';
import axios from 'axios';
import { ref, watch } from 'vue';

export interface UseCodexHighlightOptions {
    novelId: number;
    enabled?: boolean;
}

export function useCodexHighlight(options: UseCodexHighlightOptions) {
    const { novelId, enabled = true } = options;

    const entries = ref<CodexEntry[]>([]);
    const loading = ref(false);
    const error = ref<string | null>(null);
    const isEnabled = ref(enabled);

    const fetchEntries = async () => {
        if (!isEnabled.value || !novelId) {
            entries.value = [];
            return;
        }

        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(`/api/novels/${novelId}/codex`);
            entries.value = response.data.entries.map(
                (e: { id: number; type: string; name: string; aliases: string[] }) => ({
                    id: e.id,
                    type: e.type,
                    name: e.name,
                    aliases: e.aliases || [],
                })
            );
        } catch {
            error.value = 'Failed to load codex entries for highlighting';
            entries.value = [];
        } finally {
            loading.value = false;
        }
    };

    // Fetch entries when enabled changes
    watch(
        () => isEnabled.value,
        (newValue) => {
            if (newValue) {
                fetchEntries();
            } else {
                entries.value = [];
            }
        },
        { immediate: true }
    );

    // Refresh entries (can be called after creating new entries)
    const refresh = () => {
        if (isEnabled.value) {
            fetchEntries();
        }
    };

    // Toggle highlighting on/off
    const toggle = () => {
        isEnabled.value = !isEnabled.value;
    };

    return {
        entries,
        loading,
        error,
        isEnabled,
        refresh,
        toggle,
        fetchEntries,
    };
}
