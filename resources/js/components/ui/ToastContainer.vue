<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import Toast from './Toast.vue';

const { toasts, remove } = useToast();
</script>

<template>
    <div class="pointer-events-none fixed inset-0 z-50">
        <TransitionGroup
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
            move-class="transition-all duration-300"
        >
            <Toast
                v-for="toast in toasts"
                :key="toast.id"
                :variant="toast.variant"
                :title="toast.title"
                :duration="toast.duration"
                :position="toast.position"
                :dismissible="toast.dismissible"
                @close="remove(toast.id)"
            >
                {{ toast.message }}
            </Toast>
        </TransitionGroup>
    </div>
</template>
