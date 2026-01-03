<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { NodeViewWrapper, NodeViewContent } from '@tiptap/vue-3';
import SectionHeader from './SectionHeader.vue';
import SectionMenu from './SectionMenu.vue';
import { SECTION_TYPE_COLORS, SECTION_TYPES } from '@/extensions/SectionNode';

interface Props {
    node: {
        attrs: {
            id: number | null;
            type: 'content' | 'note' | 'alternative' | 'beat';
            title: string | null;
            color: string;
            isCollapsed: boolean;
            excludeFromAi: boolean;
        };
        textContent: string;
    };
    updateAttributes: (attrs: Record<string, unknown>) => void;
    deleteNode: () => void;
    getPos: () => number;
    editor: {
        commands: {
            dissolveSection: (pos: number) => boolean;
        };
    };
}

const props = defineProps<Props>();

const isEditing = ref(false);
const editedTitle = ref(props.node.attrs.title || '');
const menuOpen = ref(false);

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
        class="section-node group relative my-4 rounded-lg border-l-4 bg-zinc-50 dark:bg-zinc-900/50"
        :style="sectionStyle"
        :data-section-type="node.attrs.type"
        :data-collapsed="node.attrs.isCollapsed"
        draggable="true"
        data-drag-handle
    >
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
            @toggle-collapse="toggleCollapse"
            @toggle-ai-visibility="toggleAiVisibility"
            @start-title-edit="startTitleEdit"
            @save-title-edit="saveTitleEdit"
            @cancel-title-edit="cancelTitleEdit"
            @update:edited-title="editedTitle = $event"
            @open-menu="menuOpen = true"
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
            @close="menuOpen = false"
            @change-type="changeType"
            @change-color="changeColor"
            @copy="copyContent"
            @dissolve="dissolveSection"
            @delete="deleteSection"
        />
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
