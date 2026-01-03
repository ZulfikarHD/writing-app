import { ref, computed } from 'vue';

export interface PromptMessage {
    id: string;
    role: 'user' | 'assistant';
    content: string;
}

export interface PromptInput {
    id: number;
    prompt_id: number;
    name: string;
    label: string;
    type: 'text' | 'textarea' | 'select' | 'number' | 'checkbox';
    options?: { value: string; label: string }[];
    default_value?: string | null;
    placeholder?: string | null;
    description?: string | null;
    is_required: boolean;
    sort_order: number;
}

export interface PromptComponent {
    id: number;
    user_id: number;
    name: string;
    label: string;
    content: string;
    description?: string | null;
    is_system: boolean;
    created_at?: string | null;
    updated_at?: string | null;
}

export interface Prompt {
    id: number;
    user_id: number | null;
    folder_id: number | null;
    name: string;
    description: string | null;
    type: 'chat' | 'prose' | 'replacement' | 'summary';
    type_label: string;
    system_message: string | null;
    user_message: string | null;
    messages: PromptMessage[] | null;
    model_settings: ModelSettings | null;
    is_system: boolean;
    is_active: boolean;
    sort_order: number;
    usage_count: number;
    inputs?: PromptInput[];
    created_at: string | null;
    updated_at: string | null;
}

export interface ModelSettings {
    temperature?: number;
    max_tokens?: number;
    top_p?: number;
    frequency_penalty?: number;
    presence_penalty?: number;
    repetition_penalty?: number;
    stop_sequences?: string[];
}

export interface PromptFormData {
    name: string;
    description?: string | null;
    type: 'chat' | 'prose' | 'replacement' | 'summary';
    system_message?: string | null;
    user_message?: string | null;
    messages?: PromptMessage[] | null;
    model_settings?: ModelSettings | null;
    folder_id?: number | null;
    sort_order?: number;
    is_active?: boolean;
}

export interface PromptTypeInfo {
    chat: string;
    prose: string;
    replacement: string;
    summary: string;
}

/**
 * Composable for managing prompts.
 */
