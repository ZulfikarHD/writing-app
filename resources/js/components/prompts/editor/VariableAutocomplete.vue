<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useComponents, type PromptComponent } from '@/composables/useComponents';

export interface VariableDefinition {
    name: string;
    description: string;
    category: string;
    example?: string;
    parameters?: { name: string; type: string; required: boolean }[];
    insertText?: string; // Custom text to insert instead of default formatting
}

export interface PromptInputDef {
    id: number | string;
    name: string;
    label: string;
    type: string;
}

interface Props {
    modelValue: string;
    disabled?: boolean;
    placeholder?: string;
    rows?: number;
    promptInputs?: PromptInputDef[]; // Available inputs for current prompt
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    placeholder: 'Enter text...',
    rows: 4,
    promptInputs: () => [],
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

// Component composable
const { components, fetchComponents } = useComponents();

const textareaRef = ref<HTMLTextAreaElement | null>(null);
const showAutocomplete = ref(false);
const autocompletePosition = ref({ top: 0, left: 0 });
const searchQuery = ref('');
const selectedIndex = ref(0);
const triggerPosition = ref(0);

// Fetch components on mount
onMounted(async () => {
    await fetchComponents();
});

// Base variable registry - all available functions based on NovelCrafter reference
const baseVariableRegistry: VariableDefinition[] = [
    // Acts
    { name: 'act', description: 'Current act information', category: 'Acts' },
    { name: 'act.fullText', description: 'Full text of the current act', category: 'Acts' },
    { name: 'act.name', description: 'Name of the current act', category: 'Acts' },
    { name: 'act.summary', description: 'Summary of the current act', category: 'Acts' },

    // Chapters
    { name: 'chapter', description: 'Current chapter information', category: 'Chapters' },
    { name: 'chapter.fullText', description: 'Full text of the current chapter', category: 'Chapters' },
    { name: 'chapter.name', description: 'Name of the current chapter', category: 'Chapters' },
    { name: 'chapter.summary', description: 'Summary of the current chapter', category: 'Chapters' },

    // Scenes
    { name: 'scene', description: 'Current scene information', category: 'Scenes' },
    { name: 'scene.title', description: 'Title of the current scene', category: 'Scenes' },
    { name: 'scene.fullText', description: 'Full text of the current scene', category: 'Scenes' },
    { name: 'scene.summary', description: 'Summary of the current scene', category: 'Scenes' },
    { name: 'scene.labels', description: 'Labels assigned to the scene', category: 'Scenes' },
    { name: 'scene.next', description: 'Next scene', category: 'Scenes' },
    { name: 'scene.previous', description: 'Previous scene', category: 'Scenes' },

    // Codex
    { name: 'codex.characters', description: 'Character codex entries', category: 'Codex' },
    { name: 'codex.locations', description: 'Location codex entries', category: 'Codex' },
    { name: 'codex.lore', description: 'Lore/world-building entries', category: 'Codex' },
    { name: 'codex.objects', description: 'Object codex entries', category: 'Codex' },
    { name: 'codex.context', description: 'Relevant codex context for scene', category: 'Codex' },
    { name: 'codex.mentions', description: 'Codex items mentioned in text', category: 'Codex' },
    { name: 'codex.get', description: 'Get specific codex entry by name', category: 'Codex', parameters: [{ name: 'name', type: 'string', required: true }] },

    // Context
    { name: 'textBefore', description: 'Text before cursor position', category: 'Context' },
    { name: 'textAfter', description: 'Text after cursor position', category: 'Context' },
    { name: 'storySoFar', description: 'Story content up to this point', category: 'Context' },
    { name: 'storyToCome', description: 'Story content after this point', category: 'Context' },
    { name: 'message', description: 'User message input', category: 'Context' },
    { name: 'content', description: 'Selected/target content', category: 'Context' },

    // Composition (base functions - components and inputs added dynamically)
    { name: 'include', description: 'Include a component', category: 'Composition', parameters: [{ name: 'component', type: 'string', required: true }] },
    { name: 'input', description: 'Get prompt input value', category: 'Composition', parameters: [{ name: 'name', type: 'string', required: true }] },

    // Novel
    { name: 'novel.title', description: 'Title of the novel', category: 'Novel' },
    { name: 'novel.author', description: 'Author name', category: 'Novel' },
    { name: 'novel.outline', description: 'Novel outline', category: 'Novel' },
    { name: 'novel.tense', description: 'Writing tense (past/present)', category: 'Novel' },
    { name: 'novel.language', description: 'Writing language', category: 'Novel' },

    // POV
    { name: 'pov', description: 'Point of view information', category: 'POV' },
    { name: 'pov.character', description: 'POV character', category: 'POV' },
    { name: 'pov.type', description: 'POV type (first/third person)', category: 'POV' },

    // Text manipulation
    { name: 'wordCount', description: 'Get word count', category: 'Text', parameters: [{ name: 'text', type: 'text', required: true }] },
    { name: 'firstWords', description: 'Get first N words', category: 'Text', parameters: [{ name: 'text', type: 'text', required: true }, { name: 'count', type: 'number', required: true }] },
    { name: 'lastWords', description: 'Get last N words', category: 'Text', parameters: [{ name: 'text', type: 'text', required: true }, { name: 'count', type: 'number', required: true }] },

    // Logic
    { name: 'ifs', description: 'Conditional content', category: 'Logic', parameters: [{ name: 'condition', type: 'boolean', required: true }, { name: 'then', type: 'text', required: true }, { name: 'else', type: 'text', required: false }] },
    { name: 'isEmpty', description: 'Check if value is empty', category: 'Logic', parameters: [{ name: 'value', type: 'any', required: true }] },

    // Personas
    { name: 'personas', description: 'Active personas instructions', category: 'Other' },
    { name: 'date.today', description: 'Current date', category: 'Other' },
];

// Generate dynamic component entries for autocomplete
const componentVariables = computed<VariableDefinition[]>(() => {
    return components.value.map((component: PromptComponent) => ({
        name: `include("${component.name}")`,
        description: component.description || component.label,
        category: 'Components',
        insertText: `include("${component.name}")`,
    }));
});

// Generate dynamic input entries for autocomplete (from prompt inputs prop)
const inputVariables = computed<VariableDefinition[]>(() => {
    return props.promptInputs.map((input) => ({
        name: `input("${input.name}")`,
        description: input.label,
        category: 'Inputs',
        insertText: `input("${input.name}")`,
    }));
});

// Combined variable registry with dynamic components and inputs
const variableRegistry = computed<VariableDefinition[]>(() => {
    return [
        ...baseVariableRegistry,
        ...componentVariables.value,
        ...inputVariables.value,
    ];
});

// Filter variables based on search query
const filteredVariables = computed(() => {
    const registry = variableRegistry.value;
    if (!searchQuery.value) {
        return registry;
    }
    const query = searchQuery.value.toLowerCase();
    return registry.filter(
        (v) =>
            v.name.toLowerCase().includes(query) ||
            v.description.toLowerCase().includes(query) ||
            v.category.toLowerCase().includes(query),
    );
});

// Group filtered variables by category
const groupedVariables = computed(() => {
    const groups: Record<string, VariableDefinition[]> = {};
    for (const variable of filteredVariables.value) {
        if (!groups[variable.category]) {
            groups[variable.category] = [];
        }
        groups[variable.category].push(variable);
    }
    return groups;
});

// Flat list for keyboard navigation
const flatVariableList = computed(() => filteredVariables.value);

// Handle input
function handleInput(e: Event) {
    const target = e.target as HTMLTextAreaElement;
    emit('update:modelValue', target.value);
    checkForTrigger(target);
}

// Check if we should show autocomplete
function checkForTrigger(textarea: HTMLTextAreaElement) {
    const cursorPos = textarea.selectionStart;
    const text = textarea.value;

    // Look for '{' character before cursor
    let triggerIdx = -1;
    for (let i = cursorPos - 1; i >= 0; i--) {
        if (text[i] === '{') {
            triggerIdx = i;
            break;
        }
        if (text[i] === '}' || text[i] === '\n') {
            break;
        }
    }

    if (triggerIdx !== -1) {
        triggerPosition.value = triggerIdx;
        searchQuery.value = text.substring(triggerIdx + 1, cursorPos);
        showAutocomplete.value = true;
        selectedIndex.value = 0;
        updateAutocompletePosition(textarea, triggerIdx);
    } else {
        showAutocomplete.value = false;
    }
}

// Calculate autocomplete dropdown position
function updateAutocompletePosition(textarea: HTMLTextAreaElement, triggerIdx: number) {
    // Create a temporary element to measure text position
    const mirror = document.createElement('div');
    mirror.style.cssText = window.getComputedStyle(textarea).cssText;
    mirror.style.height = 'auto';
    mirror.style.position = 'absolute';
    mirror.style.visibility = 'hidden';
    mirror.style.whiteSpace = 'pre-wrap';
    mirror.style.wordWrap = 'break-word';

    const textBeforeCursor = textarea.value.substring(0, triggerIdx);
    mirror.textContent = textBeforeCursor;

    document.body.appendChild(mirror);

    const rect = textarea.getBoundingClientRect();
    const lineHeight = parseInt(window.getComputedStyle(textarea).lineHeight) || 20;

    // Approximate position
    const lines = textBeforeCursor.split('\n');
    const currentLine = lines.length;
    const top = rect.top + (currentLine * lineHeight) - textarea.scrollTop + lineHeight;
    const left = rect.left + 10;

    document.body.removeChild(mirror);

    autocompletePosition.value = { top, left };
}

// Handle keyboard navigation
function handleKeyDown(e: KeyboardEvent) {
    if (!showAutocomplete.value) return;

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            selectedIndex.value = Math.min(selectedIndex.value + 1, flatVariableList.value.length - 1);
            break;
        case 'ArrowUp':
            e.preventDefault();
            selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
            break;
        case 'Enter':
        case 'Tab':
            if (flatVariableList.value.length > 0) {
                e.preventDefault();
                selectVariable(flatVariableList.value[selectedIndex.value]);
            }
            break;
        case 'Escape':
            e.preventDefault();
            showAutocomplete.value = false;
            break;
    }
}

