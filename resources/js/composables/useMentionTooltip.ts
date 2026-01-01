import axios from 'axios';
import { ref, type Ref } from 'vue';

export interface MentionEntryInfo {
    id: number;
    type: string;
    name: string;
    description: string | null;
}

export interface UseMentionTooltipOptions {
    novelId?: number;
    debounceMs?: number;
}

export function useMentionTooltip(options: UseMentionTooltipOptions) {
    const { debounceMs = 300 } = options;

    const hoveredEntry = ref<MentionEntryInfo | null>(null);
    const targetRect = ref<DOMRect | null>(null);
    const loading = ref(false);

    // Cache for fetched entry details
    const entryCache = new Map<number, MentionEntryInfo>();

    let hoverTimeout: ReturnType<typeof setTimeout> | null = null;
    let currentTarget: HTMLElement | null = null;

    // Fetch entry details from API
    const fetchEntryDetails = async (entryId: number): Promise<MentionEntryInfo | null> => {
        // Check cache first
        if (entryCache.has(entryId)) {
            return entryCache.get(entryId)!;
        }

        loading.value = true;

        try {
            const response = await axios.get(`/api/codex/${entryId}`);
            const entry = response.data.entry;

            const info: MentionEntryInfo = {
                id: entry.id,
                type: entry.type,
                name: entry.name,
                description: entry.description,
            };

            // Cache the result
            entryCache.set(entryId, info);
            return info;
        } catch {
            return null;
        } finally {
            loading.value = false;
        }
    };

    // Handle mouse entering a mention
    const handleMentionMouseEnter = (target: HTMLElement) => {
        // Clear any pending timeout
        if (hoverTimeout) {
            clearTimeout(hoverTimeout);
            hoverTimeout = null;
        }

        currentTarget = target;

        // Debounce to avoid flickering on quick mouse movements
        hoverTimeout = setTimeout(async () => {
            const entryId = target.dataset.entryId;
            if (!entryId) return;

            const entry = await fetchEntryDetails(parseInt(entryId, 10));
            if (entry && currentTarget === target) {
                hoveredEntry.value = entry;
                targetRect.value = target.getBoundingClientRect();
            }
        }, debounceMs);
    };

    // Handle mouse leaving a mention
    const handleMentionMouseLeave = () => {
        if (hoverTimeout) {
            clearTimeout(hoverTimeout);
            hoverTimeout = null;
        }

        currentTarget = null;
        hoveredEntry.value = null;
        targetRect.value = null;
    };

    // Close tooltip
    const closeTooltip = () => {
        hoveredEntry.value = null;
        targetRect.value = null;
        currentTarget = null;
    };

    // Setup event listeners for editor content area
    const setupEventListeners = (containerRef: Ref<HTMLElement | null>) => {
        const handleMouseOver = (e: MouseEvent) => {
            const target = e.target as HTMLElement;
            if (target.classList.contains('codex-mention')) {
                handleMentionMouseEnter(target);
            }
        };

        const handleMouseOut = (e: MouseEvent) => {
            const target = e.target as HTMLElement;
            const relatedTarget = e.relatedTarget as HTMLElement | null;

            // Only hide if we're not moving to another mention or the tooltip
            if (target.classList.contains('codex-mention')) {
                if (!relatedTarget?.classList.contains('codex-mention')) {
                    handleMentionMouseLeave();
                }
            }
        };

        const container = containerRef.value;
        if (container) {
            container.addEventListener('mouseover', handleMouseOver);
            container.addEventListener('mouseout', handleMouseOut);

            return () => {
                container.removeEventListener('mouseover', handleMouseOver);
                container.removeEventListener('mouseout', handleMouseOut);
            };
        }

        return () => {};
    };

    // Clear cache (call after creating/updating entries)
    const clearCache = () => {
        entryCache.clear();
    };

    return {
        hoveredEntry,
        targetRect,
        loading,
        closeTooltip,
        setupEventListeners,
        clearCache,
    };
}
