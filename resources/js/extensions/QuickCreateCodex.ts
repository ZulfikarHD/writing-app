import { Extension } from '@tiptap/core';

/**
 * QuickCreateCodex Extension - Create codex entries from text selection
 *
 * Sprint 15 (US-12.11): Enables creating codex entries directly from selected
 * text in the editor, building the world database while writing.
 *
 * This extension:
 * - Provides keyboard shortcut (Cmd+Shift+C / Ctrl+Shift+C)
 * - Emits 'openQuickCreateModal' event with selected text
 * - Listens for 'codex-entry-created' to refresh highlighting
 *
 * @see https://www.novelcrafter.com/help/docs/codex/codex-details-quick-create
 */

export interface QuickCreateCodexOptions {
    /**
     * Callback when the quick create modal should open.
     * Receives the selected text.
     */
    onOpenModal?: (selectedText: string) => void;

    /**
     * Callback when codex entries are updated (for refreshing highlights).
     */
    onEntriesUpdated?: () => void;
}

export const QuickCreateCodex = Extension.create<QuickCreateCodexOptions>({
    name: 'quickCreateCodex',

    addOptions() {
        return {
            onOpenModal: undefined,
            onEntriesUpdated: undefined,
        };
    },

    addCommands() {
        return {
            openQuickCreateModal:
                () =>
                ({ editor, state }) => {
                    // Get selected text
                    const { from, to } = state.selection;
                    const selectedText = state.doc.textBetween(from, to, ' ').trim();

                    // Don't open if no text is selected
                    if (!selectedText) {
                        return false;
                    }

                    // Truncate if too long (max 255 chars for name)
                    const truncatedText = selectedText.length > 255 
                        ? selectedText.substring(0, 255) 
                        : selectedText;

                    // Call the callback if provided
                    if (this.options.onOpenModal) {
                        this.options.onOpenModal(truncatedText);
                    }

                    // Emit a custom event that parent components can listen to
                    const event = new CustomEvent('codex:open-quick-create-modal', {
                        detail: {
                            selectedText: truncatedText,
                            editorView: editor.view,
                            selection: { from, to },
                        },
                    });
                    window.dispatchEvent(event);

                    return true;
                },

            /**
             * Refresh codex mentions after a new entry is created.
             * This triggers the CodexHighlight extension to re-scan.
             */
            refreshCodexMentions:
                () =>
                ({ editor }) => {
                    // Trigger a transaction to force decoration recalculation
                    const { state } = editor;
                    const tr = state.tr;
                    editor.view.dispatch(tr);

                    // Call the callback if provided
                    if (this.options.onEntriesUpdated) {
                        this.options.onEntriesUpdated();
                    }

                    return true;
                },
        };
    },

    addKeyboardShortcuts() {
        return {
            // Cmd+Shift+C (Mac) / Ctrl+Shift+C (Windows/Linux)
            'Mod-Shift-c': () => this.editor.commands.openQuickCreateModal(),
        };
    },

    onCreate() {
        // Listen for entry creation events to refresh highlights
        const handleEntryCreated = () => {
            this.editor.commands.refreshCodexMentions();
        };

        window.addEventListener('codex-entry-created', handleEntryCreated);

        // Store the handler for cleanup
        (this as any)._handleEntryCreated = handleEntryCreated;
    },

    onDestroy() {
        // Clean up event listener
        const handler = (this as any)._handleEntryCreated;
        if (handler) {
            window.removeEventListener('codex-entry-created', handler);
        }
    },
});

export default QuickCreateCodex;
