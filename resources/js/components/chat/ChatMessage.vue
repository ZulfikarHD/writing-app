<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { marked } from 'marked';
import hljs from 'highlight.js/lib/core';
import 'highlight.js/styles/github-dark.css';
import ModelSelector from '@/components/ai/ModelSelector.vue';

// Import commonly used languages
import javascript from 'highlight.js/lib/languages/javascript';
import typescript from 'highlight.js/lib/languages/typescript';
import python from 'highlight.js/lib/languages/python';
import php from 'highlight.js/lib/languages/php';
import java from 'highlight.js/lib/languages/java';
import cpp from 'highlight.js/lib/languages/cpp';
import csharp from 'highlight.js/lib/languages/csharp';
import go from 'highlight.js/lib/languages/go';
import rust from 'highlight.js/lib/languages/rust';
import json from 'highlight.js/lib/languages/json';
import xml from 'highlight.js/lib/languages/xml';
import css from 'highlight.js/lib/languages/css';
import scss from 'highlight.js/lib/languages/scss';
import sql from 'highlight.js/lib/languages/sql';
import bash from 'highlight.js/lib/languages/bash';
import yaml from 'highlight.js/lib/languages/yaml';
import markdown from 'highlight.js/lib/languages/markdown';

// Register languages
hljs.registerLanguage('javascript', javascript);
hljs.registerLanguage('typescript', typescript);
hljs.registerLanguage('python', python);
hljs.registerLanguage('php', php);
hljs.registerLanguage('java', java);
hljs.registerLanguage('cpp', cpp);
hljs.registerLanguage('csharp', csharp);
hljs.registerLanguage('go', go);
hljs.registerLanguage('rust', rust);
hljs.registerLanguage('json', json);
hljs.registerLanguage('xml', xml);
hljs.registerLanguage('html', xml);
hljs.registerLanguage('css', css);
hljs.registerLanguage('scss', scss);
hljs.registerLanguage('sql', sql);
hljs.registerLanguage('bash', bash);
hljs.registerLanguage('shell', bash);
hljs.registerLanguage('yaml', yaml);
hljs.registerLanguage('markdown', markdown);

interface Message {
    id: number;
    thread_id: number;
    role: 'user' | 'assistant' | 'system';
    content: string;
    model_used: string | null;
    tokens_input: number | null;
    tokens_output: number | null;
    created_at: string;
}

interface CodexAliasEntry {
    id: number;
    name: string;
    type: string;
    alias?: string;
    description?: string;
}

const props = withDefaults(
    defineProps<{
        message: Message;
        isStreaming?: boolean;
        aliasLookup?: Record<string, CodexAliasEntry>;
        enableAliasLinking?: boolean;
    }>(),
    {
        isStreaming: false,
        aliasLookup: () => ({}),
        enableAliasLinking: true,
    }
);

const emit = defineEmits<{
    (e: 'transfer', message: Message): void;
    (e: 'extract', message: Message): void;
    (e: 'codex-click', entryId: number): void;
    (e: 'regenerate', message: Message): void;
    (e: 'regenerate-with-model', message: Message, model: string, connectionId: number): void;
    (e: 'edit', message: Message, newContent: string): void;
}>();

const copied = ref(false);
const showActions = ref(false);
const isEditing = ref(false);
const editContent = ref('');
const editTextarea = ref<HTMLTextAreaElement | null>(null);
const showRegenerateMenu = ref(false);
const regenerateMenuRef = ref<HTMLElement | null>(null);
const regenerateButtonRef = ref<HTMLElement | null>(null);
const regenerateModel = ref('');
const regenerateConnectionId = ref<number | undefined>(undefined);
const modelSelectorKey = ref(0);
const menuPosition = ref({ top: '0px', left: '0px' });

const isUser = computed(() => props.message.role === 'user');
const isAssistant = computed(() => props.message.role === 'assistant');

// Sorted aliases for matching (longest first)
const sortedAliases = computed(() => {
    return Object.keys(props.aliasLookup).sort((a, b) => b.length - a.length);
});

// Get entry type badge color
const getTypeBadgeClass = (type: string): string => {
    const colors: Record<string, string> = {
        character: 'codex-link-character',
        location: 'codex-link-location',
        item: 'codex-link-item',
        event: 'codex-link-event',
        concept: 'codex-link-concept',
        faction: 'codex-link-faction',
        species: 'codex-link-species',
        other: 'codex-link-other',
    };
    return colors[type] || colors.other;
};

