import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useToast } from './useToast';

/**
 * Composable to automatically show toast notifications from server-side flash messages
 * Integrates with Inertia.js flash messages system
 */
export function useFlashToast() {
    const page = usePage();
    const toast = useToast();

    // Watch for flash messages from server and show as toasts
    watch(
        () => page.props.flash,
        (flash) => {
            if (!flash) return;

            if (flash.success) {
                toast.success(flash.success);
            }

            if (flash.error) {
                toast.error(flash.error, 7000); // Longer duration for errors
            }

            if (flash.warning) {
                toast.warning(flash.warning);
            }

            if (flash.info) {
                toast.info(flash.info);
            }
        },
        { deep: true }
    );

    return {
        ...toast,
    };
}

export default useFlashToast;
