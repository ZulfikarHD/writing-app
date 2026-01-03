<script setup lang="ts">
import { ref, computed } from 'vue';
import Button from '@/components/ui/buttons/Button.vue';
import type { PromptMessage } from './TabInstructions.vue';

interface Props {
    systemMessage: string;
    userMessage: string;
    messages: PromptMessage[];
    promptType: 'chat' | 'prose' | 'replacement' | 'summary';
}

const props = defineProps<Props>();

const showResolved = ref(false);
const copied = ref(false);

// Sample context data for preview
const sampleContext = {
    'scene.title': 'Chapter 1 - The Beginning',
    'scene.summary': 'The protagonist wakes up to discover their world has changed overnight.',
    'scene.fullText': 'The morning light filtered through the dusty curtains, casting long shadows across the bedroom floor. Sarah stirred, her mind foggy from sleep...',
    'codex.characters': '**Sarah**: Female, 28, protagonist - a journalist investigating mysterious events\n**Marcus**: Male, 35, detective - Sarah\'s reluctant ally',
    'codex.locations': '**Downtown Apartment**: Sarah\'s modest one-bedroom apartment in the city center',
    'codex.context': 'Sarah is a journalist who has been investigating a series of disappearances.',
    'textBefore': 'She reached for her phone on the nightstand.',
    'textAfter': 'The notification light was blinking urgently.',
    'storySoFar': 'Sarah had been investigating the disappearances for three weeks now...',
    'storyToCome': 'She would soon discover that nothing was as it seemed.',
    'message': '[User message will appear here]',
    'content': '[Selected text will appear here]',
    'nextBeat': 'Sarah discovers a cryptic message on her phone.',
    'previousBeat': 'Sarah went to bed after a long day of research.',
    'novel.title': 'The Vanishing',
    'novel.author': 'Jane Doe',
    'pov': 'Third Person Limited',
    'pov.character': 'Sarah',
    'personas': '[Active persona instructions]',
    'date.today': new Date().toLocaleDateString(),
};

// Estimate token count (rough approximation: 1 token ~= 4 characters)
const tokenCount = computed(() => {
    const text = `${props.systemMessage}\n${props.userMessage}\n${props.messages.map((m) => m.content).join('\n')}`;
    return Math.ceil(text.length / 4);
});

// Resolve variables in text
function resolveVariables(text: string): string {
    if (!text) return '';

    let resolved = text;

    // Replace {variable} patterns with sample data
    resolved = resolved.replace(/\{([a-zA-Z_.]+)(?:\([^)]*\))?\}/g, (match, varName) => {
        const value = sampleContext[varName as keyof typeof sampleContext];
        if (value) {
            return `<span class="variable-resolved">${value}</span>`;
        }
        return `<span class="variable-unresolved">${match}</span>`;
    });

    // Replace [[component]] patterns
    resolved = resolved.replace(/\[\[([a-zA-Z_][a-zA-Z0-9_]*)\]\]/g, (match, compName) => {
        return `<span class="component-resolved">[Component: ${compName}]</span>`;
    });

    return resolved;
}

// Format message role label
function getRoleLabel(role: 'user' | 'assistant'): string {
    return role === 'user' ? 'User' : 'AI';
}

