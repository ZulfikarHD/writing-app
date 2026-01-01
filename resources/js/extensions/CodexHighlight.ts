import { Extension } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';
import { Decoration, DecorationSet } from '@tiptap/pm/view';

export interface CodexEntry {
    id: number;
    type: string;
    name: string;
    aliases: string[];
}

export interface CodexHighlightOptions {
    entries: CodexEntry[];
    typeColors: Record<string, string>;
}

// Type to underline color mapping
const defaultTypeColors: Record<string, string> = {
    character: '#a855f7', // purple
    location: '#3b82f6', // blue
    item: '#f59e0b', // amber
    lore: '#10b981', // emerald
    organization: '#f43f5e', // rose
    subplot: '#06b6d4', // cyan
};

export const codexHighlightPluginKey = new PluginKey('codexHighlight');

export const CodexHighlight = Extension.create<CodexHighlightOptions>({
    name: 'codexHighlight',

    addOptions() {
        return {
            entries: [],
            typeColors: defaultTypeColors,
        };
    },

    addProseMirrorPlugins() {
        const { entries, typeColors } = this.options;

        return [
            new Plugin({
                key: codexHighlightPluginKey,
                state: {
                    init: (_, state) => {
                        return createDecorations(state.doc, entries, typeColors);
                    },
                    apply: (tr, oldSet) => {
                        // Only recalculate if document changed
                        if (tr.docChanged) {
                            return createDecorations(tr.doc, entries, typeColors);
                        }
                        return oldSet.map(tr.mapping, tr.doc);
                    },
                },
                props: {
                    decorations(state) {
                        return this.getState(state);
                    },
                },
            }),
        ];
    },
});

function createDecorations(
    doc: import('@tiptap/pm/model').Node,
    entries: CodexEntry[],
    typeColors: Record<string, string>
): DecorationSet {
    if (entries.length === 0) {
        return DecorationSet.empty;
    }

    const decorations: Decoration[] = [];

    // Build a map of search terms to entry info
    const termMap = new Map<string, { entryId: number; type: string }>();
    entries.forEach((entry) => {
        // Add the entry name
        const lowerName = entry.name.toLowerCase();
        termMap.set(lowerName, { entryId: entry.id, type: entry.type });

        // Add all aliases
        entry.aliases.forEach((alias) => {
            const lowerAlias = alias.toLowerCase();
            if (!termMap.has(lowerAlias)) {
                termMap.set(lowerAlias, { entryId: entry.id, type: entry.type });
            }
        });
    });

    // Convert to sorted array (longer terms first to match longer phrases before shorter)
    const sortedTerms = Array.from(termMap.keys()).sort((a, b) => b.length - a.length);

    if (sortedTerms.length === 0) {
        return DecorationSet.empty;
    }

    // Build regex pattern for word boundary matching
    const escapedTerms = sortedTerms.map((t) => escapeRegExp(t));
    const pattern = new RegExp(`\\b(${escapedTerms.join('|')})\\b`, 'gi');

    // Walk through all text nodes
    doc.descendants((node, pos) => {
        if (!node.isText || !node.text) {
            return;
        }

        const text = node.text;
        let match;

        // Reset regex lastIndex
        pattern.lastIndex = 0;

        while ((match = pattern.exec(text)) !== null) {
            const matchedText = match[0];
            const lowerMatched = matchedText.toLowerCase();
            const entryInfo = termMap.get(lowerMatched);

            if (entryInfo) {
                const from = pos + match.index;
                const to = from + matchedText.length;
                const color = typeColors[entryInfo.type] || '#71717a';

                decorations.push(
                    Decoration.inline(from, to, {
                        class: 'codex-mention',
                        style: `border-bottom: 2px solid ${color};`,
                        'data-entry-id': String(entryInfo.entryId),
                        'data-entry-type': entryInfo.type,
                    })
                );
            }
        }
    });

    return DecorationSet.create(doc, decorations);
}

function escapeRegExp(string: string): string {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

export default CodexHighlight;