// Format variable display
function formatVariableDisplay(variable: VariableDefinition): string {
    // For variables with custom insertText, just show the name as-is
    if (variable.insertText) {
        return '{' + variable.insertText + '}';
    }
    const suffix = variable.parameters ? '(...)' : '';
    return '{' + variable.name + suffix + '}';
}

// Select a variable
function selectVariable(variable: VariableDefinition) {
    if (!textareaRef.value) return;

    const text = props.modelValue;
    const cursorPos = textareaRef.value.selectionStart;

    // Build the variable string - use custom insertText if available
    let variableStr: string;
    if (variable.insertText) {
        variableStr = variable.insertText;
    } else {
        variableStr = variable.name;
        if (variable.parameters && variable.parameters.length > 0) {
            const params = variable.parameters.map((p) => p.required ? `"${p.name}"` : '').filter(Boolean);
            variableStr += `(${params.join(', ')})`;
        }
    }

    // Replace from trigger position to cursor with the variable
    const newText = text.substring(0, triggerPosition.value) + `{${variableStr}}` + text.substring(cursorPos);
    emit('update:modelValue', newText);

    showAutocomplete.value = false;

    // Set cursor position after the inserted variable
    const newCursorPos = triggerPosition.value + variableStr.length + 2;
    requestAnimationFrame(() => {
        if (textareaRef.value) {
            textareaRef.value.focus();
            textareaRef.value.setSelectionRange(newCursorPos, newCursorPos);
        }
    });
}

