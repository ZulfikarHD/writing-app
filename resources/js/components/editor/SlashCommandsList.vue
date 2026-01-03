<script setup lang="ts">
import { ref, watch } from 'vue';
import type { SlashCommand } from '@/extensions/SlashCommands';
import { SECTION_TYPE_COLORS } from '@/extensions/SectionNode';

interface Props {
    items: SlashCommand[];
    command: (item: SlashCommand) => void;
}

const props = defineProps<Props>();

const selectedIndex = ref(0);

watch(
    () => props.items,
    () => {
        selectedIndex.value = 0;
    }
);

const selectItem = (index: number) => {
    const item = props.items[index];
    if (item) {
        props.command(item);
    }
};

const onKeyDown = ({ event }: { event: KeyboardEvent }) => {
    if (event.key === 'ArrowUp') {
        selectedIndex.value = (selectedIndex.value + props.items.length - 1) % props.items.length;
        return true;
    }

    if (event.key === 'ArrowDown') {
        selectedIndex.value = (selectedIndex.value + 1) % props.items.length;
        return true;
    }

    if (event.key === 'Enter') {
        selectItem(selectedIndex.value);
        return true;
    }

    return false;
};

const getIconComponent = (icon: string) => {
    const icons: Record<string, string> = {
        // AI icons
        'ai-beat': `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>`,
        'ai-continue': `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>`,
        'ai-custom': `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>`,
        // Section icons
        section: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" /><line x1="3" y1="9" x2="21" y2="9" /></svg>`,
        note: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>`,
        alternative: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>`,
        beat: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>`,
        // Formatting icons
        h1: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><text x="4" y="17" font-size="14" font-weight="bold" fill="currentColor">H1</text></svg>`,
        h2: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><text x="4" y="17" font-size="14" font-weight="bold" fill="currentColor">H2</text></svg>`,
        h3: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><text x="4" y="17" font-size="14" font-weight="bold" fill="currentColor">H3</text></svg>`,
        // Block icons
        list: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>`,
        numbered: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>`,
        quote: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>`,
        divider: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12" /></svg>`,
    };
    return icons[icon] || icons.section;
};

const getIconColor = (icon: string) => {
    const colorMap: Record<string, string> = {
        // AI commands - violet
        'ai-beat': '#8b5cf6',
        'ai-continue': '#8b5cf6',
        'ai-custom': '#8b5cf6',
        // Section types
        section: SECTION_TYPE_COLORS.content,
        note: SECTION_TYPE_COLORS.note,
        alternative: SECTION_TYPE_COLORS.alternative,
        beat: SECTION_TYPE_COLORS.beat,
    };
    return colorMap[icon] || '#71717a';
};

defineExpose({
    onKeyDown,
});
</script>

<template>
    <div class="slash-commands-list bg-white dark:bg-zinc-800 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 overflow-hidden w-72 max-h-80 overflow-y-auto">
        <div v-if="items.length === 0" class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
            No commands found
        </div>
        <button
            v-for="(item, index) in items"
            :key="item.title"
            type="button"
            class="w-full flex items-center gap-3 px-4 py-2 text-left transition-colors"
            :class="{
                'bg-zinc-100 dark:bg-zinc-700': index === selectedIndex,
                'hover:bg-zinc-50 dark:hover:bg-zinc-700/50': index !== selectedIndex,
            }"
            @click="selectItem(index)"
            @mouseenter="selectedIndex = index"
        >
            <div
                class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded"
                :style="{ color: getIconColor(item.icon), backgroundColor: `${getIconColor(item.icon)}20` }"
                v-html="getIconComponent(item.icon)"
            ></div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ item.title }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ item.description }}</p>
            </div>
        </button>
    </div>
</template>

<style scoped>
.slash-commands-list {
    animation: slideIn 0.1s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
