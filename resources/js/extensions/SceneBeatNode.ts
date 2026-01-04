import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import SceneBeatNodeView from '@/components/editor/SceneBeatNodeView.vue';

export interface SceneBeatNodeOptions {
    sceneId: number;
}

export interface GenerationMetadata {
    beatText: string;
    connectionId: number | null;
    modelId: string | null;
    wordTarget: number;
}

declare module '@tiptap/core' {
    interface Commands<ReturnType> {
        sceneBeat: {
            /**
             * Insert a scene beat block
             */
            insertSceneBeat: () => ReturnType;
            /**
             * Remove a scene beat and replace with content wrapped in a Section
             */
            replaceSceneBeatWithContent: (pos: number, content: string, metadata?: GenerationMetadata) => ReturnType;
            /**
             * Create a section for streaming content
             */
            createStreamingSection: (pos: number, metadata?: GenerationMetadata) => ReturnType;
            /**
             * Append content to the last paragraph in a section
             */
            appendToSection: (sectionPos: number, content: string) => ReturnType;
            /**
             * Remove a scene beat block
             */
            removeSceneBeat: (pos: number) => ReturnType;
        };
    }
}

export const SceneBeatNode = Node.create<SceneBeatNodeOptions>({
    name: 'sceneBeat',

    group: 'block',

    atom: true, // Treated as a single unit

    draggable: true,

    selectable: true,

    addOptions() {
        return {
            sceneId: 0,
        };
    },

    addAttributes() {
        return {
            beatText: {
                default: '',
            },
            wordTarget: {
                default: 400,
            },
            connectionId: {
                default: null,
            },
            instructions: {
                default: '',
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-scene-beat]',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(HTMLAttributes, { 'data-scene-beat': '' }), 0];
    },

    addNodeView() {
        return VueNodeViewRenderer(SceneBeatNodeView);
    },

    addCommands() {
        return {
            insertSceneBeat:
                () =>
                ({ commands }) => {
                    return commands.insertContent({
                        type: this.name,
                    });
                },

            replaceSceneBeatWithContent:
                (pos: number, content: string, metadata?: GenerationMetadata) =>
                ({ tr, dispatch }) => {
                    if (dispatch) {
                        // Find the node at this position
                        const node = tr.doc.nodeAt(pos);
                        if (node && node.type.name === this.name) {
                            // Create paragraph nodes from the content
                            const paragraphs = content.split('\n\n').filter(Boolean);
                            const paragraphNodes = paragraphs.map((text) => ({
                                type: 'paragraph',
                                content: text ? [{ type: 'text', text }] : undefined,
                            }));

                            // Wrap content in a Section node with generation metadata
                            const sectionNode = {
                                type: 'section',
                                attrs: {
                                    type: 'generated',
                                    color: '#f59e0b', // Amber for generated
                                    isCollapsed: false,
                                    excludeFromAi: false,
                                    isCompleted: false,
                                    isGenerated: true,
                                    sourceBeat: metadata?.beatText || null,
                                    sourceConnectionId: metadata?.connectionId || null,
                                    sourceModelId: metadata?.modelId || null,
                                    sourceWordTarget: metadata?.wordTarget || null,
                                },
                                content: paragraphNodes.length > 0 ? paragraphNodes : [{ type: 'paragraph' }],
                            };

                            // Replace the scene beat with the section containing the content
                            tr.replaceWith(
                                pos,
                                pos + node.nodeSize,
                                tr.doc.type.schema.nodeFromJSON(sectionNode)
                            );
                        }
                    }
                    return true;
                },

            createStreamingSection:
                (pos: number, metadata?: GenerationMetadata) =>
                ({ tr, dispatch, state }) => {
                    if (dispatch) {
                        const node = tr.doc.nodeAt(pos);
                        if (node && node.type.name === this.name) {
                            // Create an empty section with generation metadata
                            const sectionNode = {
                                type: 'section',
                                attrs: {
                                    type: 'generated',
                                    color: '#f59e0b', // Amber for generated
                                    isCollapsed: false,
                                    excludeFromAi: false,
                                    isCompleted: false,
                                    isGenerated: true,
                                    sourceBeat: metadata?.beatText || null,
                                    sourceConnectionId: metadata?.connectionId || null,
                                    sourceModelId: metadata?.modelId || null,
                                    sourceWordTarget: metadata?.wordTarget || null,
                                },
                                content: [{ type: 'paragraph' }],
                            };

                            // Insert section AFTER the scene beat (not replace)
                            const afterBeatPos = pos + node.nodeSize;
                            tr.insert(afterBeatPos, state.schema.nodeFromJSON(sectionNode));
                        }
                    }
                    return true;
                },

            appendToSection:
                (sectionPos: number, content: string) =>
                ({ tr, dispatch, state }) => {
                    if (dispatch) {
                        const node = tr.doc.nodeAt(sectionPos);
                        if (node && node.type.name === 'section') {
                            // Find the last paragraph in the section
                            const lastChildPos = sectionPos + node.nodeSize - 2; // -2 for closing tags
                            let lastParagraphPos = sectionPos + 1; // Start of section content
                            
                            // Find the actual last paragraph position
                            node.forEach((child, offset) => {
                                if (child.type.name === 'paragraph') {
                                    lastParagraphPos = sectionPos + offset + 1;
                                }
                            });

                            const lastParagraph = tr.doc.nodeAt(lastParagraphPos);
                            if (lastParagraph && lastParagraph.type.name === 'paragraph') {
                                // Get current text
                                const currentText = lastParagraph.textContent;
                                // Create new text node
                                const newText = currentText + content;
                                const newTextNode = state.schema.text(newText);
                                
                                // Replace the paragraph content
                                tr.replaceWith(
                                    lastParagraphPos,
                                    lastParagraphPos + lastParagraph.nodeSize,
                                    state.schema.nodes.paragraph.create(null, newTextNode)
                                );
                            }
                        }
                    }
                    return true;
                },

            removeSceneBeat:
                (pos: number) =>
                ({ tr, dispatch }) => {
                    if (dispatch) {
                        const node = tr.doc.nodeAt(pos);
                        if (node && node.type.name === this.name) {
                            tr.delete(pos, pos + node.nodeSize);
                        }
                    }
                    return true;
                },
        };
    },
});

export default SceneBeatNode;
