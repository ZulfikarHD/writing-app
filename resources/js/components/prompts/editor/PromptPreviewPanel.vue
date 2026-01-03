<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import Button from '@/components/ui/buttons/Button.vue';
import Input from '@/components/ui/forms/Input.vue';
import type { PromptMessage } from './TabInstructions.vue';
import { useComponents } from '@/composables/useComponents';

export interface PromptInputDef {
    id: string | number;
    name: string;
    label: string;
    type: 'text' | 'textarea' | 'select' | 'number' | 'checkbox';
    options?: { value: string; label: string }[];
    default_value?: string;
    placeholder?: string;
    description?: string;
    is_required: boolean;
    sort_order: number;
}

interface Props {
    systemMessage: string;
    userMessage: string;
    messages: PromptMessage[];
    promptType: 'chat' | 'prose' | 'replacement' | 'summary';
    inputs?: PromptInputDef[];
}

const props = withDefaults(defineProps<Props>(), {
    inputs: () => [],
});

// Components composable
const { components, fetchComponents } = useComponents();

// Fetch components on mount
onMounted(async () => {
    await fetchComponents();
});

const showResolved = ref(false);
const copied = ref(false);
const showInputPanel = ref(false);

// Test values for inputs
const inputTestValues = ref<Record<string, string>>({});

// Initialize input test values when inputs change
watch(
    () => props.inputs,
    (inputs) => {
        const values: Record<string, string> = {};
        for (const input of inputs) {
            // Keep existing value if present, otherwise use default
            values[input.name] = inputTestValues.value[input.name] ?? input.default_value ?? '';
        }
        inputTestValues.value = values;
    },
    { immediate: true, deep: true }
);

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

// Build a map of component content by name
const componentMap = computed(() => {
    const map: Record<string, string> = {};
    for (const comp of components.value) {
        map[comp.name] = comp.content;
    }
    return map;
});

// Estimate token count (rough approximation: 1 token ~= 4 characters)
const tokenCount = computed(() => {
    const text = `${props.systemMessage}\n${props.userMessage}\n${props.messages.map((m) => m.content).join('\n')}`;
    return Math.ceil(text.length / 4);
});

// Resolve variables in text
function resolveVariables(text: string): string {
    if (!text) return '';

    let resolved = text;

    // Replace {include("name")} patterns with component content
    resolved = resolved.replace(/\{include\(["']([^"']+)["']\)\}/g, (match, compName) => {
        const content = componentMap.value[compName];
        if (content) {
            // Recursively resolve variables in the component content
            const resolvedContent = resolveVariables(content);
            return `<span class="component-resolved" title="Component: ${compName}">${resolvedContent}</span>`;
        }
        return `<span class="component-unresolved" title="Component not found: ${compName}">${match}</span>`;
    });

    // Replace [[component]] patterns (legacy syntax)
    resolved = resolved.replace(/\[\[([a-zA-Z_][a-zA-Z0-9_]*)\]\]/g, (match, compName) => {
        const content = componentMap.value[compName];
        if (content) {
            const resolvedContent = resolveVariables(content);
            return `<span class="component-resolved" title="Component: ${compName}">${resolvedContent}</span>`;
        }
        return `<span class="component-unresolved" title="Component not found: ${compName}">${match}</span>`;
    });

    // Replace {input("name")} patterns with test values
    resolved = resolved.replace(/\{input\(["']([^"']+)["']\)\}/g, (match, inputName) => {
        const value = inputTestValues.value[inputName];
        if (value !== undefined && value !== '') {
            return `<span class="input-resolved" title="Input: ${inputName}">${escapeHtml(value)}</span>`;
        }
        // Check if this is a known input
        const knownInput = props.inputs.find((i) => i.name === inputName);
        if (knownInput) {
            return `<span class="input-placeholder" title="Input: ${inputName}">[${knownInput.label || inputName}]</span>`;
        }
        return `<span class="input-unresolved" title="Unknown input: ${inputName}">${match}</span>`;
    });

    // Replace {variable} patterns with sample data (simple variables without quotes)
    resolved = resolved.replace(/\{([a-zA-Z_][a-zA-Z0-9_.]*)\}/g, (match, varName) => {
        // Skip if it looks like a function call we already handled
        if (varName === 'include' || varName === 'input') {
            return match;
        }
        const value = sampleContext[varName as keyof typeof sampleContext];
        if (value) {
            return `<span class="variable-resolved" title="Variable: ${varName}">${value}</span>`;
        }
        return `<span class="variable-unresolved" title="Unknown variable: ${varName}">${match}</span>`;
    });

    return resolved;
}

