import { ref, shallowRef } from 'vue';

export interface ToastOptions {
    id?: string;
    variant?: 'info' | 'success' | 'warning' | 'danger';
    title?: string;
    message: string;
    duration?: number;
    position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center';
    dismissible?: boolean;
}

export interface Toast extends ToastOptions {
    id: string;
}

const toasts = shallowRef<Toast[]>([]);
const defaultPosition = ref<ToastOptions['position']>('top-right');
const defaultDuration = ref(5000);

export function useToast() {
    const add = (options: ToastOptions): string => {
        const id = options.id || `toast-${Date.now()}-${Math.random().toString(36).substring(7)}`;
        const toast: Toast = {
            id,
            variant: options.variant || 'info',
            title: options.title,
            message: options.message,
            duration: options.duration ?? defaultDuration.value,
            position: options.position || defaultPosition.value,
            dismissible: options.dismissible ?? true,
        };

        toasts.value = [...toasts.value, toast];
        return id;
    };

    const remove = (id: string) => {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    };

    const clear = () => {
        toasts.value = [];
    };

    // Convenience methods
    const success = (message: string, options?: Partial<Omit<ToastOptions, 'message' | 'variant'>>) => {
        return add({ ...options, message, variant: 'success' });
    };

    const error = (message: string, options?: Partial<Omit<ToastOptions, 'message' | 'variant'>>) => {
        return add({ ...options, message, variant: 'danger' });
    };

    const warning = (message: string, options?: Partial<Omit<ToastOptions, 'message' | 'variant'>>) => {
        return add({ ...options, message, variant: 'warning' });
    };

    const info = (message: string, options?: Partial<Omit<ToastOptions, 'message' | 'variant'>>) => {
        return add({ ...options, message, variant: 'info' });
    };

    return {
        toasts,
        add,
        remove,
        clear,
        success,
        error,
        warning,
        info,
        setDefaultPosition: (position: ToastOptions['position']) => {
            defaultPosition.value = position;
        },
        setDefaultDuration: (duration: number) => {
            defaultDuration.value = duration;
        },
    };
}
