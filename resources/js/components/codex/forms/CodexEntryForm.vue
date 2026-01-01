<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Card from '@/components/ui/layout/Card.vue';
import Input from '@/components/ui/forms/Input.vue';
import Textarea from '@/components/ui/forms/Textarea.vue';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

interface FormData {
    type: string;
    name: string;
    description: string;
    ai_context_mode: string;
}

interface InitialData extends Partial<FormData> {
    thumbnail_path?: string | null;
}

const props = withDefaults(
    defineProps<{
        initialData?: InitialData;
        entryId?: number | null;
        types: string[];
        contextModes: string[];
        submitLabel?: string;
        loading?: boolean;
        errors?: Record<string, string>;
    }>(),
    {
        submitLabel: 'Save',
        loading: false,
        errors: () => ({}),
        entryId: null,
    },
);

const emit = defineEmits<{
    (e: 'submit', data: FormData): void;
    (e: 'cancel'): void;
    (e: 'thumbnailUpdated', path: string | null): void;
}>();

// Thumbnail state
const thumbnailPreview = ref<string | null>(null);
const thumbnailFile = ref<File | null>(null);
const thumbnailUploading = ref(false);
const thumbnailError = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

// Compute current thumbnail URL
const currentThumbnailUrl = computed(() => {
    if (thumbnailPreview.value) return thumbnailPreview.value;
    if (props.initialData?.thumbnail_path) {
        return `/storage/${props.initialData.thumbnail_path}`;
    }
    return null;
});

// Handle file selection
const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) return;

    // Validate file type
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        thumbnailError.value = 'Please select a valid image (JPEG, PNG, GIF, or WebP)';
        return;
    }

    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
        thumbnailError.value = 'Image must be less than 2MB';
        return;
    }

    thumbnailError.value = null;
    thumbnailFile.value = file;

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        thumbnailPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);

    // If we have an entry ID, upload immediately
    if (props.entryId) {
        uploadThumbnail();
    }
};

