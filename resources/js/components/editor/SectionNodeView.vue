<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { NodeViewWrapper, NodeViewContent } from '@tiptap/vue-3';
import SectionHeader from './SectionHeader.vue';
import SectionMenu from './SectionMenu.vue';
import SceneBeatEditor from './SceneBeatEditor.vue';
import GeneratedSectionHeader from './GeneratedSectionHeader.vue';
import { SECTION_TYPE_COLORS } from '@/extensions/SectionNode';

interface Props {
    node: {
        attrs: {
            id: number | null;
            type: 'content' | 'note' | 'alternative' | 'beat' | 'generated';
            title: string | null;
            color: string;
            isCollapsed: boolean;
            excludeFromAi: boolean;
            isCompleted: boolean;
            sceneId?: number;
            // Generation metadata
            isGenerated: boolean;
            sourceBeat: string | null;
            sourceConnectionId: number | null;
            sourceModelId: string | null;
            sourceWordTarget: number | null;
        };
        textContent: string;
        nodeSize: number;
    };
    updateAttributes: (attrs: Record<string, unknown>) => void;
    deleteNode: () => void;
    getPos: () => number;
    editor: {
        commands: {
            dissolveSection: (pos: number) => boolean;
        };
        chain: () => any;
        state: {
            schema: any;
        };
        view: {
            dispatch: (tr: any) => void;
            state: {
                tr: any;
            };
        };
    };
}


const props = defineProps<Props>();

// Get sceneId from editor's SectionNode options
const sceneId = computed(() => {
    const sectionExtension = props.editor.extensionManager?.extensions.find(
        (ext: { name: string }) => ext.name === 'section'
    );
    return sectionExtension?.options?.sceneId || 0;
});

const isEditing = ref(false);
const editedTitle = ref(props.node.attrs.title || '');
const menuOpen = ref(false);
const menuTriggerRect = ref<DOMRect | null>(null);
const beatCollapsed = ref(false);

// Check if this is a beat section
const isBeatSection = computed(() => props.node.attrs.type === 'beat');

// Check if this is a generated section
const isGeneratedSection = computed(() => props.node.attrs.isGenerated || props.node.attrs.type === 'generated');

// Compute word count from content
const wordCount = computed(() => {
    const text = props.node.textContent || '';
    return text.trim().split(/\s+/).filter(Boolean).length;
});

// Get background color based on type (with transparency)
const sectionStyle = computed(() => {
    const color = props.node.attrs.color || SECTION_TYPE_COLORS[props.node.attrs.type];
    return {
        '--section-color': color,
        borderLeftColor: color,
    };
});

// Handle collapse toggle
const toggleCollapse = () => {
    props.updateAttributes({ isCollapsed: !props.node.attrs.isCollapsed });
};

// Handle AI visibility toggle
const toggleAiVisibility = () => {
    props.updateAttributes({ excludeFromAi: !props.node.attrs.excludeFromAi });
};

// Handle type change
const changeType = (newType: 'content' | 'note' | 'alternative' | 'beat') => {
    const newColor = SECTION_TYPE_COLORS[newType];
    const newExcludeFromAi = newType === 'note' || newType === 'alternative';
    props.updateAttributes({
        type: newType,
        color: newColor,
        excludeFromAi: newExcludeFromAi,
    });
};

// Handle color change
const changeColor = (newColor: string) => {
    props.updateAttributes({ color: newColor });
};

// Handle title edit
const startTitleEdit = () => {
    editedTitle.value = props.node.attrs.title || '';
    isEditing.value = true;
};

const saveTitleEdit = () => {
    props.updateAttributes({ title: editedTitle.value || null });
    isEditing.value = false;
};

const cancelTitleEdit = () => {
    editedTitle.value = props.node.attrs.title || '';
    isEditing.value = false;
};

// Handle delete
const deleteSection = () => {
    props.deleteNode();
};

// Handle dissolve (unwrap content)
const dissolveSection = () => {
    const pos = props.getPos();
    props.editor.commands.dissolveSection(pos);
};

// Handle copy content
const copyContent = async () => {
    const text = props.node.textContent;
    await navigator.clipboard.writeText(text);
};

// Handle beat completion toggle
const toggleCompletion = () => {
    props.updateAttributes({ isCompleted: !props.node.attrs.isCompleted });
};

