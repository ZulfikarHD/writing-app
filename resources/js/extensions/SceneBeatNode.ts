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
