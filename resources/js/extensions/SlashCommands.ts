import { Extension } from '@tiptap/core';
import Suggestion, { type SuggestionOptions } from '@tiptap/suggestion';
import { VueRenderer } from '@tiptap/vue-3';
import tippy, { type Instance } from 'tippy.js';
import SlashCommandsList from '@/components/editor/SlashCommandsList.vue';

export interface SlashCommand {
    title: string;
    description: string;
    icon: string;
    command: (editor: { chain: () => { focus: () => { run: () => void } } }) => void;
}

export interface SlashCommandsOptions {
    suggestion: Partial<SuggestionOptions>;
}

export const SlashCommands = Extension.create<SlashCommandsOptions>({
    name: 'slashCommands',

    addOptions() {
        return {
            suggestion: {
                char: '/',
                command: ({ editor, range, props }: { editor: any; range: any; props: SlashCommand }) => {
                    props.command(editor);
                    editor.chain().focus().deleteRange(range).run();
                },
            },
        };
    },

    addProseMirrorPlugins() {
        return [
            Suggestion({
                editor: this.editor,
                ...this.options.suggestion,
            }),
        ];
    },
});

// Default slash commands
export const defaultSlashCommands: SlashCommand[] = [
    {
        title: 'Section',
        description: 'Insert a new section block',
        icon: 'section',
        command: (editor: any) => {
            editor.chain().focus().insertSection({ type: 'content' }).run();
        },
    },
    {
        title: 'Note Section',
        description: 'Insert a note section (hidden from AI)',
        icon: 'note',
        command: (editor: any) => {
            editor.chain().focus().insertSection({ type: 'note' }).run();
        },
    },
    {
        title: 'Alternative Section',
        description: 'Insert an alternative version section',
        icon: 'alternative',
        command: (editor: any) => {
            editor.chain().focus().insertSection({ type: 'alternative' }).run();
        },
    },
    {
        title: 'Beat Section',
        description: 'Insert a beat/outline section',
        icon: 'beat',
        command: (editor: any) => {
            editor.chain().focus().insertSection({ type: 'beat' }).run();
        },
    },
    {
        title: 'Heading 1',
        description: 'Large section heading',
        icon: 'h1',
        command: (editor: any) => {
            editor.chain().focus().toggleHeading({ level: 1 }).run();
        },
    },
    {
        title: 'Heading 2',
        description: 'Medium section heading',
        icon: 'h2',
        command: (editor: any) => {
            editor.chain().focus().toggleHeading({ level: 2 }).run();
        },
    },
    {
        title: 'Heading 3',
        description: 'Small section heading',
        icon: 'h3',
        command: (editor: any) => {
            editor.chain().focus().toggleHeading({ level: 3 }).run();
        },
    },
    {
        title: 'Bullet List',
        description: 'Create a bulleted list',
        icon: 'list',
        command: (editor: any) => {
            editor.chain().focus().toggleBulletList().run();
        },
    },
    {
        title: 'Numbered List',
        description: 'Create a numbered list',
        icon: 'numbered',
        command: (editor: any) => {
            editor.chain().focus().toggleOrderedList().run();
        },
    },
    {
        title: 'Quote',
        description: 'Insert a blockquote',
        icon: 'quote',
        command: (editor: any) => {
            editor.chain().focus().toggleBlockquote().run();
        },
    },
    {
        title: 'Horizontal Rule',
        description: 'Insert a divider line',
        icon: 'divider',
        command: (editor: any) => {
            editor.chain().focus().setHorizontalRule().run();
        },
    },
];

// Suggestion configuration with popup rendering
export function createSlashCommandsSuggestion(commands: SlashCommand[] = defaultSlashCommands) {
    return {
        items: ({ query }: { query: string }) => {
            return commands.filter((command) =>
                command.title.toLowerCase().includes(query.toLowerCase())
            );
        },

        render: () => {
            let component: VueRenderer;
            let popup: Instance[];

            return {
                onStart: (props: any) => {
                    component = new VueRenderer(SlashCommandsList, {
                        props,
                        editor: props.editor,
                    });

                    if (!props.clientRect) {
                        return;
                    }

                    popup = tippy('body', {
                        getReferenceClientRect: props.clientRect,
                        appendTo: () => document.body,
                        content: component.element,
                        showOnCreate: true,
                        interactive: true,
                        trigger: 'manual',
                        placement: 'bottom-start',
                    });
                },

                onUpdate(props: any) {
                    component.updateProps(props);

                    if (!props.clientRect) {
                        return;
                    }

                    popup[0].setProps({
                        getReferenceClientRect: props.clientRect,
                    });
                },

                onKeyDown(props: any) {
                    if (props.event.key === 'Escape') {
                        popup[0].hide();
                        return true;
                    }

                    return component.ref?.onKeyDown(props);
                },

                onExit() {
                    popup[0].destroy();
                    component.destroy();
                },
            };
        },
    };
}

export default SlashCommands;