// Handle expand beat to prose - dispatches custom event for TipTapEditor to handle
const expandToProse = () => {
    const content = props.node.textContent;
    const event = new CustomEvent('expand-beat-to-prose', {
        bubbles: true,
        detail: {
            content,
            sectionType: props.node.attrs.type,
        },
    });
    // Dispatch from the editor's DOM element
    document.querySelector('.tiptap-editor')?.dispatchEvent(event);
};

// Beat section handlers
const handleBeatGenerate = (generatedContent: string) => {
    // Insert the generated content after this section
    const pos = props.getPos();
    const nodeSize = props.node.textContent.length + 2; // +2 for node boundaries
    
    // Insert paragraph with generated content after the beat section
    props.editor.chain()
        .insertContentAt(pos + nodeSize + 1, {
            type: 'paragraph',
            content: [{ type: 'text', text: generatedContent }],
        })
        .run();
    
    // Mark the beat as completed
    props.updateAttributes({ isCompleted: true });
};

const handleBeatHide = () => {
    beatCollapsed.value = true;
};

const handleBeatDelete = () => {
    props.deleteNode();
};

const handleBeatClear = () => {
    // Clear any content inside the beat
    props.updateAttributes({ isCompleted: false });
};

const handleBeatUpdate = (beatContent: string) => {
    // The beat content is stored in the node's text content
    // This is handled by TipTap's NodeViewContent
};

// Handle regeneration - replace content inside the section
const handleRegenerate = (
    content: string, 
    metadata: { beatText: string; connectionId: number | null; modelId: string | null; wordTarget: number }
) => {
    const pos = props.getPos();
    const node = props.node;
    const schema = props.editor.state.schema;
    
    // Create paragraph nodes from the new content
    const paragraphs = content.split('\n\n').filter(Boolean);
    const paragraphNodes = paragraphs.map((text) => 
        schema.nodes.paragraph.create(null, text ? schema.text(text) : null)
    );
    
    // Update the section content and metadata
    const tr = props.editor.view.state.tr;
    
    // First, update the attributes with new metadata
    props.updateAttributes({
        sourceBeat: metadata.beatText,
        sourceConnectionId: metadata.connectionId,
        sourceModelId: metadata.modelId,
        sourceWordTarget: metadata.wordTarget,
    });
    
    // Then replace the content inside the section (pos + 1 is start of content)
    const contentStart = pos + 1;
    const contentEnd = pos + node.nodeSize - 1;
    
    const fragment = schema.nodes.doc.create(null, paragraphNodes).content;
    tr.replaceWith(contentStart, contentEnd, fragment);
    
    props.editor.view.dispatch(tr);
};

// Handle opening menu with position
const handleOpenMenu = (event: Event) => {
    const target = event.target as HTMLElement;
    const button = target.closest('button');
    if (button) {
        menuTriggerRect.value = button.getBoundingClientRect();
    }
    menuOpen.value = true;
};

// Watch for external title changes
watch(
    () => props.node.attrs.title,
    (newTitle) => {
        if (!isEditing.value) {
            editedTitle.value = newTitle || '';
        }
    }
);
</script>