/**
 * Link codex aliases in text content.
 * Only links within plain text, avoiding HTML tags and code blocks.
 */
const linkAliasesInText = (text: string): string => {
    if (!props.enableAliasLinking || sortedAliases.value.length === 0) {
        return text;
    }

    // Track replacements to avoid double-processing
    type Replacement = { start: number; end: number; html: string };
    const replacements: Replacement[] = [];

    for (const alias of sortedAliases.value) {
        const entry = props.aliasLookup[alias];
        if (!entry) continue;

        const escapedAlias = alias.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`\\b${escapedAlias}\\b`, 'gi');

        let match;
        while ((match = regex.exec(text)) !== null) {
            // Check if this range overlaps with any existing replacement
            const overlaps = replacements.some(
                (r) =>
                    (match!.index >= r.start && match!.index < r.end) ||
                    (match!.index + match![0].length > r.start && match!.index + match![0].length <= r.end)
            );

            if (!overlaps) {
                const typeClass = getTypeBadgeClass(entry.type);
                const html = `<a href="/codex/${entry.id}" class="codex-alias-link ${typeClass}" data-codex-id="${entry.id}" data-codex-type="${entry.type}" title="${entry.name}${entry.alias ? ` (alias: ${entry.alias})` : ''} - ${entry.type}">${match[0]}</a>`;

                replacements.push({
                    start: match.index,
                    end: match.index + match[0].length,
                    html,
                });
            }
        }
    }

    // Sort replacements by position (descending) to replace from end to start
    replacements.sort((a, b) => b.start - a.start);

    // Apply replacements
    let result = text;
    for (const r of replacements) {
        result = result.slice(0, r.start) + r.html + result.slice(r.end);
    }

    return result;
};

// Action handlers
const handleTransfer = () => {
    emit('transfer', props.message);
};

const handleExtract = () => {
    emit('extract', props.message);
};

const handleRegenerate = () => {
    emit('regenerate', props.message);
};

const handleMouseLeave = () => {
    // Don't hide actions if the regenerate menu is open
    if (!showRegenerateMenu.value) {
        showActions.value = false;
    }
};

const toggleRegenerateMenu = async () => {
    if (!showRegenerateMenu.value) {
        // Reset state when opening
        regenerateModel.value = '';
        regenerateConnectionId.value = undefined;
        modelSelectorKey.value++;
        
        // Calculate position
        await nextTick();
        if (regenerateButtonRef.value) {
            const rect = regenerateButtonRef.value.getBoundingClientRect();
            const menuWidth = 288; // w-72 = 18rem = 288px
            const menuHeight = 180; // approximate height
            
            // Position above the button by default
            let top = rect.top - menuHeight - 8;
            let left = rect.left;
            
            // If it would go off the top, position below instead
            if (top < 8) {
                top = rect.bottom + 8;
            }
            
            // If it would go off the right, align to the right edge
            if (left + menuWidth > window.innerWidth - 8) {
                left = window.innerWidth - menuWidth - 8;
            }
            
            // Keep within left bounds
            left = Math.max(8, left);
            
            menuPosition.value = {
                top: `${top}px`,
                left: `${left}px`,
            };
        }
    }
    showRegenerateMenu.value = !showRegenerateMenu.value;
};

const handleRegenerateWithModel = () => {
    if (regenerateModel.value && regenerateConnectionId.value) {
        emit('regenerate-with-model', props.message, regenerateModel.value, regenerateConnectionId.value);
        showRegenerateMenu.value = false;
    }
};

const startEdit = async () => {
    isEditing.value = true;
    editContent.value = props.message.content;
    await nextTick();
    if (editTextarea.value) {
        editTextarea.value.focus();
        editTextarea.value.select();
    }
};

const cancelEdit = () => {
    isEditing.value = false;
    editContent.value = '';
};

const submitEdit = () => {
    if (editContent.value.trim()) {
        emit('edit', props.message, editContent.value.trim());
        isEditing.value = false;
        editContent.value = '';
    }
};

// Close regenerate menu when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    if (!showRegenerateMenu.value) return;
    const target = event.target as HTMLElement;
    
    // Don't close if clicking inside the menu or the trigger button
    if (regenerateMenuRef.value?.contains(target)) return;
    if (regenerateButtonRef.value?.contains(target)) return;
    
    // Don't close if clicking inside any ModelSelector's fixed dropdown
    const modelSelectorDropdowns = document.querySelectorAll('.fixed.z-\\[9999\\]');
    for (const dropdown of modelSelectorDropdowns) {
        if (dropdown.contains(target)) return;
    }
    
    showRegenerateMenu.value = false;
};

