<script setup lang="ts">
import { Motion } from 'motion-v';
import { ref, watch, onUnmounted, nextTick } from 'vue';

interface MenuItem {
    label: string;
    icon?: string;
    action: () => void;
    variant?: 'default' | 'danger';
    divider?: boolean;
}

interface Props {
    items: MenuItem[];
    position?: { x: number; y: number };
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
}>();

const menuRef = ref<HTMLElement | null>(null);
const adjustedPosition = ref({ x: 0, y: 0 });

// Adjust position to keep menu in viewport
const adjustPosition = () => {
    if (!props.position || !menuRef.value) return;

    const menu = menuRef.value;
    const menuRect = menu.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    let x = props.position.x;
    let y = props.position.y;

    // Adjust horizontal position
    if (x + menuRect.width > viewportWidth - 8) {
        x = viewportWidth - menuRect.width - 8;
    }
    if (x < 8) x = 8;

    // Adjust vertical position
    if (y + menuRect.height > viewportHeight - 8) {
        y = viewportHeight - menuRect.height - 8;
    }
    if (y < 8) y = 8;

    adjustedPosition.value = { x, y };
};

watch(
    () => props.position,
    async () => {
        if (props.position) {
            adjustedPosition.value = { x: props.position.x, y: props.position.y };
            await nextTick();
            adjustPosition();
        }
    },
    { immediate: true }
);

const handleClickOutside = (e: MouseEvent) => {
    if (menuRef.value && !menuRef.value.contains(e.target as Node)) {
        emit('close');
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        emit('close');
    }
};

watch(
    () => props.position,
    (newPos) => {
        if (newPos) {
            document.addEventListener('click', handleClickOutside);
            document.addEventListener('keydown', handleKeydown);
        } else {
            document.removeEventListener('click', handleClickOutside);
            document.removeEventListener('keydown', handleKeydown);
        }
    },
    { immediate: true }
);

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeydown);
});

const handleItemClick = (item: MenuItem) => {
    item.action();
    emit('close');
};
</script>

<template>
    <Teleport to="body">
        <Motion
            v-if="position"
            ref="menuRef"
            :initial="{ opacity: 0, scale: 0.95 }"
            :animate="{ opacity: 1, scale: 1 }"
            :exit="{ opacity: 0, scale: 0.95 }"
            :transition="{ type: 'spring', stiffness: 500, damping: 35, duration: 0.15 }"
            :style="{ left: `${adjustedPosition.x}px`, top: `${adjustedPosition.y}px` }"
            class="fixed z-50 min-w-40 origin-top-left rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
        >
                <template v-for="(item, index) in items" :key="index">
                    <div v-if="item.divider" class="my-1 border-t border-zinc-200 dark:border-zinc-700" />
                    <button
                        v-else
                        type="button"
                        :class="[
                            'flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition-colors',
                            item.variant === 'danger'
                                ? 'text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20'
                                : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700',
                        ]"
                        @click="handleItemClick(item)"
                    >
                        <span v-if="item.icon" class="text-base" v-html="item.icon" />
                        {{ item.label }}
                    </button>
                </template>
        </Motion>
    </Teleport>
</template>
