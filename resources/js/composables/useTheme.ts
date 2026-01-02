import { ref, watch, onMounted } from 'vue';

export type Theme = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'novelwrite-theme';
const theme = ref<Theme>('system');
const isDark = ref(false);
let isInitialized = false;
let systemThemeListener: ((e: MediaQueryListEvent) => void) | null = null;

function getSystemTheme(): boolean {
    if (typeof window === 'undefined') return false;
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function applyTheme(dark: boolean) {
    isDark.value = dark;
    if (typeof document !== 'undefined') {
        document.documentElement.classList.toggle('dark', dark);
    }
}

function updateTheme() {
    const shouldBeDark = theme.value === 'system' ? getSystemTheme() : theme.value === 'dark';
    applyTheme(shouldBeDark);
}

function initializeTheme() {
    if (typeof window === 'undefined' || isInitialized) return;

    // Load stored preference
    const stored = localStorage.getItem(STORAGE_KEY) as Theme | null;
    if (stored && ['light', 'dark', 'system'].includes(stored)) {
        theme.value = stored;
    }

    // Apply theme
    updateTheme();

    // Listen for system theme changes
    if (!systemThemeListener) {
        systemThemeListener = () => {
            if (theme.value === 'system') {
                updateTheme();
            }
        };
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', systemThemeListener);
    }

    isInitialized = true;
}

// Try to initialize immediately for client-side hydration
if (typeof window !== 'undefined') {
    initializeTheme();
}

export function useTheme() {
    onMounted(() => {
        // Ensure theme is initialized when component mounts
        initializeTheme();
    });

    // Watch for theme changes and persist + apply
    watch(theme, (newTheme) => {
        if (typeof window === 'undefined') return;
        localStorage.setItem(STORAGE_KEY, newTheme);
        updateTheme();
    });

    const setTheme = (t: Theme) => {
        theme.value = t;
    };

    const toggleTheme = () => {
        theme.value = isDark.value ? 'light' : 'dark';
    };

    return { theme, isDark, setTheme, toggleTheme };
}
