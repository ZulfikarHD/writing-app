import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import SectionNodeView from '@/components/editor/SectionNodeView.vue';

export interface SectionAttributes {
    id: number | null;
    type: 'content' | 'note' | 'alternative' | 'beat';
    title: string | null;
    color: string;
    isCollapsed: boolean;
    excludeFromAi: boolean;
    isCompleted: boolean;
}

declare module '@tiptap/core' {
    interface Commands<ReturnType> {
        sectionNode: {
            /**
             * Insert a section at the current position
             */
            insertSection: (attributes?: Partial<SectionAttributes>) => ReturnType;
            /**
             * Toggle section collapse state
             */
            toggleSectionCollapse: (pos: number) => ReturnType;
            /**
             * Update section attributes
             */
            updateSectionAttributes: (pos: number, attributes: Partial<SectionAttributes>) => ReturnType;
            /**
             * Remove a section but keep its content
             */
            dissolveSection: (pos: number) => ReturnType;
        };
    }
}

export const SECTION_TYPE_COLORS: Record<string, string> = {
    content: '#6366f1', // Indigo
    note: '#eab308', // Yellow
    alternative: '#8b5cf6', // Violet
    beat: '#22c55e', // Green
};

export const SECTION_TYPES = {
    content: 'Content',
    note: 'Note',
    alternative: 'Alternative',
    beat: 'Beat',
} as const;

export const SectionNode = Node.create({
    name: 'section',

    group: 'block',

    content: 'block+',

    defining: true,

    isolating: true,

    addAttributes() {
        return {
            id: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-section-id'),
                renderHTML: (attributes) => {
                    if (!attributes.id) {
                        return {};
                    }
                    return { 'data-section-id': attributes.id };
                },
            },
            type: {
                default: 'content',
                parseHTML: (element) => element.getAttribute('data-section-type') || 'content',
                renderHTML: (attributes) => ({
                    'data-section-type': attributes.type,
                }),
            },
            title: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-section-title'),
                renderHTML: (attributes) => {
                    if (!attributes.title) {
                        return {};
                    }
                    return { 'data-section-title': attributes.title };
                },
            },
            color: {
                default: SECTION_TYPE_COLORS.content,
                parseHTML: (element) => element.getAttribute('data-section-color') || SECTION_TYPE_COLORS.content,
                renderHTML: (attributes) => ({
                    'data-section-color': attributes.color,
                }),
            },
            isCollapsed: {
                default: false,
                parseHTML: (element) => element.getAttribute('data-collapsed') === 'true',
                renderHTML: (attributes) => ({
                    'data-collapsed': attributes.isCollapsed ? 'true' : 'false',
                }),
            },
            excludeFromAi: {
                default: false,
                parseHTML: (element) => element.getAttribute('data-exclude-ai') === 'true',
                renderHTML: (attributes) => ({
                    'data-exclude-ai': attributes.excludeFromAi ? 'true' : 'false',
                }),
            },
            isCompleted: {
                default: false,
                parseHTML: (element) => element.getAttribute('data-completed') === 'true',
                renderHTML: (attributes) => ({
                    'data-completed': attributes.isCompleted ? 'true' : 'false',
                }),
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-section]',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'div',
            mergeAttributes(HTMLAttributes, { 'data-section': '' }),
            0,
        ];
    },

    addNodeView() {
        return VueNodeViewRenderer(SectionNodeView, {
            // Enable drag handle on the section header
            draggable: true,
        });
    },

    // Allow dragging sections
    draggable: true,

    addCommands() {
        return {
            insertSection:
                (attributes = {}) =>
                ({ commands, state }) => {
                    const defaultType = attributes.type || 'content';
                    const defaultAttributes: SectionAttributes = {
                        id: null,
                        type: defaultType,
                        title: null,
                        color: SECTION_TYPE_COLORS[defaultType] || SECTION_TYPE_COLORS.content,
                        isCollapsed: false,
                        excludeFromAi: defaultType === 'note' || defaultType === 'alternative',
                        isCompleted: false,
                    };

                    const finalAttributes = { ...defaultAttributes, ...attributes };

                    return commands.insertContent({
                        type: this.name,
                        attrs: finalAttributes,
                        content: [
                            {
                                type: 'paragraph',
                            },
                        ],
                    });
                },

            toggleSectionCollapse:
                (pos: number) =>
                ({ tr, dispatch }) => {
                    const node = tr.doc.nodeAt(pos);
                    if (!node || node.type.name !== this.name) {
                        return false;
                    }

                    if (dispatch) {
                        tr.setNodeMarkup(pos, undefined, {
                            ...node.attrs,
                            isCollapsed: !node.attrs.isCollapsed,
                        });
                    }

                    return true;
                },

            updateSectionAttributes:
                (pos: number, attributes: Partial<SectionAttributes>) =>
                ({ tr, dispatch }) => {
                    const node = tr.doc.nodeAt(pos);
                    if (!node || node.type.name !== this.name) {
                        return false;
                    }

                    if (dispatch) {
                        tr.setNodeMarkup(pos, undefined, {
                            ...node.attrs,
                            ...attributes,
                        });
                    }

                    return true;
                },

            dissolveSection:
                (pos: number) =>
                ({ tr, dispatch, state }) => {
                    const node = tr.doc.nodeAt(pos);
                    if (!node || node.type.name !== this.name) {
                        return false;
                    }

                    if (dispatch) {
                        // Replace the section node with its content
                        const content = node.content;
                        tr.replaceWith(pos, pos + node.nodeSize, content);
                    }

                    return true;
                },
        };
    },

    addKeyboardShortcuts() {
        return {
            // Backspace at the start of a section should not delete it
            Backspace: ({ editor }) => {
                const { selection } = editor.state;
                const { $from } = selection;

                // Check if we're at the start of a section
                if ($from.parent.type.name === this.name && $from.parentOffset === 0) {
                    return true; // Prevent default backspace behavior
                }

                return false;
            },
        };
    },
});

export default SectionNode;
