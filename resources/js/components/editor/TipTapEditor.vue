<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import CharacterCount from '@tiptap/extension-character-count';
import Underline from '@tiptap/extension-underline';
import { watch, computed, onBeforeUnmount } from 'vue';

interface Props {
    modelValue: object | null;
    placeholder?: string;
    editable?: boolean;
    autofocus?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    placeholder: 'Start writing your story...',
    editable: true,
    autofocus: true,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: object): void;
    (e: 'update', editor: ReturnType<typeof useEditor>): void;
    (e: 'focus'): void;
    (e: 'blur'): void;
}>();

const editor = useEditor({
    content: props.modelValue,
    editable: props.editable,
    autofocus: props.autofocus ? 'end' : false,
    extensions: [
        StarterKit.configure({
            history: {
                depth: 100,
            },
        }),
        Underline,
        Placeholder.configure({
            placeholder: props.placeholder,
            emptyEditorClass: 'is-editor-empty',
        }),
        CharacterCount,
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-zinc dark:prose-invert max-w-none focus:outline-none min-h-[calc(100vh-16rem)] px-4 py-8 md:px-8 lg:px-16',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getJSON());
        emit('update', editor);
    },
    onFocus: () => {
        emit('focus');
    },
    onBlur: () => {
        emit('blur');
    },
});

watch(
    () => props.modelValue,
    (newValue) => {
        if (!editor.value) return;
        const currentContent = JSON.stringify(editor.value.getJSON());
        const newContent = JSON.stringify(newValue);
        if (currentContent !== newContent) {
            editor.value.commands.setContent(newValue || {});
        }
    },
    { deep: true }
);

watch(
    () => props.editable,
    (newValue) => {
        editor.value?.setEditable(newValue);
    }
);

const wordCount = computed(() => {
    return editor.value?.storage.characterCount.words() ?? 0;
});

const characterCount = computed(() => {
    return editor.value?.storage.characterCount.characters() ?? 0;
});

const undo = () => editor.value?.chain().focus().undo().run();
const redo = () => editor.value?.chain().focus().redo().run();
const canUndo = computed(() => editor.value?.can().undo() ?? false);
const canRedo = computed(() => editor.value?.can().redo() ?? false);

const toggleBold = () => editor.value?.chain().focus().toggleBold().run();
const toggleItalic = () => editor.value?.chain().focus().toggleItalic().run();
const toggleUnderline = () => editor.value?.chain().focus().toggleUnderline().run();
const toggleStrike = () => editor.value?.chain().focus().toggleStrike().run();

const isBold = computed(() => editor.value?.isActive('bold') ?? false);
const isItalic = computed(() => editor.value?.isActive('italic') ?? false);
const isUnderline = computed(() => editor.value?.isActive('underline') ?? false);
const isStrike = computed(() => editor.value?.isActive('strike') ?? false);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

defineExpose({
    editor,
    wordCount,
    characterCount,
    undo,
    redo,
    canUndo,
    canRedo,
    toggleBold,
    toggleItalic,
    toggleUnderline,
    toggleStrike,
    isBold,
    isItalic,
    isUnderline,
    isStrike,
});
</script>

<template>
    <div class="tiptap-editor">
        <EditorContent :editor="editor" />
    </div>
</template>

<style>
.tiptap p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #a1a1aa;
    pointer-events: none;
    height: 0;
}

.dark .tiptap p.is-editor-empty:first-child::before {
    color: #71717a;
}

.tiptap:focus {
    outline: none;
}

.tiptap ::selection {
    background-color: #ddd6fe;
}

.dark .tiptap ::selection {
    background-color: #5b21b6;
}

.tiptap {
    font-family: 'Georgia', serif;
    font-size: 1.125rem;
    line-height: 1.8;
}

.tiptap p {
    margin-bottom: 1em;
}

.tiptap h1,
.tiptap h2,
.tiptap h3 {
    font-family: system-ui, -apple-system, sans-serif;
    font-weight: 600;
    margin-top: 1.5em;
    margin-bottom: 0.5em;
}
</style>