// Close autocomplete when clicking outside
function handleClickOutside(e: MouseEvent) {
    const target = e.target as HTMLElement;
    if (!target.closest('.autocomplete-container')) {
        showAutocomplete.value = false;
    }
}

onMounted(async () => {
    document.addEventListener('click', handleClickOutside);
    // Components already fetched at top of script
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="autocomplete-container relative">
        <textarea
            ref="textareaRef"
            :value="modelValue"
            :disabled="disabled"
            :rows="rows"
            :placeholder="placeholder"
            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 font-mono text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
            @input="handleInput"
            @keydown="handleKeyDown"
        ></textarea>

        <!-- Autocomplete Dropdown -->
        <Teleport to="body">
            <div
                v-if="showAutocomplete && flatVariableList.length > 0"
                class="fixed z-50 max-h-64 w-72 overflow-y-auto rounded-lg border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-800"
                :style="{ top: `${autocompletePosition.top}px`, left: `${autocompletePosition.left}px` }"
            >
                <!-- Search indicator -->
                <div class="sticky top-0 border-b border-zinc-200 bg-zinc-50 px-3 py-2 text-xs text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                    <span v-if="searchQuery">Searching: <code class="rounded bg-zinc-200 px-1 dark:bg-zinc-700">{{ searchQuery }}</code></span>
                    <span v-else>Type to search variables</span>
                </div>

                <!-- Grouped variables -->
                <div v-for="(variables, category) in groupedVariables" :key="category">
                    <div class="sticky top-8 bg-zinc-100 px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400">
                        {{ category }}
                    </div>
                    <button
                        v-for="variable in variables"
                        :key="variable.name"
                        type="button"
                        class="block w-full px-3 py-2 text-left transition-colors"
                        :class="[
                            flatVariableList.indexOf(variable) === selectedIndex
                                ? 'bg-violet-100 dark:bg-violet-900/30'
                                : 'hover:bg-zinc-100 dark:hover:bg-zinc-700',
                        ]"
                        @click="selectVariable(variable)"
                        @mouseenter="selectedIndex = flatVariableList.indexOf(variable)"
                    >
                        <div class="font-mono text-sm text-violet-600 dark:text-violet-400">
                            {{ formatVariableDisplay(variable) }}
                        </div>
                        <div class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ variable.description }}
                        </div>
                    </button>
                </div>
            </div>
        </Teleport>
    </div>
</template>