// Upload thumbnail to server
const uploadThumbnail = async () => {
    if (!thumbnailFile.value || !props.entryId) return;

    thumbnailUploading.value = true;
    thumbnailError.value = null;

    try {
        const formData = new FormData();
        formData.append('thumbnail', thumbnailFile.value);

        const response = await axios.post(`/api/codex/${props.entryId}/thumbnail`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        emit('thumbnailUpdated', response.data.thumbnail_path);
        thumbnailFile.value = null;
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        thumbnailError.value = axiosError.response?.data?.message || 'Failed to upload thumbnail';
        thumbnailPreview.value = null;
    } finally {
        thumbnailUploading.value = false;
    }
};

// Remove thumbnail
const removeThumbnail = async () => {
    if (props.entryId && props.initialData?.thumbnail_path) {
        thumbnailUploading.value = true;
        try {
            await axios.delete(`/api/codex/${props.entryId}/thumbnail`);
            emit('thumbnailUpdated', null);
        } catch {
            thumbnailError.value = 'Failed to remove thumbnail';
        } finally {
            thumbnailUploading.value = false;
        }
    }

    thumbnailPreview.value = null;
    thumbnailFile.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

// Trigger file input click
const triggerFileInput = () => {
    fileInputRef.value?.click();
};

const typeConfig: Record<string, { label: string; icon: string; description: string }> = {
    character: { label: 'Character', icon: '👤', description: 'People, creatures, or beings' },
    location: { label: 'Location', icon: '📍', description: 'Places and environments' },
    item: { label: 'Item', icon: '⚔️', description: 'Objects and artifacts' },
    lore: { label: 'Lore', icon: '📜', description: 'History and world-building' },
    organization: { label: 'Organization', icon: '🏛️', description: 'Groups and factions' },
    subplot: { label: 'Subplot', icon: '📖', description: 'Secondary storylines' },
};

const contextModeLabels: Record<string, { label: string; description: string }> = {
    always: { label: 'Always', description: 'Always include in AI context' },
    detected: { label: 'Detected', description: 'Include when mentioned in text' },
    manual: { label: 'Manual', description: 'Only when manually selected' },
    never: { label: 'Never', description: 'Never include in AI context' },
};

const formData = ref<FormData>({
    type: props.initialData?.type || '',
    name: props.initialData?.name || '',
    description: props.initialData?.description || '',
    ai_context_mode: props.initialData?.ai_context_mode || 'detected',
});

// Watch for external data changes
watch(
    () => props.initialData,
    (newData) => {
        if (newData) {
            formData.value = {
                type: newData.type || formData.value.type,
                name: newData.name || formData.value.name,
                description: newData.description || formData.value.description,
                ai_context_mode: newData.ai_context_mode || formData.value.ai_context_mode,
            };
        }
    },
    { deep: true },
);

const selectType = (type: string) => {
    formData.value.type = type;
};

const handleSubmit = () => {
    emit('submit', { ...formData.value });
};

const isValid = () => {
    return formData.value.type && formData.value.name.trim();
};
</script>

<template>
    <form @submit.prevent="handleSubmit">
        <!-- Entry Type -->
        <Card class="mb-6">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Entry Type</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <button
                    v-for="type in types"
                    :key="type"
                    type="button"
                    class="flex flex-col items-center rounded-lg border p-4 text-center transition-all active:scale-[0.98]"
                    :class="
                        formData.type === type
                            ? 'border-violet-500 bg-violet-50 ring-2 ring-violet-500/20 dark:border-violet-500 dark:bg-violet-900/20'
                            : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                    "
                    @click="selectType(type)"
                >
                    <span class="text-2xl">{{ typeConfig[type]?.icon }}</span>
                    <span class="mt-2 font-medium text-zinc-900 dark:text-white">{{ typeConfig[type]?.label }}</span>
                    <span class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ typeConfig[type]?.description }}</span>
                </button>
            </div>
            <p v-if="errors.type" class="mt-2 text-sm text-red-500">{{ errors.type }}</p>
        </Card>

        <!-- Basic Info -->
        <Card class="mb-6">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Basic Information</h2>
            <div class="space-y-4">
                <Input v-model="formData.name" label="Name" placeholder="Enter entry name" required :error="errors.name" />

                <!-- Thumbnail Upload -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Thumbnail</label>
                    <div class="flex items-start gap-4">
                        <!-- Thumbnail Preview -->
                        <div
                            class="relative h-20 w-20 shrink-0 overflow-hidden rounded-lg border-2 border-dashed transition-colors"
                            :class="
                                currentThumbnailUrl
                                    ? 'border-transparent'
                                    : 'border-zinc-300 dark:border-zinc-600 hover:border-violet-400 dark:hover:border-violet-500'
                            "
                        >
                            <img
                                v-if="currentThumbnailUrl"
                                :src="currentThumbnailUrl"
                                alt="Thumbnail preview"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full w-full cursor-pointer items-center justify-center bg-zinc-50 dark:bg-zinc-800"
                                @click="triggerFileInput"
                            >
                                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                            <!-- Loading overlay -->
                            <div
                                v-if="thumbnailUploading"
                                class="absolute inset-0 flex items-center justify-center bg-black/50"
                            >
                                <div class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent" />
                            </div>
                        </div>

                        <!-- Upload controls -->
                        <div class="flex-1">
                            <input
                                ref="fileInputRef"
                                type="file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                class="hidden"
                                @change="handleFileSelect"
                            />
                            <div class="flex flex-wrap gap-2">
                                <Button
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    :disabled="thumbnailUploading"
                                    @click="triggerFileInput"
                                >
                                    {{ currentThumbnailUrl ? 'Change' : 'Upload' }}
                                </Button>
                                <Button
                                    v-if="currentThumbnailUrl"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    :disabled="thumbnailUploading"
                                    @click="removeThumbnail"
                                >
                                    Remove
                                </Button>
                            </div>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                JPEG, PNG, GIF, or WebP. Max 2MB.
                            </p>
                            <p v-if="thumbnailError" class="mt-1 text-xs text-red-500">
                                {{ thumbnailError }}
                            </p>
                            <p v-if="thumbnailFile && !entryId" class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                Thumbnail will be uploaded after saving.
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <Textarea
                        v-model="formData.description"
                        label="Description"
                        placeholder="Describe this entry..."
                        rows="5"
                        :error="errors.description"
                    />
                    <!-- Description Guidelines -->
                    <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                        <span class="font-medium">Tip:</span> Write in 3rd person. Include key traits, motivations, and relationships. This text is sent to AI for context.
                    </p>
                </div>
            </div>
        </Card>

        <!-- AI Settings -->
        <Card class="mb-6">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">AI Context Settings</h2>
            <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">Control how this entry is included when AI generates content.</p>
            <div class="space-y-2">
                <label
                    v-for="mode in contextModes"
                    :key="mode"
                    class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition-all"
                    :class="
                        formData.ai_context_mode === mode
                            ? 'border-violet-500 bg-violet-50 dark:border-violet-500 dark:bg-violet-900/20'
                            : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                    "
                >
                    <input
                        v-model="formData.ai_context_mode"
                        type="radio"
                        :value="mode"
                        class="mt-1 h-4 w-4 border-zinc-300 text-violet-600 focus:ring-violet-500"
                    />
                    <div>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ contextModeLabels[mode]?.label }}</span>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ contextModeLabels[mode]?.description }}</p>
                    </div>
                </label>
            </div>
        </Card>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <Button type="button" variant="ghost" @click="emit('cancel')"> Cancel </Button>
            <Button type="submit" :loading="loading" :disabled="!isValid() || loading">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
