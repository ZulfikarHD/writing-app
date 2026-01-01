import { Extension } from '@tiptap/core';

/**
 * CodexProgression Extension - Add progressions from the editor
 *
 * Sprint 15 (US-12.9): Enables adding codex progressions without leaving
 * the writing flow. Triggered via /progression slash command or Cmd+Shift+P.
 *
 * This extension:
 * - Listens for the /progression slash command
 * - Provides keyboard shortcut (Cmd+Shift+P / Ctrl+Shift+P)
 * - Emits 'openProgressionModal' event for the parent component to handle
 *
 * @see https://www.novelcrafter.com/help/docs/codex/progressions-additions
 */

export interface CodexProgressionOptions {
    /**
     * Callback when the progression modal should open.
     * The parent component handles the actual modal display.
     */
    onOpenModal?: () => void;
}

export const CodexProgression = Extension.create<CodexProgressionOptions>({
    name: 'codexProgression',

    addOptions() {
        return {
            onOpenModal: undefined,
        };
    },

    addCommands() {
        return {
            openProgressionModal:
                () =>
                ({ editor }) => {
                    // Call the callback if provided
                    if (this.options.onOpenModal) {
                        this.options.onOpenModal();
                    }

                    // Emit a custom event that parent components can listen to
                    // This allows for flexible integration without tight coupling
                    const event = new CustomEvent('codex:open-progression-modal', {
                        detail: {
                            editorView: editor.view,
                        },
                    });
                    window.dispatchEvent(event);

                    return true;
                },
        };
    },

    addKeyboardShortcuts() {
        return {
            // Cmd+Shift+P (Mac) / Ctrl+Shift+P (Windows/Linux)
            'Mod-Shift-p': () => this.editor.commands.openProgressionModal(),
        };
    },

    addInputRules() {
        return [];
    },

    /**
     * Watch for slash command pattern: /progression
     * When detected, open the modal and remove the command text.
     */
    onTransaction({ transaction }) {
        // Only check if there was text inserted
        if (!transaction.docChanged) return;

        // Get the current selection position
        const { from } = transaction.selection;

        // Get text before cursor (last 20 characters should be enough)
        const textBefore = transaction.doc.textBetween(Math.max(0, from - 20), from, '');

        // Check for /progression or /prog command at the end
        const progressionMatch = textBefore.match(/\/progression\s*$/i) || textBefore.match(/\/prog\s*$/i);

        if (progressionMatch) {
            // Remove the slash command text
            const deleteFrom = from - progressionMatch[0].length;

            // Use setTimeout to avoid conflicts with the current transaction
            setTimeout(() => {
                this.editor
                    .chain()
                    .focus()
                    .deleteRange({ from: deleteFrom, to: from })
                    .run();

                // Open the modal
                this.editor.commands.openProgressionModal();
            }, 0);
        }
    },
});

export default CodexProgression;
