import { Mark, mergeAttributes } from '@tiptap/core';

export interface HighlightMarkOptions {
    HTMLAttributes: Record<string, unknown>;
    colors: string[];
}

declare module '@tiptap/core' {
    interface Commands<ReturnType> {
        highlightMark: {
            /**
             * Set a highlight mark with a specific color
             */
            setHighlight: (color: string) => ReturnType;
            /**
             * Toggle a highlight mark with a specific color
             */
            toggleHighlight: (color: string) => ReturnType;
            /**
             * Remove highlight mark
             */
            unsetHighlight: () => ReturnType;
        };
    }
}

/**
 * Preset highlight colors for the writing app.
 * These colors are optimized for readability in both light and dark modes.
 */
export const HIGHLIGHT_COLORS = {
    yellow: '#fef08a',
    green: '#bbf7d0',
    blue: '#bfdbfe',
    pink: '#fbcfe8',
    orange: '#fed7aa',
    purple: '#e9d5ff',
    cyan: '#a5f3fc',
    red: '#fecaca',
} as const;

export type HighlightColor = keyof typeof HIGHLIGHT_COLORS;

export const HighlightMark = Mark.create<HighlightMarkOptions>({
    name: 'highlight',

    addOptions() {
        return {
            HTMLAttributes: {},
            colors: Object.values(HIGHLIGHT_COLORS),
        };
    },

    addAttributes() {
        return {
            color: {
                default: HIGHLIGHT_COLORS.yellow,
                parseHTML: (element) =>
                    element.style.backgroundColor || element.getAttribute('data-color') || HIGHLIGHT_COLORS.yellow,
                renderHTML: (attributes) => {
                    if (!attributes.color) {
                        return {};
                    }
                    return {
                        'data-color': attributes.color,
                        style: `background-color: ${attributes.color}`,
                    };
                },
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'mark',
            },
            {
                tag: 'span[data-highlight]',
            },
            {
                style: 'background-color',
                getAttrs: (value) => {
                    // Only match highlight colors, not other background colors
                    if (typeof value === 'string' && Object.values(HIGHLIGHT_COLORS).includes(value as string)) {
                        return { color: value };
                    }
                    return false;
                },
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'mark',
            mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, {
                'data-highlight': '',
            }),
            0,
        ];
    },

    addCommands() {
        return {
            setHighlight:
                (color: string) =>
                ({ commands }) => {
                    return commands.setMark(this.name, { color });
                },
            toggleHighlight:
                (color: string) =>
                ({ commands }) => {
                    return commands.toggleMark(this.name, { color });
                },
            unsetHighlight:
                () =>
                ({ commands }) => {
                    return commands.unsetMark(this.name);
                },
        };
    },

    addKeyboardShortcuts() {
        return {
            // Ctrl/Cmd + Shift + H to toggle highlight (default yellow)
            'Mod-Shift-h': () => this.editor.commands.toggleHighlight(HIGHLIGHT_COLORS.yellow),
        };
    },
});

export default HighlightMark;
