import { ref, onMounted, onUnmounted } from 'vue';

/**
 * useCodexEditor - Composable for codex integration in the editor
 *
 * Sprint 15: Provides reactive state management for codex-related
 * editor interactions (progressions, quick create, hover tooltips).
 *
 * This composable:
 * - Manages modal visibility states
 * - Listens for editor events
 * - Provides handlers for codex operations
 */

export interface CodexEditorState {
    /** Whether the progression modal is open */
    showProgressionModal: boolean;
    /** Whether the quick create modal is open */
    showQuickCreateModal: boolean;
    /** Text selected for quick create */
    selectedTextForCreate: string;
    /** Current scene ID for progression linking */
    currentSceneId: number | null;
    /** Current scene name for display */
    currentSceneName: string | null;
}

export function useCodexEditor(options: {
    sceneId?: number;
    sceneName?: string;
    novelId: number;
    onEntryCreated?: () => void;
}) {
    // State
    const showProgressionModal = ref(false);
    const showQuickCreateModal = ref(false);
    const selectedTextForCreate = ref('');
    const currentSceneId = ref(options.sceneId || null);
    const currentSceneName = ref(options.sceneName || null);

    // Event handlers for extension events
    const handleOpenProgressionModal = () => {
        showProgressionModal.value = true;
    };

    const handleOpenQuickCreateModal = (event: CustomEvent) => {
        const { selectedText } = event.detail;
        selectedTextForCreate.value = selectedText || '';
        showQuickCreateModal.value = true;
    };

    // Close handlers
    const closeProgressionModal = () => {
        showProgressionModal.value = false;
    };

    const closeQuickCreateModal = () => {
        showQuickCreateModal.value = false;
        selectedTextForCreate.value = '';
    };

    // Handler for when an entry is created
    const handleEntryCreated = (entry: { id: number; name: string; type: string }) => {
        // Dispatch event for other components to react
        window.dispatchEvent(
            new CustomEvent('codex-entry-created', {
                detail: entry,
            })
        );

        // Call the callback if provided
        if (options.onEntryCreated) {
            options.onEntryCreated();
        }
    };

    // Handler for when a progression is saved
    const handleProgressionSaved = (data: { entryId: number; entryName: string }) => {
        // Could show a toast or perform other actions
        console.log(`Progression added for ${data.entryName}`);
    };

    // Set up event listeners
    onMounted(() => {
        window.addEventListener('codex:open-progression-modal', handleOpenProgressionModal);
        window.addEventListener('codex:open-quick-create-modal', handleOpenQuickCreateModal as EventListener);
    });

    onUnmounted(() => {
        window.removeEventListener('codex:open-progression-modal', handleOpenProgressionModal);
        window.removeEventListener('codex:open-quick-create-modal', handleOpenQuickCreateModal as EventListener);
    });

    // Update scene context when props change
    const updateSceneContext = (sceneId: number | null, sceneName: string | null) => {
        currentSceneId.value = sceneId;
        currentSceneName.value = sceneName;
    };

    return {
        // State
        showProgressionModal,
        showQuickCreateModal,
        selectedTextForCreate,
        currentSceneId,
        currentSceneName,

        // Methods
        closeProgressionModal,
        closeQuickCreateModal,
        handleEntryCreated,
        handleProgressionSaved,
        updateSceneContext,

        // Direct open methods (for buttons, etc.)
        openProgressionModal: () => {
            showProgressionModal.value = true;
        },
        openQuickCreateModal: (text: string = '') => {
            selectedTextForCreate.value = text;
            showQuickCreateModal.value = true;
        },
    };
}

export default useCodexEditor;