export function usePrompts() {
    const prompts = ref<Prompt[]>([]);
    const isLoading = ref(false);
    const error = ref<string | null>(null);
    const types = ref<PromptTypeInfo>({
        chat: 'Workshop Chat',
        prose: 'Scene Beat Completion',
        replacement: 'Text Replacement',
        summary: 'Scene Summarization',
    });

    const csrfToken = computed(() => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    });

    /**
     * Filter prompts by type.
     */
    const promptsByType = computed(() => {
        const grouped: Record<string, Prompt[]> = {
            chat: [],
            prose: [],
            replacement: [],
            summary: [],
        };

        for (const prompt of prompts.value) {
            if (grouped[prompt.type]) {
                grouped[prompt.type].push(prompt);
            }
        }

        return grouped;
    });

    /**
     * System prompts only.
     */
    const systemPrompts = computed(() => {
        return prompts.value.filter((p) => p.is_system);
    });

    /**
     * User prompts only.
     */
    const userPrompts = computed(() => {
        return prompts.value.filter((p) => !p.is_system);
    });

    /**
     * Fetch all accessible prompts.
     */
    async function fetchPrompts(type?: string, search?: string): Promise<void> {
        isLoading.value = true;
        error.value = null;

        try {
            const params = new URLSearchParams();
            if (type) params.append('type', type);
            if (search) params.append('search', search);

            const url = `/api/prompts${params.toString() ? `?${params}` : ''}`;
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch prompts');
            }

            const data = await response.json();
            prompts.value = data.prompts;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch prompts';
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Fetch prompts by type (for dropdowns).
     */
    async function fetchPromptsByType(type: string): Promise<Prompt[]> {
        try {
            const response = await fetch(`/api/prompts/type/${type}`, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch prompts');
            }

            const data = await response.json();
            return data.prompts;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch prompts';
            return [];
        }
    }

    /**
     * Get a single prompt.
     */
    async function getPrompt(id: number): Promise<Prompt | null> {
        try {
            const response = await fetch(`/api/prompts/${id}`, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch prompt');
            }

            const data = await response.json();
            return data.prompt;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch prompt';
            return null;
        }
    }

    /**
     * Create a new prompt.
     */
    async function createPrompt(data: PromptFormData): Promise<Prompt | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/prompts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to create prompt');
            }

            const result = await response.json();
            prompts.value.push(result.prompt);
            return result.prompt;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to create prompt';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Update an existing prompt.
     */
    async function updatePrompt(id: number, data: Partial<PromptFormData>): Promise<Prompt | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompts/${id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to update prompt');
            }

            const result = await response.json();

            // Update local state
            const index = prompts.value.findIndex((p) => p.id === id);
            if (index !== -1) {
                prompts.value[index] = result.prompt;
            }

            return result.prompt;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to update prompt';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Delete a prompt.
     */
    async function deletePrompt(id: number): Promise<boolean> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompts/${id}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to delete prompt');
            }

            // Remove from local state
            prompts.value = prompts.value.filter((p) => p.id !== id);
            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to delete prompt';
            return false;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Clone a prompt.
     */
    async function clonePrompt(id: number, newName?: string): Promise<Prompt | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompts/${id}/clone`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                body: JSON.stringify({ name: newName }),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to clone prompt');
            }

            const result = await response.json();
            prompts.value.push(result.prompt);
            return result.prompt;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to clone prompt';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Record prompt usage.
     */
    async function recordUsage(id: number): Promise<void> {
        try {
            await fetch(`/api/prompts/${id}/usage`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            // Update local state
            const prompt = prompts.value.find((p) => p.id === id);
            if (prompt) {
                prompt.usage_count++;
            }
        } catch {
            // Silent fail - usage tracking is not critical
        }
    }

    /**
     * Get type label for a prompt type.
     */
    function getTypeLabel(type: string): string {
        return types.value[type as keyof PromptTypeInfo] || type;
    }

    return {
        // State
        prompts,
        isLoading,
        error,
        types,

        // Computed
        promptsByType,
        systemPrompts,
        userPrompts,

        // Actions
        fetchPrompts,
        fetchPromptsByType,
        getPrompt,
        createPrompt,
        updatePrompt,
        deletePrompt,
        clonePrompt,
        recordUsage,
        getTypeLabel,
    };
}

// ==================== Folder Tree Utilities ====================

export interface ParsedPromptName {
    folders: string[];
    displayName: string;
    fullPath: string;
}

export interface PromptFolderNode {
    name: string;
    path: string;
    prompts: Prompt[];
    children: PromptFolderNode[];
}

/**
 * Parse a prompt name to extract folder path.
 * Format: "Folder / Subfolder / Prompt Name"
 * The separator is " / " (space-slash-space).
 */
export function parsePromptName(name: string): ParsedPromptName {
    const parts = name.split(' / ').map(p => p.trim()).filter(Boolean);
    
    if (parts.length <= 1) {
        return {
            folders: [],
            displayName: name,
            fullPath: '',
        };
    }
    
    const displayName = parts.pop()!;
    return {
        folders: parts,
        displayName,
        fullPath: parts.join(' / '),
    };
}

/**
 * Get the display name of a prompt (last part after folder separators).
 */
export function getPromptDisplayName(prompt: Prompt): string {
    return parsePromptName(prompt.name).displayName;
}

/**
 * Get the folder path of a prompt (everything before the display name).
 */
export function getPromptFolderPath(prompt: Prompt): string {
    return parsePromptName(prompt.name).fullPath;
}

/**
 * Build a folder tree from a list of prompts.
 */
export function buildPromptFolderTree(prompts: Prompt[]): PromptFolderNode[] {
    const root: PromptFolderNode[] = [];
    const folderMap = new Map<string, PromptFolderNode>();

    // Sort prompts by name for consistent tree building
    const sortedPrompts = [...prompts].sort((a, b) => a.name.localeCompare(b.name));

    for (const prompt of sortedPrompts) {
        const parsed = parsePromptName(prompt.name);

        if (parsed.folders.length === 0) {
            // Prompt at root level - find or create root-level node for ungrouped prompts
            let rootNode = root.find(n => n.name === '' && n.path === '');
            if (!rootNode) {
                rootNode = { name: '', path: '', prompts: [], children: [] };
                root.unshift(rootNode); // Add at beginning
            }
            rootNode.prompts.push(prompt);
            continue;
        }

        // Navigate/create folder structure
        let currentLevel = root;
        let currentPath = '';

        for (let i = 0; i < parsed.folders.length; i++) {
            const folderName = parsed.folders[i];
            currentPath = currentPath ? `${currentPath} / ${folderName}` : folderName;

            let existingFolder = currentLevel.find(n => n.name === folderName);
            
            if (!existingFolder) {
                existingFolder = {
                    name: folderName,
                    path: currentPath,
                    prompts: [],
                    children: [],
                };
                currentLevel.push(existingFolder);
                folderMap.set(currentPath, existingFolder);
            }

            // If this is the last folder, add the prompt here
            if (i === parsed.folders.length - 1) {
                existingFolder.prompts.push(prompt);
            } else {
                // Move to next level
                currentLevel = existingFolder.children;
            }
        }
    }

    // Sort folders alphabetically (but keep root-level ungrouped at top)
    const sortFolders = (nodes: PromptFolderNode[]) => {
        nodes.sort((a, b) => {
            // Empty name (ungrouped) stays at top
            if (a.name === '' && b.name !== '') return -1;
            if (a.name !== '' && b.name === '') return 1;
            return a.name.localeCompare(b.name);
        });
        for (const node of nodes) {
            if (node.children.length > 0) {
                sortFolders(node.children);
            }
        }
    };
    sortFolders(root);

    return root;
}

/**
 * Get all unique folder paths from a list of prompts.
 */
export function getUniqueFolderPaths(prompts: Prompt[]): string[] {
    const paths = new Set<string>();
    
    for (const prompt of prompts) {
        const parsed = parsePromptName(prompt.name);
        let currentPath = '';
        
        for (const folder of parsed.folders) {
            currentPath = currentPath ? `${currentPath} / ${folder}` : folder;
            paths.add(currentPath);
        }
    }
    
    return Array.from(paths).sort();
}

/**
 * Filter prompts by folder path.
 */
export function filterPromptsByFolder(prompts: Prompt[], folderPath: string): Prompt[] {
    if (!folderPath) {
        // Return prompts without folder structure
        return prompts.filter(p => parsePromptName(p.name).folders.length === 0);
    }
    
    return prompts.filter(p => {
        const parsed = parsePromptName(p.name);
        return parsed.fullPath === folderPath || parsed.fullPath.startsWith(folderPath + ' / ');
    });
}

/**
 * Check if a prompt is in a specific folder (exact match, not nested).
 */
export function isPromptInFolder(prompt: Prompt, folderPath: string): boolean {
    const parsed = parsePromptName(prompt.name);
    return parsed.fullPath === folderPath;
}
