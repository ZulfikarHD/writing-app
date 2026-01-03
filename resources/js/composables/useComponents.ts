import { ref, computed } from 'vue';

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

export interface ComponentFormData {
    name: string;
    label: string;
    content: string;
    description?: string | null;
}

export interface ComponentUsage {
    prompt_id: number;
    prompt_name: string;
    prompt_type: string;
}

/**
 * Composable for managing prompt components.
 * Components are reusable instruction blocks that can be inserted into prompts
 * using the {include("name")} syntax.
 */
export function useComponents() {
    const components = ref<PromptComponent[]>([]);
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    const csrfToken = computed(() => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    });

    /**
     * System components only.
     */
    const systemComponents = computed(() => {
        return components.value.filter((c) => c.is_system);
    });

    /**
     * User-created components only.
     */
    const userComponents = computed(() => {
        return components.value.filter((c) => !c.is_system);
    });

    /**
     * All components sorted by label.
     */
    const sortedComponents = computed(() => {
        return [...components.value].sort((a, b) => {
            // System components first
            if (a.is_system !== b.is_system) {
                return a.is_system ? -1 : 1;
            }
            return a.label.localeCompare(b.label);
        });
    });

    /**
     * Fetch all components accessible by the current user.
     */
    async function fetchComponents(): Promise<void> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/prompt-components', {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch components');
            }

            const data = await response.json();
            components.value = data.components;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch components';
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Get a single component.
     */
    async function getComponent(id: number): Promise<PromptComponent | null> {
        try {
            const response = await fetch(`/api/prompt-components/${id}`, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch component');
            }

            const data = await response.json();
            return data.component;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch component';
            return null;
        }
    }

    /**
     * Create a new component.
     */
    async function createComponent(data: ComponentFormData): Promise<PromptComponent | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/prompt-components', {
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
                throw new Error(errorData.message || errorData.error || 'Failed to create component');
            }

            const result = await response.json();
            components.value.push(result.component);
            return result.component;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to create component';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Update an existing component.
     */
    async function updateComponent(
        id: number,
        data: Partial<ComponentFormData>
    ): Promise<PromptComponent | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompt-components/${id}`, {
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
                throw new Error(errorData.message || errorData.error || 'Failed to update component');
            }

            const result = await response.json();

            // Update local state
            const index = components.value.findIndex((c) => c.id === id);
            if (index !== -1) {
                components.value[index] = result.component;
            }

            return result.component;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to update component';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Delete a component.
     */
    async function deleteComponent(id: number): Promise<boolean> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompt-components/${id}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || errorData.error || 'Failed to delete component');
            }

            // Remove from local state
            components.value = components.value.filter((c) => c.id !== id);
            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to delete component';
            return false;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Clone a component.
     */
    async function cloneComponent(id: number): Promise<PromptComponent | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompt-components/${id}/clone`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || errorData.error || 'Failed to clone component');
            }

            const result = await response.json();
            components.value.push(result.component);
            return result.component;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to clone component';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Get the include() call syntax for a component.
     */
    function getIncludeSyntax(component: PromptComponent): string {
        return `{include("${component.name}")}`;
    }

    /**
     * Copy the include() call to clipboard.
     */
    async function copyIncludeCall(component: PromptComponent): Promise<boolean> {
        try {
            await navigator.clipboard.writeText(getIncludeSyntax(component));
            return true;
        } catch {
            return false;
        }
    }

    /**
     * Find component by name (case-insensitive).
     */
    function findByName(name: string): PromptComponent | undefined {
        const lowerName = name.toLowerCase();
        return components.value.find((c) => c.name.toLowerCase() === lowerName);
    }

    /**
     * Check if a component name is available for the user.
     */
    function isNameAvailable(name: string, excludeId?: number): boolean {
        const lowerName = name.toLowerCase();
        return !userComponents.value.some(
            (c) => c.name.toLowerCase() === lowerName && c.id !== excludeId
        );
    }

    /**
     * Validate a component name (must be valid identifier).
     */
    function isValidName(name: string): boolean {
        return /^[a-z_][a-z0-9_]*$/i.test(name);
    }

    return {
        // State
        components,
        isLoading,
        error,

        // Computed
        systemComponents,
        userComponents,
        sortedComponents,

        // Actions
        fetchComponents,
        getComponent,
        createComponent,
        updateComponent,
        deleteComponent,
        cloneComponent,

        // Utilities
        getIncludeSyntax,
        copyIncludeCall,
        findByName,
        isNameAvailable,
        isValidName,
    };
}
