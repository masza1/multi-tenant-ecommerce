<template>
    <Teleport to="body">
        <div class="fixed top-0 right-0 p-4 z-50 space-y-3 pointer-events-none">
            <TransitionGroup name="toast" tag="div">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 pointer-events-auto animate-slideIn',
                        toastClasses(toast.type)
                    ]"
                >
                    <!-- Icon -->
                    <component :is="toastIcon(toast.type)" class="w-5 h-5 flex-shrink-0" />

                    <!-- Message -->
                    <span class="text-sm font-medium">{{ toast.message }}</span>

                    <!-- Close Button -->
                    <button
                        @click="removeToast(toast.id)"
                        class="ml-2 text-lg font-bold opacity-60 hover:opacity-100 transition"
                    >
                        ×
                    </button>

                    <!-- Progress Bar -->
                    <div
                        class="absolute bottom-0 left-0 h-1 bg-current opacity-30"
                        :style="{ width: toast.progress + '%' }"
                    />
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import {
    CheckCircleIcon,
    ExclamationCircleIcon,
    XCircleIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    toasts: { type: Array, default: () => [] },
});

const emit = defineEmits(['remove']);

const removeToast = (id) => {
    emit('remove', id);
};

const toastClasses = (type) => {
    const classes = {
        success: 'bg-green-50 text-green-800 border border-green-200',
        error: 'bg-red-50 text-red-800 border border-red-200',
        warning: 'bg-yellow-50 text-yellow-800 border border-yellow-200',
        info: 'bg-blue-50 text-blue-800 border border-blue-200',
    };
    return classes[type] || classes.info;
};

const toastIcon = (type) => {
    const icons = {
        success: CheckCircleIcon,
        error: XCircleIcon,
        warning: ExclamationCircleIcon,
        info: InformationCircleIcon,
    };
    return icons[type] || InformationCircleIcon;
};
</script>

<style scoped>
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(400px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.animate-slideIn {
    animation: slideIn 0.3s ease-out;
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(400px);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(400px);
}
</style>
