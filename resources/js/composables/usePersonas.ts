import { ref, computed } from 'vue';

export type InteractionType = 'chat' | 'prose' | 'replacement' | 'summary';

export interface Persona {
    id: number;
    user_id: number;
    name: string;
    description: string | null;
    system_message: string;
    interaction_types: InteractionType[] | null;
    project_ids: number[] | null;
    is_default: boolean;
    is_archived: boolean;
    created_at: string | null;
    updated_at: string | null;
}

export interface PersonaFormData {
    name: string;
    description?: string | null;
    system_message: string;
    interaction_types?: InteractionType[] | null;
    project_ids?: number[] | null;
    is_default?: boolean;
}

export interface InteractionTypeInfo {
    chat: string;
    prose: string;
    replacement: string;
    summary: string;
}

/**
 * Composable for managing prompt personas.
 */
export function usePersonas() {
    const personas = ref<Persona[]>([]);
    const isLoading = ref(false);
    const error = ref<string | null>(null);
    const interactionTypes = ref<InteractionTypeInfo>({
        chat: 'Workshop Chat',
        prose: 'Scene Beat Completion',
        replacement: 'Text Replacement',
        summary: 'Scene Summarization',
    });

    const csrfToken = computed(() => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    });

    /**
     * Active (non-archived) personas only.
     */
    const activePersonas = computed(() => {
        return personas.value.filter((p) => !p.is_archived);
    });

    /**
     * Archived personas only.
     */
    const archivedPersonas = computed(() => {
        return personas.value.filter((p) => p.is_archived);
    });

    /**
     * Default personas.
     */
    const defaultPersonas = computed(() => {
        return personas.value.filter((p) => p.is_default && !p.is_archived);
    });

    /**
     * Fetch all personas for the current user.
     */
    async function fetchPersonas(includeArchived = false): Promise<void> {
        isLoading.value = true;
        error.value = null;

        try {
            const params = new URLSearchParams();
            if (includeArchived) params.append('include_archived', '1');

            const url = `/api/prompt-personas${params.toString() ? `?${params}` : ''}`;
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch personas');
            }

            const data = await response.json();
            personas.value = data.personas;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch personas';
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Fetch personas applicable for a specific context (type and/or project).
     */
    async function fetchPersonasForContext(
        type?: InteractionType,
        projectId?: number
    ): Promise<Persona[]> {
        try {
            const params = new URLSearchParams();
            if (type) params.append('type', type);
            if (projectId) params.append('project_id', projectId.toString());

            const url = `/api/prompt-personas/context${params.toString() ? `?${params}` : ''}`;
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch personas for context');
            }

            const data = await response.json();
            return data.personas;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch personas';
            return [];
        }
    }

    /**
     * Get a single persona.
     */
    async function getPersona(id: number): Promise<Persona | null> {
        try {
            const response = await fetch(`/api/prompt-personas/${id}`, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch persona');
            }

            const data = await response.json();
            return data.persona;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch persona';
            return null;
        }
    }

    /**
     * Create a new persona.
     */
    async function createPersona(data: PersonaFormData): Promise<Persona | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/prompt-personas', {
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
                throw new Error(errorData.message || 'Failed to create persona');
            }

            const result = await response.json();
            personas.value.push(result.persona);
            return result.persona;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to create persona';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Update an existing persona.
     */
    async function updatePersona(id: number, data: Partial<PersonaFormData>): Promise<Persona | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompt-personas/${id}`, {
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
                throw new Error(errorData.message || 'Failed to update persona');
            }

            const result = await response.json();

            // Update local state
            const index = personas.value.findIndex((p) => p.id === id);
            if (index !== -1) {
                personas.value[index] = result.persona;
            }

            return result.persona;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to update persona';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Archive a persona.
     */
    async function archivePersona(id: number): Promise<boolean> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompt-personas/${id}/archive`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to archive persona');
            }

            // Update local state
            const persona = personas.value.find((p) => p.id === id);
            if (persona) {
                persona.is_archived = true;
            }

            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to archive persona';
            return false;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Restore an archived persona.
     */
    async function restorePersona(id: number): Promise<Persona | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompt-personas/${id}/restore`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to restore persona');
            }

            const result = await response.json();

            // Update local state
            const index = personas.value.findIndex((p) => p.id === id);
            if (index !== -1) {
                personas.value[index] = result.persona;
            }

            return result.persona;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to restore persona';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Delete a persona permanently.
     */
    async function deletePersona(id: number): Promise<boolean> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/prompt-personas/${id}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to delete persona');
            }

            // Remove from local state
            personas.value = personas.value.filter((p) => p.id !== id);
            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to delete persona';
            return false;
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Get personas applicable for a specific interaction type.
     */
    function getPersonasForType(type: InteractionType): Persona[] {
        return activePersonas.value.filter((p) => {
            if (p.interaction_types === null) {
                return true; // null means all types
            }
            return p.interaction_types.includes(type);
        });
    }

    /**
     * Get personas applicable for a specific project.
     */
    function getPersonasForProject(projectId: number | null): Persona[] {
        return activePersonas.value.filter((p) => {
            if (p.project_ids === null) {
                return true; // null means all projects
            }
            if (projectId === null) {
                return true; // If no project specified, return all
            }
            return p.project_ids.includes(projectId);
        });
    }

    /**
     * Get interaction type label.
     */
    function getInteractionTypeLabel(type: InteractionType): string {
        return interactionTypes.value[type] || type;
    }

    return {
        // State
        personas,
        isLoading,
        error,
        interactionTypes,

        // Computed
        activePersonas,
        archivedPersonas,
        defaultPersonas,

        // Actions
        fetchPersonas,
        fetchPersonasForContext,
        getPersona,
        createPersona,
        updatePersona,
        archivePersona,
        restorePersona,
        deletePersona,
        getPersonasForType,
        getPersonasForProject,
        getInteractionTypeLabel,
    };
}