// Copy to clipboard
async function copyToClipboard() {
    const text = buildFullPrompt();
    try {
        await navigator.clipboard.writeText(text);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
}

// Build full prompt text for copying
function buildFullPrompt(): string {
    const parts: string[] = [];

    if (props.systemMessage) {
        parts.push(`[SYSTEM]\n${props.systemMessage}`);
    }

    if (props.userMessage) {
        parts.push(`[USER]\n${props.userMessage}`);
    }

    for (const msg of props.messages) {
        const roleLabel = msg.role === 'user' ? 'USER' : 'ASSISTANT';
        parts.push(`[${roleLabel}]\n${msg.content}`);
    }

    return parts.join('\n\n');
}
</script>

<template>
    <div class="space-y-4">
        <!-- Preview Controls -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-sm">
                    <input
                        v-model="showResolved"
                        type="checkbox"
                        class="h-4 w-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500"
                    />
                    <span class="text-zinc-700 dark:text-zinc-300">Show with sample data</span>
                </label>
            </div>

            <div class="flex items-center gap-3">
                <!-- Token Count -->
                <div class="flex items-center gap-1.5 rounded-lg bg-zinc-100 px-3 py-1.5 dark:bg-zinc-800">
                    <svg class="h-4 w-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">~{{ tokenCount }} tokens</span>
                </div>

                <!-- Copy Button -->
                <Button size="sm" variant="ghost" @click="copyToClipboard">
                    <svg v-if="!copied" class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <svg v-else class="mr-1 h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ copied ? 'Copied!' : 'Copy' }}
                </Button>
            </div>
        </div>

        <!-- Preview Content -->
        <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <!-- System Message Preview -->
            <div v-if="systemMessage" class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                <div class="mb-2 flex items-center gap-2">
                    <span class="rounded bg-zinc-200 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                        SYSTEM
                    </span>
                </div>
                <div
                    v-if="showResolved"
                    class="prose prose-sm max-w-none font-mono text-sm text-zinc-800 dark:prose-invert dark:text-zinc-200"
                    v-html="resolveVariables(systemMessage)"
                ></div>
                <pre v-else class="whitespace-pre-wrap font-mono text-sm text-zinc-800 dark:text-zinc-200">{{ systemMessage }}</pre>
            </div>

            <!-- User Message Preview -->
            <div v-if="userMessage" class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                <div class="mb-2 flex items-center gap-2">
                    <span class="rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                        USER
                    </span>
                </div>
                <div
                    v-if="showResolved"
                    class="prose prose-sm max-w-none font-mono text-sm text-zinc-800 dark:prose-invert dark:text-zinc-200"
                    v-html="resolveVariables(userMessage)"
                ></div>
                <pre v-else class="whitespace-pre-wrap font-mono text-sm text-zinc-800 dark:text-zinc-200">{{ userMessage }}</pre>
            </div>

            <!-- Additional Messages Preview -->
            <div
                v-for="msg in messages"
                :key="msg.id"
                class="border-b border-zinc-200 p-4 last:border-b-0 dark:border-zinc-700"
            >
                <div class="mb-2 flex items-center gap-2">
                    <span
                        class="rounded px-2 py-0.5 text-xs font-medium"
                        :class="[
                            msg.role === 'user'
                                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                                : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300',
                        ]"
                    >
                        {{ getRoleLabel(msg.role) }}
                    </span>
                </div>
                <div
                    v-if="showResolved"
                    class="prose prose-sm max-w-none font-mono text-sm text-zinc-800 dark:prose-invert dark:text-zinc-200"
                    v-html="resolveVariables(msg.content)"
                ></div>
                <pre v-else class="whitespace-pre-wrap font-mono text-sm text-zinc-800 dark:text-zinc-200">{{ msg.content }}</pre>
            </div>

            <!-- Empty State -->
            <div
                v-if="!systemMessage && !userMessage && messages.length === 0"
                class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400"
            >
                No content to preview. Add a system message or user message template.
            </div>
        </div>

        <!-- Variable Legend -->
        <div v-if="showResolved" class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="mb-2 text-xs font-medium text-zinc-500 dark:text-zinc-400">Legend</div>
            <div class="flex flex-wrap gap-3 text-xs">
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded bg-violet-200 dark:bg-violet-800"></span>
                    <span class="text-zinc-600 dark:text-zinc-400">Resolved Variable</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded bg-amber-200 dark:bg-amber-800"></span>
                    <span class="text-zinc-600 dark:text-zinc-400">Unresolved Variable</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded bg-cyan-200 dark:bg-cyan-800"></span>
                    <span class="text-zinc-600 dark:text-zinc-400">Component Reference</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.variable-resolved) {
    border-radius: 0.25rem;
    background-color: rgb(237 233 254);
    padding: 0.125rem 0.25rem;
    color: rgb(91 33 182);
}

:deep(.variable-unresolved) {
    border-radius: 0.25rem;
    background-color: rgb(254 243 199);
    padding: 0.125rem 0.25rem;
    color: rgb(146 64 14);
}

:deep(.component-resolved) {
    border-radius: 0.25rem;
    background-color: rgb(207 250 254);
    padding: 0.125rem 0.25rem;
    color: rgb(14 116 144);
}

@media (prefers-color-scheme: dark) {
    :deep(.variable-resolved) {
        background-color: rgba(91, 33, 182, 0.5);
        color: rgb(221 214 254);
    }

    :deep(.variable-unresolved) {
        background-color: rgba(146, 64, 14, 0.5);
        color: rgb(253 230 138);
    }

    :deep(.component-resolved) {
        background-color: rgba(14, 116, 144, 0.5);
        color: rgb(165 243 252);
    }
}
</style>
