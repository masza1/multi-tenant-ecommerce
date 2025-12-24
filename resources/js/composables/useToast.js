import { ref } from 'vue';

const toasts = ref([]);
let toastId = 0;

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

const success = (message, duration = 5000) => {
    return addToast(message, 'success', duration);
};

const error = (message, duration = 5000) => {
    return addToast(message, 'error', duration);
};

const warning = (message, duration = 5000) => {
    return addToast(message, 'warning', duration);
};

const info = (message, duration = 5000) => {
    return addToast(message, 'info', duration);
};

const removeToast = (id) => {
    const index = toasts.value.findIndex((t) => t.id === id);
    if (index > -1) {
        toasts.value.splice(index, 1);
    }
};

const clearAll = () => {
    toasts.value = [];
};

// Toast object with methods (for compatibility with code that uses toast.success())
const toast = {
    success,
    error,
    warning,
    info,
};

export function useToast() {
    return {
        toasts,
        addToast,
        success,
        error,
        warning,
        info,
        removeToast,
        clearAll,
        toast, // Add this for Shop Index compatibility
    };
}
