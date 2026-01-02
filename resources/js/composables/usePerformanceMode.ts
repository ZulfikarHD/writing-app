import { ref, computed, watch, onMounted } from 'vue';

export type PerformanceMode = 'full' | 'reduced';

export interface PerformanceSettings {
    mode: PerformanceMode;
}

const STORAGE_KEY = 'novelwrite-performance-settings';

const defaultSettings: PerformanceSettings = {
    mode: 'full', // Default to full animations
};

// Shared reactive state (singleton pattern)
const settings = ref<PerformanceSettings>({ ...defaultSettings });
let isInitialized = false;

function loadSettings(): PerformanceSettings {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            const parsed = JSON.parse(stored) as Partial<PerformanceSettings>;
            return {
                mode: parsed.mode || defaultSettings.mode,
            };
        }
    } catch {
        // Ignore parse errors
    }
    return { ...defaultSettings };
}

function saveSettings(newSettings: PerformanceSettings) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(newSettings));
    } catch {
        // Ignore storage errors
    }
}

export function usePerformanceMode() {
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

    // Computed flags for easy conditional logic
    const isReducedMotion = computed(() => settings.value.mode === 'reduced');
    const isFullAnimation = computed(() => settings.value.mode === 'full');

    // Animation configs based on mode
    const springConfig = computed(() => {
        if (isReducedMotion.value) {
            // Faster, simpler transitions for reduced mode
            return {
                type: 'tween' as const,
                duration: 0.15,
                ease: 'easeOut' as const,
            };
        }
        return {
            type: 'spring' as const,
            stiffness: 400,
            damping: 30,
        };
    });

    const sidebarSpringConfig = computed(() => {
        if (isReducedMotion.value) {
            return {
                type: 'tween' as const,
                duration: 0.2,
                ease: 'easeOut' as const,
            };
        }
        return {
            type: 'spring' as const,
            stiffness: 400,
            damping: 40,
        };
    });

    // CSS classes based on mode
    const transitionClass = computed(() =>
        isReducedMotion.value ? 'transition-opacity duration-200' : 'transition-all'
    );

    const buttonTransitionClass = computed(() =>
        isReducedMotion.value ? 'transition-colors duration-150' : 'transition-all'
    );

    const backdropBlurClass = computed(() =>
        isReducedMotion.value ? '' : 'backdrop-blur-sm'
    );

    const scaleActiveClass = computed(() =>
        isReducedMotion.value ? '' : 'active:scale-95'
    );

    // Setters
    const setMode = (mode: PerformanceMode) => {
        settings.value.mode = mode;
    };

    const toggleMode = () => {
        settings.value.mode = settings.value.mode === 'full' ? 'reduced' : 'full';
    };

    return {
        settings,
        isReducedMotion,
        isFullAnimation,
        springConfig,
        sidebarSpringConfig,
        transitionClass,
        buttonTransitionClass,
        backdropBlurClass,
        scaleActiveClass,
        setMode,
        toggleMode,
    };
}