// Escape HTML to prevent XSS when showing user input
function escapeHtml(text: string): string {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
                <button
                    v-if="inputs.length > 0"
                    type="button"
                    class="flex items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-medium transition-colors"
                    :class="[
                        showInputPanel
                            ? 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300'
                            : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700',
                    ]"
                    @click="showInputPanel = !showInputPanel"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Test Inputs ({{ inputs.length }})
                </button>
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

        <!-- Test Input Panel -->
        <div
            v-if="showInputPanel && inputs.length > 0"
            class="rounded-lg border border-pink-200 bg-pink-50 p-3 dark:border-pink-800 dark:bg-pink-950/30"
        >
            <div class="mb-2 flex items-center gap-2">
                <svg class="h-4 w-4 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span class="text-xs font-medium text-pink-700 dark:text-pink-300">Test Input Values</span>
            </div>
            <div class="grid gap-2 sm:grid-cols-2">
                <div v-for="input in inputs" :key="input.id">
                    <label class="mb-0.5 block text-[10px] font-medium text-pink-700 dark:text-pink-400">
                        {{ input.label || input.name }}
                    </label>
                    <Input
                        v-model="inputTestValues[input.name]"
                        :placeholder="input.placeholder || `Enter ${input.label || input.name}...`"
                        size="sm"
                        class="bg-white text-xs dark:bg-zinc-800"
                    />
                </div>
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
                    <span class="text-zinc-600 dark:text-zinc-400">Component (Resolved)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded bg-pink-200 dark:bg-pink-800"></span>
                    <span class="text-zinc-600 dark:text-zinc-400">Input Value</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded bg-zinc-300 dark:bg-zinc-600"></span>
                    <span class="text-zinc-600 dark:text-zinc-400">Input Placeholder</span>
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

:deep(.component-unresolved) {
    border-radius: 0.25rem;
    background-color: rgb(254 226 226);
    padding: 0.125rem 0.25rem;
    color: rgb(185 28 28);
}

:deep(.input-resolved) {
    border-radius: 0.25rem;
    background-color: rgb(252 231 243);
    padding: 0.125rem 0.25rem;
    color: rgb(157 23 77);
}

:deep(.input-placeholder) {
    border-radius: 0.25rem;
    background-color: rgb(228 228 231);
    padding: 0.125rem 0.25rem;
    color: rgb(82 82 91);
    font-style: italic;
}

:deep(.input-unresolved) {
    border-radius: 0.25rem;
    background-color: rgb(254 243 199);
    padding: 0.125rem 0.25rem;
    color: rgb(146 64 14);
}

.dark :deep(.variable-resolved) {
    background-color: rgba(91, 33, 182, 0.5);
    color: rgb(221 214 254);
}

.dark :deep(.variable-unresolved) {
    background-color: rgba(146, 64, 14, 0.5);
    color: rgb(253 230 138);
}

.dark :deep(.component-resolved) {
    background-color: rgba(14, 116, 144, 0.5);
    color: rgb(165 243 252);
}

.dark :deep(.component-unresolved) {
    background-color: rgba(185, 28, 28, 0.5);
    color: rgb(254 202 202);
}

.dark :deep(.input-resolved) {
    background-color: rgba(157, 23, 77, 0.5);
    color: rgb(251 207 232);
}

.dark :deep(.input-placeholder) {
    background-color: rgba(82, 82, 91, 0.5);
    color: rgb(212 212 216);
}

.dark :deep(.input-unresolved) {
    background-color: rgba(146, 64, 14, 0.5);
    color: rgb(253 230 138);
}
</style>