// Watch for menu close to update actions visibility
watch(showRegenerateMenu, (isOpen) => {
    if (isOpen) {
        // Keep actions visible when menu is open
        showActions.value = true;
    }
});

onMounted(() => {
    document.addEventListener('click', handleClickOutside, true);
});

// Cleanup on unmount
const cleanup = () => {
    document.removeEventListener('click', handleClickOutside, true);
};

onMounted(() => {
    return cleanup;
});

const formatTime = (dateStr: string): string => {
    const date = new Date(dateStr);
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(props.message.content);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (e) {
        console.error('Failed to copy:', e);
    }
};

// Configure marked with custom renderer
const renderer = new marked.Renderer();

// Custom code block renderer with syntax highlighting
renderer.code = ({ text, lang }: { text: string; lang?: string }) => {
    const validLanguage = lang && hljs.getLanguage(lang) ? lang : 'plaintext';
    const highlighted = validLanguage !== 'plaintext' 
        ? hljs.highlight(text, { language: validLanguage }).value 
        : text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    
    const lines = highlighted.split('\n');
    const lineNumbers = lines.map((_, i) => `<span class="line-number">${i + 1}</span>`).join('');
    const codeLines = lines.map(line => `<span class="code-line">${line || ' '}</span>`).join('\n');
    
    return `
        <div class="code-block-wrapper">
            <div class="code-block-header">
                <span class="code-language">${validLanguage}</span>
                <button class="copy-code-btn" onclick="copyCode(this)" title="Copy code">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>
            </div>
            <pre class="code-block"><code class="hljs language-${validLanguage}"><div class="line-numbers">${lineNumbers}</div><div class="code-content">${codeLines}</div></code></pre>
            <div class="code-data" style="display:none;">${text.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
        </div>
    `;
};

// Custom inline code renderer
renderer.codespan = ({ text }: { text: string }) => {
    return `<code class="inline-code">${text}</code>`;
};

// Custom link renderer (open in new tab)
renderer.link = ({ href, title, tokens }: { href: string; title?: string | null; tokens: any[] }) => {
    const text = renderer.parser.parseInline(tokens);
    const titleAttr = title ? ` title="${title}"` : '';
    return `<a href="${href}" target="_blank" rel="noopener noreferrer"${titleAttr} class="markdown-link">${text}</a>`;
};

// Custom blockquote renderer
renderer.blockquote = ({ tokens }: { tokens: any[] }) => {
    const body = renderer.parser.parse(tokens);
    return `<blockquote class="markdown-blockquote">${body}</blockquote>`;
};

// Custom list renderer
renderer.list = ({ ordered, items }: { ordered: boolean; items: any[] }) => {
    const tag = ordered ? 'ol' : 'ul';
    const body = items.map(item => `<li>${renderer.parser.parse(item.tokens)}</li>`).join('');
    return `<${tag} class="markdown-list ${ordered ? 'markdown-list-ordered' : 'markdown-list-unordered'}">${body}</${tag}>`;
};

// Custom heading renderer
renderer.heading = ({ tokens, depth }: { tokens: any[]; depth: number }) => {
    const text = renderer.parser.parseInline(tokens);
    return `<h${depth} class="markdown-heading markdown-h${depth}">${text}</h${depth}>`;
};

marked.setOptions({
    renderer: renderer,
    gfm: true,
    breaks: true,
});