<template>
    <NodeViewWrapper
        :class="[
            'section-node group relative my-4',
            isBeatSection ? '' : 'rounded-lg border-l-4 bg-zinc-50 dark:bg-zinc-900/50',
        ]"
        :style="isBeatSection ? {} : sectionStyle"
        :data-section-type="node.attrs.type"
        :data-collapsed="node.attrs.isCollapsed || beatCollapsed"
        draggable="true"
        data-drag-handle
    >
        <!-- Beat Section: Use SceneBeatEditor -->
        <template v-if="isBeatSection && sceneId && !beatCollapsed">
            <SceneBeatEditor
                :scene-id="sceneId"
                :initial-beat="node.textContent"
                :is-collapsed="node.attrs.isCollapsed"
                @generate="handleBeatGenerate"
                @hide="handleBeatHide"
                @delete="handleBeatDelete"
                @clear="handleBeatClear"
                @update:beat="handleBeatUpdate"
            />
        </template>
        
        <!-- Beat Section: Collapsed State -->
        <template v-else-if="isBeatSection && beatCollapsed">
            <div 
                class="flex items-center gap-2 px-3 py-2 bg-zinc-800 border border-zinc-700 rounded-lg cursor-pointer hover:bg-zinc-700/50 transition-colors"
                @click="beatCollapsed = false"
            >
                <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span class="text-xs font-medium text-violet-400 uppercase">Scene Beat</span>
                <span class="text-xs text-zinc-500 truncate flex-1">{{ node.textContent.substring(0, 50) }}{{ node.textContent.length > 50 ? '...' : '' }}</span>
                <span class="text-xs text-zinc-500">(click to expand)</span>
            </div>
        </template>

        <!-- Generated Sections: Special UI with regenerate -->
        <template v-else-if="isGeneratedSection">
            <GeneratedSectionHeader
                :source-beat="node.attrs.sourceBeat"
                :source-connection-id="node.attrs.sourceConnectionId"
                :source-model-id="node.attrs.sourceModelId"
                :source-word-target="node.attrs.sourceWordTarget"
                :is-collapsed="node.attrs.isCollapsed"
                :word-count="wordCount"
                :scene-id="sceneId"
                @toggle-collapse="toggleCollapse"
                @regenerate="handleRegenerate"
                @open-menu="handleOpenMenu"
                @dissolve="dissolveSection"
            />

            <!-- Section Content -->
            <div
                v-show="!node.attrs.isCollapsed"
                class="section-content px-4 pb-4"
            >
                <NodeViewContent class="prose prose-zinc dark:prose-invert max-w-none" />
            </div>

            <!-- Collapsed Preview -->
            <div
                v-if="node.attrs.isCollapsed"
                class="px-4 pb-3 text-sm text-zinc-500 dark:text-zinc-400 italic truncate"
            >
                {{ node.textContent.substring(0, 100) }}{{ node.textContent.length > 100 ? '...' : '' }}
            </div>

            <!-- Section Menu -->
            <SectionMenu
                :open="menuOpen"
                :type="node.attrs.type"
                :color="node.attrs.color"
                :word-count="wordCount"
                :trigger-rect="menuTriggerRect"
                @close="menuOpen = false"
                @change-type="changeType"
                @change-color="changeColor"
                @copy="copyContent"
                @dissolve="dissolveSection"
                @delete="deleteSection"
            />
        </template>

        <!-- Non-Beat Sections: Regular Section UI -->
        <template v-else>
            <!-- Section Header -->
            <SectionHeader
                :type="node.attrs.type"
                :title="node.attrs.title"
                :is-collapsed="node.attrs.isCollapsed"
                :exclude-from-ai="node.attrs.excludeFromAi"
                :color="node.attrs.color"
                :word-count="wordCount"
                :is-editing="isEditing"
                :edited-title="editedTitle"
                :is-completed="node.attrs.isCompleted"
                @toggle-collapse="toggleCollapse"
                @toggle-ai-visibility="toggleAiVisibility"
                @start-title-edit="startTitleEdit"
                @save-title-edit="saveTitleEdit"
                @cancel-title-edit="cancelTitleEdit"
                @update:edited-title="editedTitle = $event"
                @open-menu="handleOpenMenu"
                @expand-to-prose="expandToProse"
                @toggle-completion="toggleCompletion"
            />

            <!-- Section Content -->
            <div
                v-show="!node.attrs.isCollapsed"
                class="section-content px-4 pb-4"
            >
                <NodeViewContent class="prose prose-zinc dark:prose-invert max-w-none" />
            </div>

            <!-- Collapsed Preview -->
            <div
                v-if="node.attrs.isCollapsed"
                class="px-4 pb-3 text-sm text-zinc-500 dark:text-zinc-400 italic truncate"
            >
                {{ node.textContent.substring(0, 100) }}{{ node.textContent.length > 100 ? '...' : '' }}
            </div>

            <!-- Section Menu -->
            <SectionMenu
                :open="menuOpen"
                :type="node.attrs.type"
                :color="node.attrs.color"
                :word-count="wordCount"
                :trigger-rect="menuTriggerRect"
                @close="menuOpen = false"
                @change-type="changeType"
                @change-color="changeColor"
                @copy="copyContent"
                @dissolve="dissolveSection"
                @delete="deleteSection"
            />
        </template>
    </NodeViewWrapper>
</template>

<style scoped>
.section-node {
    transition: all 0.2s ease;
}

.section-node:hover {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.section-content :deep(.ProseMirror) {
    outline: none;
    min-height: 1.5rem;
}

.section-content :deep(.ProseMirror) p {
    margin: 0.5em 0;
}

.section-content :deep(.ProseMirror) p:first-child {
    margin-top: 0;
}

.section-content :deep(.ProseMirror) p:last-child {
    margin-bottom: 0;
}

/* Drag handle cursor */
.section-node[data-drag-handle] {
    cursor: grab;
}

.section-node[data-drag-handle]:active {
    cursor: grabbing;
}
</style>
