<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import { ref } from 'vue';

const props = defineProps<{
    novelId: number;
    disabled?: boolean;
}>();

const showDropdown = ref(false);
const exporting = ref(false);

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const closeDropdown = () => {
    showDropdown.value = false;
};

const exportJson = () => {
    exporting.value = true;
    window.location.href = `/api/novels/${props.novelId}/codex/export/json`;
    setTimeout(() => {
        exporting.value = false;
        closeDropdown();
    }, 1000);
};

const exportCsv = () => {
    exporting.value = true;
    window.location.href = `/api/novels/${props.novelId}/codex/export/csv`;
    setTimeout(() => {
        exporting.value = false;
        closeDropdown();
    }, 1000);
};

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.export-dropdown')) {
        closeDropdown();
    }
};

// Add and remove click listener
import { onMounted, onBeforeUnmount } from 'vue';

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="export-dropdown relative">
        <Button
            variant="ghost"
            size="sm"
            :disabled="disabled || exporting"
            @click.stop="toggleDropdown"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <span v-if="exporting">Exporting...</span>
            <span v-else>Export</span>
            <svg class="h-3 w-3 transition-transform" :class="{ 'rotate-180': showDropdown }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </Button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-if="showDropdown"
                class="absolute right-0 top-full z-10 mt-1 w-48 origin-top-right rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
            >
                <button
                    type="button"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    @click="exportJson"
                >
                    <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <div>
                        <p class="font-medium">Export as JSON</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Full data with structure</p>
                    </div>
                </button>
                <button
                    type="button"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    @click="exportCsv"
                >
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div>
                        <p class="font-medium">Export as CSV</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Spreadsheet compatible</p>
                    </div>
                </button>
            </div>
        </Transition>
    </div>
</template>
