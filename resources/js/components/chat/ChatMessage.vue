<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { marked } from 'marked';
import hljs from 'highlight.js/lib/core';
import 'highlight.js/styles/github-dark.css';

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

const props = withDefaults(
    defineProps<{
        message: Message;
        isStreaming?: boolean;
    }>(),
    {
        isStreaming: false,
    }
);

const copied = ref(false);

const isUser = computed(() => props.message.role === 'user');
const isAssistant = computed(() => props.message.role === 'assistant');

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

// Format markdown content
const formattedContent = computed(() => {
    if (isUser.value) {
        // For user messages, just escape HTML and preserve line breaks
        return props.message.content
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>');
    }
    
    // For assistant messages, parse markdown
    try {
        return marked.parse(props.message.content);
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
    <div class="group flex gap-3" :class="[isUser ? 'justify-end' : 'justify-start']">
        <!-- Avatar for assistant -->
        <div v-if="isAssistant" class="shrink-0">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-100 dark:bg-violet-900/30">
                <svg class="h-5 w-5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Message Content -->
        <div
            class="relative max-w-[85%] rounded-2xl px-4 py-3"
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

            <!-- Copy Button (Assistant only) -->
            <button
                v-if="isAssistant && !isStreaming"
                type="button"
                class="absolute -right-2 -top-2 rounded-full bg-white p-1.5 opacity-0 shadow-md ring-1 ring-zinc-200 transition-all hover:scale-110 group-hover:opacity-100 active:scale-95 dark:bg-zinc-700 dark:ring-zinc-600"
                :class="[copied ? 'text-green-600' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200']"
                :title="copied ? 'Copied!' : 'Copy'"
                @click="copyToClipboard"
            >
                <svg v-if="copied" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                    />
                </svg>
            </button>
        </div>

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
</style>
