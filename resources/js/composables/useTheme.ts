import { ref, watch, onMounted } from 'vue';

export type Theme = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'novelwrite-theme';
const theme = ref<Theme>('system');
const isDark = ref(false);

function getSystemTheme(): boolean {
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function applyTheme(dark: boolean) {
    isDark.value = dark;
    document.documentElement.classList.toggle('dark', dark);
}

function updateTheme() {
    applyTheme(theme.value === 'system' ? getSystemTheme() : theme.value === 'dark');
}

export function useTheme() {
    onMounted(() => {
        const stored = localStorage.getItem(STORAGE_KEY) as Theme | null;
        if (stored && ['light', 'dark', 'system'].includes(stored)) theme.value = stored;
        updateTheme();
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (theme.value === 'system') updateTheme();
        });
    });

    watch(theme, (newTheme) => { localStorage.setItem(STORAGE_KEY, newTheme); updateTheme(); });

    const setTheme = (t: Theme) => { theme.value = t; };
    const toggleTheme = () => { theme.value = isDark.value ? 'light' : 'dark'; };

    return { theme, isDark, setTheme, toggleTheme };
}
