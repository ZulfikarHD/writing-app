<script setup lang="ts">
import axios from 'axios';
import { ref, watch } from 'vue';

const props = defineProps<{
    entryId: number;
    isTrackingEnabled: boolean;
}>();

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

const enabled = ref(props.isTrackingEnabled);
const updating = ref(false);

// Watch for external changes
watch(() => props.isTrackingEnabled, (newVal) => {
    enabled.value = newVal;
});

const toggleTracking = async () => {
    updating.value = true;
    const newValue = !enabled.value;
    
    try {
        await axios.patch(`/api/codex/${props.entryId}`, {
            is_tracking_enabled: newValue,
        });
        
        enabled.value = newValue;
        emit('updated');
    } catch (error) {
        console.error('Failed to update tracking status:', error);
    } finally {
        updating.value = false;
    }
};
</script>

<template>
    <div class="flex items-start gap-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <h4 class="text-sm font-medium text-zinc-900 dark:text-white">Track Mentions</h4>
                <span
                    v-if="!enabled"
                    class="rounded-full bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
                >
                    Disabled
                </span>
            </div>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                <template v-if="enabled">
                    This entry's name and aliases will be detected and highlighted in your scenes.
                </template>
                <template v-else>
                    This entry won't be highlighted or tracked in mentions. Useful for common words like "Red" or "The Council".
                </template>
            </p>
        </div>
        
        <button
            type="button"
            role="switch"
            :aria-checked="enabled"
            :disabled="updating"
            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-zinc-900"
            :class="enabled ? 'bg-violet-600' : 'bg-zinc-200 dark:bg-zinc-600'"
            @click="toggleTracking"
        >
            <span class="sr-only">{{ enabled ? 'Disable' : 'Enable' }} mention tracking</span>
            <span
                aria-hidden="true"
                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                :class="enabled ? 'translate-x-5' : 'translate-x-0'"
            >
                <!-- Loading indicator -->
                <svg
                    v-if="updating"
                    class="absolute inset-0 m-auto h-3 w-3 animate-spin text-zinc-400"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
            </span>
        </button>
    </div>
</template>
