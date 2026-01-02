import axios from 'axios';
import { ref, watch, computed, type Ref } from 'vue';

export interface CodexAliasEntry {
    id: number;
    name: string;
    type: string;
    alias?: string;
    description?: string;
}

export interface DetectedAlias {
    entry: CodexAliasEntry;
    matchedText: string;
    startIndex: number;
    endIndex: number;
}

export interface UseCodexAliasDetectionOptions {
    novelId: Ref<number | null | undefined> | number;
    enabled?: boolean;
    debounceMs?: number;
}

/**
 * Composable for detecting codex aliases in text content.
 * Used in ChatInput for auto-context detection and ChatMessage for linking.
 *
 * Sprint 21 (F1): Codex Alias Auto-Detection
 */
export function useCodexAliasDetection(options: UseCodexAliasDetectionOptions) {
    const novelId = typeof options.novelId === 'number' ? ref(options.novelId) : options.novelId;
    const enabled = ref(options.enabled ?? true);
    // Note: debounceMs is available in options for consumers to use
    // options.debounceMs ?? 300;

    // Lookup map: lowercased alias/name -> entry info
    const lookupMap = ref<Record<string, CodexAliasEntry>>({});
    const loading = ref(false);
    const error = ref<string | null>(null);

    // Sorted aliases for matching (longest first to match longest possible)
    const sortedAliases = computed(() => {
        return Object.keys(lookupMap.value).sort((a, b) => b.length - a.length);
    });

    // Fetch the alias lookup map
    const fetchLookup = async () => {
        const id = novelId.value;
        if (!enabled.value || !id) {
            lookupMap.value = {};
            return;
        }

        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(`/api/novels/${id}/codex/alias-lookup`);
            lookupMap.value = response.data.lookup || {};
        } catch (e) {
            error.value = 'Failed to load codex alias lookup';
            lookupMap.value = {};
            console.error('Failed to fetch alias lookup:', e);
        } finally {
            loading.value = false;
        }
    };

    // Watch for novelId changes
    watch(
        () => novelId.value,
        (newId) => {
            if (newId && enabled.value) {
                fetchLookup();
            } else {
                lookupMap.value = {};
            }
        },
        { immediate: true }
    );

    // Watch for enabled changes
    watch(
        () => enabled.value,
        (newEnabled) => {
            if (newEnabled && novelId.value) {
                fetchLookup();
            } else if (!newEnabled) {
                lookupMap.value = {};
            }
        }
    );

    /**
     * Detect aliases in text and return unique matched entries.
     * Returns array of detected aliases with their positions.
     */
    const detectAliases = (text: string): DetectedAlias[] => {
        if (!text || sortedAliases.value.length === 0) {
            return [];
        }

        const detected: DetectedAlias[] = [];
        const seenEntryIds = new Set<number>();

        // Word boundary regex for matching whole words
        for (const alias of sortedAliases.value) {
            // Create a regex that matches the alias as a whole word
            const escapedAlias = alias.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`\\b${escapedAlias}\\b`, 'gi');

            let match;
            while ((match = regex.exec(text)) !== null) {
                const entry = lookupMap.value[alias];
                if (entry && !seenEntryIds.has(entry.id)) {
                    seenEntryIds.add(entry.id);
                    detected.push({
                        entry,
                        matchedText: match[0],
                        startIndex: match.index,
                        endIndex: match.index + match[0].length,
                    });
                }
            }
        }

        // Sort by position in text
        return detected.sort((a, b) => a.startIndex - b.startIndex);
    };

    /**
     * Get unique detected entries (without position info).
     * Useful for auto-adding context.
     */
    const detectUniqueEntries = (text: string): CodexAliasEntry[] => {
        const detected = detectAliases(text);
        return detected.map((d) => d.entry);
    };

    /**
     * Check if a specific entry name/alias exists in the lookup.
     */
    const hasEntry = (nameOrAlias: string): boolean => {
        return nameOrAlias.toLowerCase() in lookupMap.value;
    };

    /**
     * Get entry by name or alias.
     */
    const getEntry = (nameOrAlias: string): CodexAliasEntry | null => {
        return lookupMap.value[nameOrAlias.toLowerCase()] || null;
    };

    /**
     * Replace aliases in text with linked versions (for rendering).
     * Returns HTML string with alias links.
     */
    const linkAliasesInHtml = (
        text: string,
        linkClass = 'codex-alias-link',
        linkClickHandler?: string // e.g., "handleCodexClick"
    ): string => {
        if (!text || sortedAliases.value.length === 0) {
            return text;
        }

        // Track replacements to avoid double-processing
        type Replacement = { start: number; end: number; html: string };
        const replacements: Replacement[] = [];

        for (const alias of sortedAliases.value) {
            const escapedAlias = alias.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`\\b${escapedAlias}\\b`, 'gi');

            let match;
            while ((match = regex.exec(text)) !== null) {
                const entry = lookupMap.value[alias];
                if (!entry) continue;

                // Check if this range overlaps with any existing replacement
                const overlaps = replacements.some(
                    (r) =>
                        (match!.index >= r.start && match!.index < r.end) ||
                        (match!.index + match![0].length > r.start && match!.index + match![0].length <= r.end)
                );

                if (!overlaps) {
                    const clickAttr = linkClickHandler ? ` @click.prevent="${linkClickHandler}(${entry.id})"` : '';
                    const html = `<a href="/codex/${entry.id}" class="${linkClass}" data-codex-id="${entry.id}" data-codex-type="${entry.type}" title="${entry.name} (${entry.type})"${clickAttr}>${match[0]}</a>`;

                    replacements.push({
                        start: match.index,
                        end: match.index + match[0].length,
                        html,
                    });
                }
            }
        }

        // Sort replacements by position (descending) to replace from end to start
        replacements.sort((a, b) => b.start - a.start);

        // Apply replacements
        let result = text;
        for (const r of replacements) {
            result = result.slice(0, r.start) + r.html + result.slice(r.end);
        }

        return result;
    };

    // Refresh lookup (can be called after creating new entries)
    const refresh = () => {
        if (enabled.value && novelId.value) {
            fetchLookup();
        }
    };

    // Toggle detection on/off
    const toggle = () => {
        enabled.value = !enabled.value;
    };

    return {
        lookupMap,
        loading,
        error,
        enabled,
        detectAliases,
        detectUniqueEntries,
        hasEntry,
        getEntry,
        linkAliasesInHtml,
        refresh,
        toggle,
        fetchLookup,
    };
}