// Format markdown content with alias linking
const formattedContent = computed(() => {
    if (isUser.value) {
        // For user messages, just escape HTML and preserve line breaks
        let content = props.message.content
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        
        // Apply alias linking to user messages too
        content = linkAliasesInText(content);
        
        return content.replace(/\n/g, '<br>');
    }
    
    // For assistant messages, parse markdown first
    try {
        let html = marked.parse(props.message.content) as string;
        
        // Apply alias linking to text nodes only (not inside code blocks or HTML tags)
        // We need to be careful not to link inside code blocks or existing links
        if (props.enableAliasLinking && sortedAliases.value.length > 0) {
            // Simple approach: link in text between tags
            // Split by HTML tags and process only text parts
            const parts = html.split(/(<[^>]+>)/g);
            let inCode = false;
            let inLink = false;
            
            html = parts.map(part => {
                // Check if we're entering/leaving code or link elements
                if (part.match(/<code[^>]*>/i) || part.match(/<pre[^>]*>/i)) {
                    inCode = true;
                } else if (part.match(/<\/code>/i) || part.match(/<\/pre>/i)) {
                    inCode = false;
                } else if (part.match(/<a[^>]*>/i)) {
                    inLink = true;
                } else if (part.match(/<\/a>/i)) {
                    inLink = false;
                }
                
                // If it's an HTML tag, return as-is
                if (part.startsWith('<')) {
                    return part;
                }
                
                // If we're inside code or link, return as-is
                if (inCode || inLink) {
                    return part;
                }
                
                // Otherwise, link aliases in this text part
                return linkAliasesInText(part);
            }).join('');
        }
        
        return html;
    } catch (e) {
        console.error('Markdown parsing error:', e);
        return props.message.content.replace(/\n/g, '<br>');
    }
});

// Extend window interface for copyCode function
declare global {
    interface Window {
        copyCode?: (btn: HTMLElement) => Promise<void>;
    }
}

// Add copy code function to window
onMounted(() => {
    if (!window.copyCode) {
        window.copyCode = async (btn: HTMLElement) => {
            const wrapper = btn.closest('.code-block-wrapper');
            const codeData = wrapper?.querySelector('.code-data')?.textContent;
            if (codeData) {
                try {
                    await navigator.clipboard.writeText(codeData);
                    const svg = btn.querySelector('svg');
                    if (svg) {
                        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />';
                        setTimeout(() => {
                            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />';
                        }, 2000);
                    }
                } catch (e) {
                    console.error('Failed to copy code:', e);
                }
            }
        };
    }
});
</script>

