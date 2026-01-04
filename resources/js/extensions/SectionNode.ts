import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import SectionNodeView from '@/components/editor/SectionNodeView.vue';

export interface SectionAttributes {
    id: number | null;
    type: 'content' | 'note' | 'alternative' | 'beat' | 'generated';
    title: string | null;
    color: string;
    isCollapsed: boolean;
    excludeFromAi: boolean;
    isCompleted: boolean;
    // Generation metadata (for AI-generated sections)
    isGenerated: boolean;
    sourceBeat: string | null;
    sourceConnectionId: number | null;
    sourceModelId: string | null;
    sourceWordTarget: number | null;
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
    generated: '#f59e0b', // Amber - for AI generated content
};

export const SECTION_TYPES = {
    content: 'Content',
    note: 'Note',
    alternative: 'Alternative',
    beat: 'Beat',
    generated: 'Generated',
} as const;

export interface SectionNodeOptions {
    sceneId: number;
}

export const SectionNode = Node.create<SectionNodeOptions>({
    name: 'section',

    group: 'block',

    content: 'block+',

    defining: true,

    addOptions() {
        return {
            sceneId: 0,
        };
    },

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
            // Generation metadata attributes
            isGenerated: {
                default: false,
                parseHTML: (element) => element.getAttribute('data-is-generated') === 'true',
                renderHTML: (attributes) => ({
                    'data-is-generated': attributes.isGenerated ? 'true' : 'false',
                }),
            },
            sourceBeat: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-source-beat'),
                renderHTML: (attributes) => {
                    if (!attributes.sourceBeat) return {};
                    return { 'data-source-beat': attributes.sourceBeat };
                },
            },
            sourceConnectionId: {
                default: null,
                parseHTML: (element) => {
                    const val = element.getAttribute('data-source-connection-id');
                    return val ? parseInt(val, 10) : null;
                },
                renderHTML: (attributes) => {
                    if (!attributes.sourceConnectionId) return {};
                    return { 'data-source-connection-id': attributes.sourceConnectionId };
                },
            },
            sourceModelId: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-source-model-id'),
                renderHTML: (attributes) => {
                    if (!attributes.sourceModelId) return {};
                    return { 'data-source-model-id': attributes.sourceModelId };
                },
            },
            sourceWordTarget: {
                default: null,
                parseHTML: (element) => {
                    const val = element.getAttribute('data-source-word-target');
                    return val ? parseInt(val, 10) : null;
                },
                renderHTML: (attributes) => {
                    if (!attributes.sourceWordTarget) return {};
                    return { 'data-source-word-target': attributes.sourceWordTarget };
                },
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
                ({ commands, state, tr, dispatch }) => {
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
                    const { selection } = state;
                    const { from, to, empty } = selection;

                    // If there's selected content, wrap it in a section
                    if (!empty) {
                        const sectionType = state.schema.nodes.section;
                        const paragraphType = state.schema.nodes.paragraph;

                        if (sectionType && paragraphType && dispatch) {
                            // Get the selected text
                            const selectedText = state.doc.textBetween(from, to, '\n\n');
                            
                            // Create paragraphs from the text (split by double newlines)
                            const paragraphs = selectedText.split('\n\n').filter(Boolean).map(text => {
                                return paragraphType.create(null, state.schema.text(text));
                            });

                            // If no valid paragraphs, create one with the raw text
                            const content = paragraphs.length > 0 
                                ? paragraphs 
                                : [paragraphType.create(null, state.schema.text(selectedText || ' '))];

                            // Create section node with the content
                            const sectionNode = sectionType.create(finalAttributes, content);

                            // Replace selection with the section containing the content
                            tr.replaceSelection(sectionNode);
                            dispatch(tr);
                            return true;
                        }
                    }

                    // No selection: insert empty section
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
