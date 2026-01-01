import { ref, computed, watch, onMounted } from 'vue';

export type FontFamily = 'serif' | 'sans' | 'mono' | 'dyslexic';
export type EditorWidth = 'narrow' | 'medium' | 'wide';

export interface EditorSettings {
    fontFamily: FontFamily;
    fontSize: number;
    lineHeight: number;
    editorWidth: EditorWidth;
}

const STORAGE_KEY = 'novelwrite-editor-settings';

const defaultSettings: EditorSettings = {
    fontFamily: 'serif',
    fontSize: 18,
    lineHeight: 1.8,
    editorWidth: 'medium',
};

// Font family CSS mappings
const fontFamilyMap: Record<FontFamily, string> = {
    serif: "'Georgia', 'Times New Roman', serif",
    sans: "'Inter', 'Segoe UI', system-ui, sans-serif",
    mono: "'JetBrains Mono', 'Fira Code', monospace",
    dyslexic: "'OpenDyslexic', 'Comic Sans MS', sans-serif",
};

// Font family display names
export const fontFamilyOptions: { value: FontFamily; label: string }[] = [
    { value: 'serif', label: 'Serif (Georgia)' },
    { value: 'sans', label: 'Sans-serif (Inter)' },
    { value: 'mono', label: 'Monospace' },
    { value: 'dyslexic', label: 'Dyslexia-friendly' },
];

// Font size options
export const fontSizeOptions = [14, 16, 18, 20, 22, 24];

// Line height options
export const lineHeightOptions = [1.5, 1.6, 1.8, 2.0];

// Editor width options
export const editorWidthOptions: { value: EditorWidth; label: string }[] = [
    { value: 'narrow', label: 'Narrow' },
    { value: 'medium', label: 'Medium' },
    { value: 'wide', label: 'Wide' },
];

// Shared reactive state (singleton pattern)
const settings = ref<EditorSettings>({ ...defaultSettings });
let isInitialized = false;

function loadSettings(): EditorSettings {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            const parsed = JSON.parse(stored) as Partial<EditorSettings>;
            return {
                fontFamily: parsed.fontFamily || defaultSettings.fontFamily,
                fontSize: parsed.fontSize || defaultSettings.fontSize,
                lineHeight: parsed.lineHeight || defaultSettings.lineHeight,
                editorWidth: parsed.editorWidth || defaultSettings.editorWidth,
            };
        }
    } catch {
        // Ignore parse errors
    }
    return { ...defaultSettings };
}

function saveSettings(newSettings: EditorSettings) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(newSettings));
    } catch {
        // Ignore storage errors
    }
}

export function useEditorSettings() {
    onMounted(() => {
        if (!isInitialized) {
            settings.value = loadSettings();
            isInitialized = true;
        }
    });

    // Watch for changes and persist
    watch(
        settings,
        (newSettings) => {
            saveSettings(newSettings);
        },
        { deep: true }
    );

    // Computed CSS styles for the editor
    const editorStyles = computed(() => ({
        '--editor-font-family': fontFamilyMap[settings.value.fontFamily],
        '--editor-font-size': `${settings.value.fontSize}px`,
        '--editor-line-height': String(settings.value.lineHeight),
    }));

    // Setters
    const setFontFamily = (family: FontFamily) => {
        settings.value.fontFamily = family;
    };

    const setFontSize = (size: number) => {
        settings.value.fontSize = size;
    };

    const setLineHeight = (height: number) => {
        settings.value.lineHeight = height;
    };

    const setEditorWidth = (width: EditorWidth) => {
        settings.value.editorWidth = width;
    };

    const resetToDefaults = () => {
        settings.value = { ...defaultSettings };
    };

    return {
        settings,
        editorStyles,
        setFontFamily,
        setFontSize,
        setLineHeight,
        setEditorWidth,
        resetToDefaults,
        // Export options for UI
        fontFamilyOptions,
        fontSizeOptions,
        lineHeightOptions,
        editorWidthOptions,
    };
}