<template>
    <div 
        class="group flex gap-3" 
        :class="[isUser ? 'justify-end' : 'justify-start']"
        @mouseenter="showActions = true"
        @mouseleave="handleMouseLeave"
    >
        <!-- Avatar for assistant -->
        <div v-if="isAssistant" class="shrink-0">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-100 dark:bg-violet-900/30">
                <svg class="h-5 w-5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Message Content -->
        <div class="flex max-w-[85%] flex-col">
            <!-- Edit Mode for User Messages -->
            <div
                v-if="isUser && isEditing"
                class="relative rounded-2xl bg-white p-3 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-800 dark:ring-zinc-700"
            >
                <textarea
                    ref="editTextarea"
                    v-model="editContent"
                    rows="3"
                    class="w-full resize-none rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                    @keydown.enter.exact.prevent="submitEdit"
                    @keydown.esc="cancelEdit"
                ></textarea>
                <div class="mt-2 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 transition-all hover:bg-zinc-50 active:scale-95 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        @click="cancelEdit"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white transition-all hover:bg-violet-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="!editContent.trim()"
                        @click="submitEdit"
                    >
                        Send
                    </button>
                </div>
            </div>

            <!-- Normal Message Display -->
            <div
                v-else
                class="relative rounded-2xl px-4 py-3"
                :class="[
                    isUser
                        ? 'rounded-br-md bg-linear-to-br from-violet-600 to-violet-700 text-white shadow-md'
                        : 'rounded-bl-md bg-white text-zinc-900 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-800 dark:text-zinc-100 dark:ring-zinc-700',
                ]"
            >
                <!-- Message Text -->
                <div 
                    class="message-content max-w-none" 
                    :class="[isUser ? 'user-content' : 'assistant-content']"
                    v-html="formattedContent"
                ></div>
                
                <!-- Streaming cursor - typing animation -->
                <span v-if="isStreaming" class="typing-cursor ml-0.5 inline-block h-4 w-0.5 animate-blink bg-violet-500 align-middle dark:bg-violet-400"></span>

                <!-- Metadata -->
                <div
                    class="mt-2 flex items-center gap-2 text-xs"
                    :class="[isUser ? 'text-violet-200' : 'text-zinc-400 dark:text-zinc-500']"
                >
                    <span>{{ formatTime(message.created_at) }}</span>
                    <span v-if="message.model_used" class="hidden sm:inline">• {{ message.model_used }}</span>
                </div>
            </div>

            <!-- Action Bar (Assistant messages only) -->
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div 
                    v-if="isAssistant && !isStreaming && showActions" 
                    class="mt-1.5 flex items-center gap-1 pl-1"
                >
                    <!-- Copy Button -->
                    <button
                        type="button"
                        class="action-btn"
                        :class="[copied ? 'text-green-600 dark:text-green-400' : '']"
                        :title="copied ? 'Copied!' : 'Copy message'"
                        @click="copyToClipboard"
                    >
                        <svg v-if="copied" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span class="text-xs">{{ copied ? 'Copied' : 'Copy' }}</span>
                    </button>

                    <!-- Transfer/Insert Button -->
                    <button
                        type="button"
                        class="action-btn"
                        title="Insert to scene"
                        @click="handleTransfer"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span class="text-xs">Insert</span>
                    </button>

                    <!-- Extract Button -->
                    <button
                        type="button"
                        class="action-btn"
                        title="Extract to Codex"
                        @click="handleExtract"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs">Extract</span>
                    </button>

                    <!-- Regenerate Button Group -->
                    <div class="relative flex items-center">
                        <!-- Main Regenerate Button -->
                        <button
                            type="button"
                            class="action-btn-left"
                            title="Regenerate this response"
                            @click="handleRegenerate"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>
                            <span class="text-xs">Regenerate</span>
                        </button>
                        
                        <!-- Dropdown Trigger -->
                        <button
                            ref="regenerateButtonRef"
                            type="button"
                            class="action-btn-right"
                            title="Choose model"
                            @click.stop="toggleRegenerateMenu"
                        >
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Action Bar (User messages only) -->
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div 
                    v-if="isUser && !isEditing && showActions" 
                    class="mt-1.5 flex items-center justify-end gap-1 pr-1"
                >
                    <!-- Edit Button -->
                    <button
                        type="button"
                        class="action-btn"
                        title="Edit message"
                        @click="startEdit"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span class="text-xs">Edit</span>
                    </button>
                </div>
            </Transition>
        </div>

        <!-- Model Selection Menu (Teleported to body) -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div
                    v-if="showRegenerateMenu"
                    ref="regenerateMenuRef"
                    class="fixed z-[9998] w-72 rounded-lg border border-zinc-200 bg-white p-3 shadow-xl dark:border-zinc-700 dark:bg-zinc-800"
                    :style="menuPosition"
                >
                    <p class="mb-2 text-xs font-medium text-zinc-700 dark:text-zinc-300">Regenerate with different model</p>
                    <ModelSelector
                        :key="modelSelectorKey"
                        v-model="regenerateModel"
                        :connection-id="regenerateConnectionId"
                        placeholder="Select a model"
                        size="sm"
                        @update:connection-id="regenerateConnectionId = $event"
                    />
                    <div class="mt-2 flex gap-2">
                        <button
                            type="button"
                            class="flex-1 rounded-md border border-zinc-300 bg-white px-2 py-1.5 text-xs font-medium text-zinc-700 transition-all hover:bg-zinc-50 active:scale-95 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600"
                            @click="showRegenerateMenu = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="flex-1 rounded-md bg-violet-600 px-2 py-1.5 text-xs font-medium text-white transition-all hover:bg-violet-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!regenerateModel || !regenerateConnectionId"
                            @click="handleRegenerateWithModel"
                        >
                            Regenerate
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Avatar for user -->
        <div v-if="isUser" class="shrink-0">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-700">
                <svg class="h-5 w-5 text-zinc-600 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes blink {
    0%, 50% {
        opacity: 1;
    }
    51%, 100% {
        opacity: 0;
    }
}

.animate-blink {
    animation: blink 0.8s infinite;
}

