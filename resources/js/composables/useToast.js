import { ref } from 'vue';

const toasts = ref([]);
let toastId = 0;

export function useToast() {
    /**
     * Add a new toast notification
     * @param {string} message - Toast message
     * @param {string} type - Toast type: 'success', 'error', 'warning', 'info'
     * @param {number} duration - Auto-dismiss duration in milliseconds (0 = no auto-dismiss)
     */
    const addToast = (message, type = 'info', duration = 5000) => {
        const id = toastId++;
        const toast = {
            id,
            message,
            type,
            progress: 100,
        };

        toasts.value.push(toast);

        if (duration > 0) {
            // Progress bar animation
            let elapsed = 0;
            const interval = setInterval(() => {
                elapsed += 50;
                toast.progress = Math.max(0, 100 - (elapsed / duration) * 100);

                if (elapsed >= duration) {
                    clearInterval(interval);
                    removeToast(id);
                }
            }, 50);
        }

        return id;
    };

    /**
     * Show success toast
     */
    const success = (message, duration = 5000) => {
        return addToast(message, 'success', duration);
    };

    /**
     * Show error toast
     */
    const error = (message, duration = 5000) => {
        return addToast(message, 'error', duration);
    };

    /**
     * Show warning toast
     */
    const warning = (message, duration = 5000) => {
        return addToast(message, 'warning', duration);
    };

    /**
     * Show info toast
     */
    const info = (message, duration = 5000) => {
        return addToast(message, 'info', duration);
    };

    /**
     * Remove toast by ID
     */
    const removeToast = (id) => {
        const index = toasts.value.findIndex((t) => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    /**
     * Clear all toasts
     */
    const clearAll = () => {
        toasts.value = [];
    };

    return {
        toasts,
        addToast,
        success,
        error,
        warning,
        info,
        removeToast,
        clearAll,
    };
}
