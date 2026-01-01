<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import CodexTypeBadge from '../shared/CodexTypeBadge.vue';
import CodexTypeIcon from '../shared/CodexTypeIcon.vue';

interface Category {
    id: number;
    name: string;
    color: string | null;
}

interface CodexEntry {
    id: number;
    type: string;
    name: string;
    description: string | null;
    thumbnail_path: string | null;
    ai_context_mode: string;
    aliases: string[];
    categories?: Category[];
}

withDefaults(
    defineProps<{
        entry: CodexEntry;
        showType?: boolean;
        showAliases?: boolean;
        compact?: boolean;
    }>(),
    {
        showType: true,
        showAliases: true,
        compact: false,
    },
);

const typeColors: Record<string, string> = {
    character: 'bg-purple-100 dark:bg-purple-900/30',
    location: 'bg-blue-100 dark:bg-blue-900/30',
    item: 'bg-amber-100 dark:bg-amber-900/30',
    lore: 'bg-emerald-100 dark:bg-emerald-900/30',
    organization: 'bg-rose-100 dark:bg-rose-900/30',
    subplot: 'bg-cyan-100 dark:bg-cyan-900/30',
};

const getTypeColor = (type: string) => typeColors[type] || 'bg-zinc-100 dark:bg-zinc-800';

const truncateDescription = (text: string | null, length: number = 100) => {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length).trim() + '...';
};
</script>

<template>
    <Link
        :href="`/codex/${entry.id}`"
        class="group block rounded-lg border border-zinc-200 bg-white transition-all hover:border-violet-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-violet-600"
        :class="compact ? 'p-3' : 'p-4'"
    >
        <div class="flex items-start gap-3">
            <!-- Thumbnail / Icon -->
            <div
                class="flex shrink-0 items-center justify-center rounded-lg"
                :class="[getTypeColor(entry.type), compact ? 'h-10 w-10' : 'h-12 w-12']"
            >
                <CodexTypeIcon :type="entry.type" :size="compact ? 'md' : 'lg'" />
            </div>

            <div class="min-w-0 flex-1">
                <!-- Header -->
                <div class="flex items-start justify-between gap-2">
                    <h3
                        class="truncate font-medium text-zinc-900 group-hover:text-violet-600 dark:text-white dark:group-hover:text-violet-400"
                        :class="compact ? 'text-sm' : 'text-base'"
                    >
                        {{ entry.name }}
                    </h3>
                    <CodexTypeBadge v-if="showType" :type="entry.type" size="sm" :show-icon="false" />
                </div>

                <!-- Description -->
                <p v-if="!compact && entry.description" class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ truncateDescription(entry.description) }}
                </p>

                <!-- Aliases -->
                <div v-if="showAliases && entry.aliases.length > 0 && !compact" class="mt-2 flex flex-wrap gap-1">
                    <span
                        v-for="alias in entry.aliases.slice(0, 3)"
                        :key="alias"
                        class="inline-flex rounded bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400"
                    >
                        {{ alias }}
                    </span>
                    <span v-if="entry.aliases.length > 3" class="text-xs text-zinc-400"> +{{ entry.aliases.length - 3 }} more </span>
                </div>

                <!-- Categories -->
                <div v-if="entry.categories && entry.categories.length > 0 && !compact" class="mt-2 flex flex-wrap gap-1">
                    <span
                        v-for="cat in entry.categories.slice(0, 2)"
                        :key="cat.id"
                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                        :style="cat.color ? { backgroundColor: cat.color + '20', color: cat.color } : {}"
                        :class="!cat.color ? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400' : ''"
                    >
                        {{ cat.name }}
                    </span>
                </div>
            </div>
        </div>
    </Link>
</template>
