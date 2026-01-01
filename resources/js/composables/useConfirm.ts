import { ref, shallowRef } from 'vue';

export interface ConfirmOptions {
    title?: string;
    message?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'warning' | 'info';
}

interface ConfirmState extends ConfirmOptions {
    isOpen: boolean;
    resolve: ((value: boolean) => void) | null;
}

const state = shallowRef<ConfirmState>({
    isOpen: false,
    title: 'Confirm Action',
    message: 'Are you sure you want to continue?',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'danger',
    resolve: null,
});

const isLoading = ref(false);

export function useConfirm() {
    const confirm = (options: ConfirmOptions = {}): Promise<boolean> => {
        return new Promise((resolve) => {
            state.value = {
                isOpen: true,
                title: options.title || 'Confirm Action',
                message: options.message || 'Are you sure you want to continue?',
                confirmText: options.confirmText || 'Confirm',
                cancelText: options.cancelText || 'Cancel',
                variant: options.variant || 'danger',
                resolve,
            };
        });
    };

    const handleConfirm = () => {
        if (state.value.resolve) {
            state.value.resolve(true);
        }
        close();
    };

    const handleCancel = () => {
        if (state.value.resolve) {
            state.value.resolve(false);
        }
        close();
    };

    const close = () => {
        state.value = {
            ...state.value,
            isOpen: false,
            resolve: null,
        };
        isLoading.value = false;
    };

    const setLoading = (loading: boolean) => {
        isLoading.value = loading;
    };

    return {
        state,
        isLoading,
        confirm,
        handleConfirm,
        handleCancel,
        setLoading,
    };
}

// Convenience function for quick confirmations
export async function confirmAction(options: ConfirmOptions = {}): Promise<boolean> {
    const { confirm } = useConfirm();
    return confirm(options);
}