/* Message Content Base Styles */
.message-content {
    font-size: 0.9375rem;
    line-height: 1.6;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.user-content {
    color: white;
}

.assistant-content {
    color: rgb(24 24 27);
}

:deep(.dark) .assistant-content {
    color: rgb(250 250 250);
}

/* Markdown Typography */
.message-content :deep(p) {
    margin: 0.75em 0;
}

.message-content :deep(p:first-child) {
    margin-top: 0;
}

.message-content :deep(p:last-child) {
    margin-bottom: 0;
}

/* Headings */
.message-content :deep(.markdown-heading) {
    font-weight: 600;
    line-height: 1.3;
    margin: 1.25em 0 0.5em;
}

.message-content :deep(.markdown-h1) {
    font-size: 1.75em;
    border-bottom: 2px solid rgb(228 228 231);
    padding-bottom: 0.3em;
}

:deep(.dark) .message-content :deep(.markdown-h1) {
    border-bottom-color: rgb(63 63 70);
}

.message-content :deep(.markdown-h2) {
    font-size: 1.5em;
    border-bottom: 1px solid rgb(228 228 231);
    padding-bottom: 0.3em;
}

:deep(.dark) .message-content :deep(.markdown-h2) {
    border-bottom-color: rgb(63 63 70);
}

.message-content :deep(.markdown-h3) {
    font-size: 1.25em;
}

.message-content :deep(.markdown-h4) {
    font-size: 1.1em;
}

.message-content :deep(.markdown-h5) {
    font-size: 1em;
}

.message-content :deep(.markdown-h6) {
    font-size: 0.9em;
    color: rgb(113 113 122);
}

/* Lists */
.message-content :deep(.markdown-list) {
    margin: 0.75em 0;
    padding-left: 2em;
}

.message-content :deep(.markdown-list-unordered) {
    list-style-type: disc;
}

.message-content :deep(.markdown-list-ordered) {
    list-style-type: decimal;
}

.message-content :deep(.markdown-list li) {
    margin: 0.25em 0;
    padding-left: 0.5em;
}

.message-content :deep(.markdown-list li > p) {
    margin: 0.5em 0;
}

.message-content :deep(.markdown-list ul),
.message-content :deep(.markdown-list ol) {
    margin: 0.5em 0;
}

/* Blockquotes */
.message-content :deep(.markdown-blockquote) {
    margin: 1em 0;
    padding: 0.75em 1em;
    border-left: 4px solid rgb(139 92 246);
    background: rgb(245 243 255);
    border-radius: 0 0.5rem 0.5rem 0;
}

:deep(.dark) .message-content :deep(.markdown-blockquote) {
    background: rgb(46 16 101 / 0.3);
    border-left-color: rgb(167 139 250);
}

.message-content :deep(.markdown-blockquote p) {
    margin: 0.5em 0;
}

.message-content :deep(.markdown-blockquote p:first-child) {
    margin-top: 0;
}

.message-content :deep(.markdown-blockquote p:last-child) {
    margin-bottom: 0;
}

/* Inline Code */
.message-content :deep(.inline-code) {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro', monospace;
    font-size: 0.875em;
    padding: 0.2em 0.4em;
    background: rgb(244 244 245);
    border: 1px solid rgb(228 228 231);
    border-radius: 0.375rem;
    color: rgb(219 39 119);
}

:deep(.dark) .message-content :deep(.inline-code) {
    background: rgb(39 39 42);
    border-color: rgb(63 63 70);
    color: rgb(251 113 133);
}

/* Links */
.message-content :deep(.markdown-link) {
    color: rgb(124 58 237);
    text-decoration: underline;
    text-decoration-color: rgb(124 58 237 / 0.3);
    text-underline-offset: 2px;
    transition: all 0.2s;
}

.message-content :deep(.markdown-link:hover) {
    text-decoration-color: rgb(124 58 237);
    color: rgb(109 40 217);
}

:deep(.dark) .message-content :deep(.markdown-link) {
    color: rgb(167 139 250);
    text-decoration-color: rgb(167 139 250 / 0.3);
}

:deep(.dark) .message-content :deep(.markdown-link:hover) {
    text-decoration-color: rgb(167 139 250);
    color: rgb(196 181 253);
}

/* Code Blocks */
.message-content :deep(.code-block-wrapper) {
    margin: 1em 0;
    border-radius: 0.75rem;
    overflow: hidden;
    background: rgb(24 24 27);
    border: 1px solid rgb(39 39 42);
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.message-content :deep(.code-block-header) {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 1rem;
    background: rgb(39 39 42);
    border-bottom: 1px solid rgb(63 63 70);
}

.message-content :deep(.code-language) {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: rgb(161 161 170);
    letter-spacing: 0.05em;
}

.message-content :deep(.copy-code-btn) {
    padding: 0.25rem;
    color: rgb(161 161 170);
    border-radius: 0.375rem;
    transition: all 0.2s;
    background: transparent;
    border: none;
    cursor: pointer;
}

.message-content :deep(.copy-code-btn:hover) {
    color: rgb(244 244 245);
    background: rgb(63 63 70);
    transform: scale(1.1);
}

.message-content :deep(.copy-code-btn:active) {
    transform: scale(0.95);
}

.message-content :deep(.code-block) {
    margin: 0;
    padding: 0;
    overflow-x: auto;
    background: rgb(24 24 27);
}

.message-content :deep(.code-block code) {
    display: flex;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro', monospace;
    font-size: 0.875rem;
    line-height: 1.6;
    color: rgb(228 228 231);
    padding: 1rem 0;
}

.message-content :deep(.line-numbers) {
    display: flex;
    flex-direction: column;
    padding: 0 1rem;
    text-align: right;
    user-select: none;
    border-right: 1px solid rgb(63 63 70);
    color: rgb(113 113 122);
    min-width: 3rem;
}

.message-content :deep(.line-number) {
    line-height: 1.6;
}

.message-content :deep(.code-content) {
    flex: 1;
    padding: 0 1rem;
    overflow-x: auto;
}

.message-content :deep(.code-line) {
    display: block;
    line-height: 1.6;
}

/* Horizontal Rule */
.message-content :deep(hr) {
    margin: 1.5em 0;
    border: none;
    border-top: 1px solid rgb(228 228 231);
}

:deep(.dark) .message-content :deep(hr) {
    border-top-color: rgb(63 63 70);
}

/* Tables */
.message-content :deep(table) {
    width: 100%;
    margin: 1em 0;
    border-collapse: collapse;
    overflow: hidden;
    border-radius: 0.5rem;
    border: 1px solid rgb(228 228 231);
}

:deep(.dark) .message-content :deep(table) {
    border-color: rgb(63 63 70);
}

.message-content :deep(thead) {
    background: rgb(244 244 245);
}

:deep(.dark) .message-content :deep(thead) {
    background: rgb(39 39 42);
}

.message-content :deep(th) {
    padding: 0.75rem 1rem;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid rgb(228 228 231);
}

:deep(.dark) .message-content :deep(th) {
    border-bottom-color: rgb(63 63 70);
}

.message-content :deep(td) {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgb(228 228 231);
}

:deep(.dark) .message-content :deep(td) {
    border-bottom-color: rgb(63 63 70);
}

.message-content :deep(tr:last-child td) {
    border-bottom: none;
}

.message-content :deep(tbody tr:hover) {
    background: rgb(250 250 250);
}

:deep(.dark) .message-content :deep(tbody tr:hover) {
    background: rgb(39 39 42 / 0.5);
}

/* Strong & Emphasis */
.message-content :deep(strong) {
    font-weight: 600;
}

.message-content :deep(em) {
    font-style: italic;
}

/* Images */
.message-content :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1em 0;
}

/* Action Buttons */
.action-btn {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.625rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: rgb(113 113 122);
    background: rgb(250 250 250);
    border: 1px solid rgb(228 228 231);
    border-radius: 0.5rem;
    transition: all 0.15s ease;
    cursor: pointer;
}

.action-btn:hover {
    color: rgb(82 82 91);
    background: rgb(244 244 245);
    border-color: rgb(212 212 216);
    transform: scale(1.02);
}

.action-btn:active {
    transform: scale(0.97);
}

:deep(.dark) .action-btn {
    color: rgb(161 161 170);
    background: rgb(39 39 42);
    border-color: rgb(63 63 70);
}

:deep(.dark) .action-btn:hover {
    color: rgb(212 212 216);
    background: rgb(52 52 56);
    border-color: rgb(82 82 91);
}

/* Split Action Buttons */
.action-btn-left {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.625rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: rgb(113 113 122);
    background: rgb(250 250 250);
    border: 1px solid rgb(228 228 231);
    border-radius: 0.5rem 0 0 0.5rem;
    border-right: none;
    transition: all 0.15s ease;
    cursor: pointer;
}

.action-btn-left:hover {
    color: rgb(82 82 91);
    background: rgb(244 244 245);
    border-color: rgb(212 212 216);
}

.action-btn-left:active {
    transform: scale(0.97);
}

.action-btn-right {
    display: flex;
    align-items: center;
    padding: 0.375rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: rgb(113 113 122);
    background: rgb(250 250 250);
    border: 1px solid rgb(228 228 231);
    border-radius: 0 0.5rem 0.5rem 0;
    transition: all 0.15s ease;
    cursor: pointer;
}

.action-btn-right:hover {
    color: rgb(82 82 91);
    background: rgb(244 244 245);
    border-color: rgb(212 212 216);
}

.action-btn-right:active {
    transform: scale(0.97);
}

:deep(.dark) .action-btn-left,
:deep(.dark) .action-btn-right {
    color: rgb(161 161 170);
    background: rgb(39 39 42);
    border-color: rgb(63 63 70);
}

:deep(.dark) .action-btn-left:hover,
:deep(.dark) .action-btn-right:hover {
    color: rgb(212 212 216);
    background: rgb(52 52 56);
    border-color: rgb(82 82 91);
}

:deep(.dark) .action-btn-left {
    border-right: none;
}

/* Codex Alias Links */
.message-content :deep(.codex-alias-link) {
    display: inline;
    padding: 0.1em 0.4em;
    border-radius: 0.375rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s ease;
    cursor: pointer;
}

.message-content :deep(.codex-alias-link:hover) {
    filter: brightness(0.95);
    transform: scale(1.02);
}

/* Character - Blue */
.message-content :deep(.codex-link-character) {
    background: rgb(219 234 254 / 0.7);
    color: rgb(29 78 216);
    border: 1px solid rgb(147 197 253 / 0.5);
}

:deep(.dark) .message-content :deep(.codex-link-character) {
    background: rgb(30 58 138 / 0.3);
    color: rgb(147 197 253);
    border-color: rgb(59 130 246 / 0.3);
}

/* Location - Green */
.message-content :deep(.codex-link-location) {
    background: rgb(220 252 231 / 0.7);
    color: rgb(21 128 61);
    border: 1px solid rgb(134 239 172 / 0.5);
}

:deep(.dark) .message-content :deep(.codex-link-location) {
    background: rgb(20 83 45 / 0.3);
    color: rgb(134 239 172);
    border-color: rgb(34 197 94 / 0.3);
}

/* Item - Amber */
.message-content :deep(.codex-link-item) {
    background: rgb(254 243 199 / 0.7);
    color: rgb(180 83 9);
    border: 1px solid rgb(252 211 77 / 0.5);
}

:deep(.dark) .message-content :deep(.codex-link-item) {
    background: rgb(120 53 15 / 0.3);
    color: rgb(252 211 77);
    border-color: rgb(245 158 11 / 0.3);
}

/* Event - Purple */
.message-content :deep(.codex-link-event) {
    background: rgb(243 232 255 / 0.7);
    color: rgb(126 34 206);
    border: 1px solid rgb(216 180 254 / 0.5);
}

:deep(.dark) .message-content :deep(.codex-link-event) {
    background: rgb(88 28 135 / 0.3);
    color: rgb(216 180 254);
    border-color: rgb(168 85 247 / 0.3);
}

/* Concept - Pink */
.message-content :deep(.codex-link-concept) {
    background: rgb(252 231 243 / 0.7);
    color: rgb(190 24 93);
    border: 1px solid rgb(249 168 212 / 0.5);
}

:deep(.dark) .message-content :deep(.codex-link-concept) {
    background: rgb(131 24 67 / 0.3);
    color: rgb(249 168 212);
    border-color: rgb(236 72 153 / 0.3);
}

/* Faction - Red */
.message-content :deep(.codex-link-faction) {
    background: rgb(254 226 226 / 0.7);
    color: rgb(185 28 28);
    border: 1px solid rgb(252 165 165 / 0.5);
}

:deep(.dark) .message-content :deep(.codex-link-faction) {
    background: rgb(127 29 29 / 0.3);
    color: rgb(252 165 165);
    border-color: rgb(239 68 68 / 0.3);
}

/* Species - Teal */
.message-content :deep(.codex-link-species) {
    background: rgb(204 251 241 / 0.7);
    color: rgb(15 118 110);
    border: 1px solid rgb(94 234 212 / 0.5);
}

:deep(.dark) .message-content :deep(.codex-link-species) {
    background: rgb(19 78 74 / 0.3);
    color: rgb(94 234 212);
    border-color: rgb(20 184 166 / 0.3);
}

/* Other - Zinc */
.message-content :deep(.codex-link-other) {
    background: rgb(244 244 245 / 0.7);
    color: rgb(63 63 70);
    border: 1px solid rgb(212 212 216 / 0.5);
}

:deep(.dark) .message-content :deep(.codex-link-other) {
    background: rgb(63 63 70 / 0.3);
    color: rgb(212 212 216);
    border-color: rgb(113 113 122 / 0.3);
}

/* User message codex links (lighter variant for dark background) */
.user-content :deep(.codex-alias-link) {
    background: rgb(255 255 255 / 0.2);
    color: white;
    border-color: rgb(255 255 255 / 0.3);
}

.user-content :deep(.codex-alias-link:hover) {
    background: rgb(255 255 255 / 0.3);
}
</style>
