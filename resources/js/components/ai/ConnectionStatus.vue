<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    status: 'success' | 'failed' | 'pending';
    lastTestedAt?: string | null;
    size?: 'sm' | 'md';
}>();

const statusConfig = computed(() => {
    switch (props.status) {
        case 'success':
            return {
                label: 'Connected',
                bgColor: 'bg-emerald-100 dark:bg-emerald-900/30',
                textColor: 'text-emerald-700 dark:text-emerald-400',
                dotColor: 'bg-emerald-500',
            };
        case 'failed':
            return {
                label: 'Failed',
                bgColor: 'bg-red-100 dark:bg-red-900/30',
                textColor: 'text-red-700 dark:text-red-400',
                dotColor: 'bg-red-500',
            };
        default:
            return {
                label: 'Not tested',
                bgColor: 'bg-zinc-100 dark:bg-zinc-800',
                textColor: 'text-zinc-600 dark:text-zinc-400',
                dotColor: 'bg-zinc-400 dark:bg-zinc-500',
            };
    }
});

const sizeClasses = computed(() => {
    return props.size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-2.5 py-1 text-xs';
});

const dotSizeClasses = computed(() => {
    return props.size === 'sm' ? 'h-1.5 w-1.5' : 'h-2 w-2';
});

const formattedDate = computed(() => {
    if (!props.lastTestedAt) return null;
    const date = new Date(props.lastTestedAt);
    return date.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});
</script>

<template>
    <div class="flex items-center gap-2">
        <span
            :class="[statusConfig.bgColor, statusConfig.textColor, sizeClasses]"
            class="inline-flex items-center gap-1.5 rounded-full font-medium"
        >
            <span :class="[statusConfig.dotColor, dotSizeClasses]" class="rounded-full"></span>
            {{ statusConfig.label }}
        </span>
        <span v-if="formattedDate && status !== 'pending'" class="text-xs text-zinc-400 dark:text-zinc-500">
            {{ formattedDate }}
        </span>
    </div>
</template>
