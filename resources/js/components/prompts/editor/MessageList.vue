<script setup lang="ts">
import { ref } from 'vue';
import MessageItem from './MessageItem.vue';
import Button from '@/components/ui/buttons/Button.vue';

export interface PromptMessage {
    id: string;
    role: 'user' | 'assistant';
    content: string;
}

interface Variable {
    name: string;
    description: string;
}

interface Props {
    messages: PromptMessage[];
    isEditable: boolean;
    availableVariables: Variable[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:messages': [value: PromptMessage[]];
}>();

const draggedIndex = ref<number | null>(null);

function generateId(): string {
    return `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
}

function addMessage() {
    const newMessage: PromptMessage = {
        id: generateId(),
        role: 'user',
        content: '',
    };
    emit('update:messages', [...props.messages, newMessage]);
}

function updateMessage(id: string, updates: Partial<PromptMessage>) {
    const updated = props.messages.map((msg) =>
        msg.id === id ? { ...msg, ...updates } : msg,
    );
    emit('update:messages', updated);
}

function removeMessage(id: string) {
    emit('update:messages', props.messages.filter((msg) => msg.id !== id));
}

function toggleRole(id: string) {
    const msg = props.messages.find((m) => m.id === id);
    if (msg) {
        updateMessage(id, { role: msg.role === 'user' ? 'assistant' : 'user' });
    }
}

function duplicateMessage(id: string) {
    const msg = props.messages.find((m) => m.id === id);
    if (msg) {
        const index = props.messages.findIndex((m) => m.id === id);
        const newMessage: PromptMessage = {
            id: generateId(),
            role: msg.role,
            content: msg.content,
        };
        const updated = [...props.messages];
        updated.splice(index + 1, 0, newMessage);
        emit('update:messages', updated);
    }
}

// Drag and drop handlers
function onDragStart(index: number) {
    draggedIndex.value = index;
}

function onDragOver(e: DragEvent, index: number) {
    e.preventDefault();
    if (draggedIndex.value === null || draggedIndex.value === index) return;

    const messages = [...props.messages];
    const [removed] = messages.splice(draggedIndex.value, 1);
    messages.splice(index, 0, removed);
    emit('update:messages', messages);
    draggedIndex.value = index;
}

function onDragEnd() {
    draggedIndex.value = null;
}

function insertVariable(messageId: string, variableName: string) {
    const msg = props.messages.find((m) => m.id === messageId);
    if (msg) {
        updateMessage(messageId, { content: msg.content + `{${variableName}}` });
    }
}
</script>

<template>
    <div class="space-y-2">
        <TransitionGroup name="list" tag="div" class="space-y-2">
            <div
                v-for="(message, index) in messages"
                :key="message.id"
                :draggable="isEditable"
                class="transition-all duration-200"
                :class="{ 'opacity-50': draggedIndex === index }"
                @dragstart="onDragStart(index)"
                @dragover="(e) => onDragOver(e, index)"
                @dragend="onDragEnd"
            >
                <MessageItem
                    :message="message"
                    :index="index"
                    :is-editable="isEditable"
                    :available-variables="availableVariables"
                    @update:content="updateMessage(message.id, { content: $event })"
                    @toggle-role="toggleRole(message.id)"
                    @duplicate="duplicateMessage(message.id)"
                    @remove="removeMessage(message.id)"
                    @insert-variable="insertVariable(message.id, $event)"
                />
            </div>
        </TransitionGroup>

        <Button
            v-if="isEditable"
            variant="ghost"
            size="sm"
            class="w-full justify-center border border-dashed border-zinc-300 dark:border-zinc-600"
            @click="addMessage"
        >
            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Message
        </Button>
    </div>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}

.list-move {
    transition: transform 0.3s ease;
}
</style>
