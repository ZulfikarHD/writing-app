import { ref, computed, watch, onMounted } from 'vue';

export type WorkspaceMode = 'write' | 'plan' | 'codex' | 'chat';

export type SidebarTool = 'scenes' | 'codex' | 'notes' | 'prompts';

export interface WorkspaceState {
    mode: WorkspaceMode;
    sidebarCollapsed: boolean;
    expandedTools: SidebarTool[];
    pinnedTools: SidebarTool[];
}

const STORAGE_KEY = 'novelwrite-workspace-state';

const defaultState: WorkspaceState = {
    mode: 'write',
    sidebarCollapsed: false,
    expandedTools: ['scenes'],
    pinnedTools: [],
};

// Shared reactive state (singleton pattern)
const state = ref<WorkspaceState>({ ...defaultState });
const activeCodexEntryId = ref<number | null>(null);
let isInitialized = false;

function loadState(): WorkspaceState {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            const parsed = JSON.parse(stored) as Partial<WorkspaceState>;
            return {
                mode: parsed.mode || defaultState.mode,
                sidebarCollapsed: parsed.sidebarCollapsed ?? defaultState.sidebarCollapsed,
                expandedTools: parsed.expandedTools || defaultState.expandedTools,
                pinnedTools: parsed.pinnedTools || defaultState.pinnedTools,
            };
        }
    } catch {
        // Ignore parse errors
    }
    return { ...defaultState };
}

function saveState(newState: WorkspaceState) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(newState));
    } catch {
        // Ignore storage errors
    }
}

export function useWorkspaceState(initialMode?: WorkspaceMode) {
    onMounted(() => {
        if (!isInitialized) {
            state.value = loadState();
            isInitialized = true;
        }
        // Override mode if initial mode is provided (from URL)
        if (initialMode && initialMode !== state.value.mode) {
            state.value.mode = initialMode;
        }
    });

    // Watch for changes and persist
    watch(
        state,
        (newState) => {
            saveState(newState);
        },
        { deep: true }
    );

    // Current mode
    const mode = computed(() => state.value.mode);

    // Mode checks
    const isWriteMode = computed(() => state.value.mode === 'write');
    const isPlanMode = computed(() => state.value.mode === 'plan');
    const isCodexMode = computed(() => state.value.mode === 'codex');
    const isChatMode = computed(() => state.value.mode === 'chat');

    // Sidebar state
    const sidebarCollapsed = computed(() => state.value.sidebarCollapsed);
    const expandedTools = computed(() => state.value.expandedTools);
    const pinnedTools = computed(() => state.value.pinnedTools);

    // Mode setters
    const setMode = (newMode: WorkspaceMode) => {
        state.value.mode = newMode;
    };

    // Sidebar toggle
    const toggleSidebar = () => {
        state.value.sidebarCollapsed = !state.value.sidebarCollapsed;
    };

    const setSidebarCollapsed = (collapsed: boolean) => {
        state.value.sidebarCollapsed = collapsed;
    };

    // Tool expansion
    const isToolExpanded = (tool: SidebarTool): boolean => {
        return state.value.expandedTools.includes(tool);
    };

    const toggleToolExpanded = (tool: SidebarTool) => {
        const index = state.value.expandedTools.indexOf(tool);
        if (index === -1) {
            state.value.expandedTools.push(tool);
        } else {
            state.value.expandedTools.splice(index, 1);
        }
    };

    const setToolExpanded = (tool: SidebarTool, expanded: boolean) => {
        const index = state.value.expandedTools.indexOf(tool);
        if (expanded && index === -1) {
            state.value.expandedTools.push(tool);
        } else if (!expanded && index !== -1) {
            state.value.expandedTools.splice(index, 1);
        }
    };

    // Tool pinning
    const isToolPinned = (tool: SidebarTool): boolean => {
        return state.value.pinnedTools.includes(tool);
    };

    const toggleToolPinned = (tool: SidebarTool) => {
        const index = state.value.pinnedTools.indexOf(tool);
        if (index === -1) {
            state.value.pinnedTools.push(tool);
            // Also expand if not already
            if (!isToolExpanded(tool)) {
                state.value.expandedTools.push(tool);
            }
        } else {
            state.value.pinnedTools.splice(index, 1);
        }
    };

    // Codex entry modal state
    const openCodexEntry = (entryId: number) => {
        activeCodexEntryId.value = entryId;
    };

    const closeCodexEntry = () => {
        activeCodexEntryId.value = null;
    };

    // Reset to defaults
    const resetToDefaults = () => {
        state.value = { ...defaultState };
        activeCodexEntryId.value = null;
    };

    return {
        // State
        mode,
        isWriteMode,
        isPlanMode,
        isCodexMode,
        isChatMode,
        sidebarCollapsed,
        expandedTools,
        pinnedTools,
        activeCodexEntryId,

        // Mode actions
        setMode,

        // Sidebar actions
        toggleSidebar,
        setSidebarCollapsed,

        // Tool actions
        isToolExpanded,
        toggleToolExpanded,
        setToolExpanded,
        isToolPinned,
        toggleToolPinned,

        // Codex modal actions
        openCodexEntry,
        closeCodexEntry,

        // Reset
        resetToDefaults,
    };
}
