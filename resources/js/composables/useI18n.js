import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Global reactive locale state
const globalLocale = ref(localStorage.getItem('app_locale') || 'en');
let cachedTranslations = null;

/**
 * Simple i18n composable for accessing translations
 * Uses Laravel's translation system via backend
 * Primary language: English (en)
 * Fallback language: English (en)
 */
export function useI18n() {
    const page = usePage();

    /**
     * Get translation string
     * @param {string} key - Translation key (e.g., 'messages.login')
     * @param {object} replacements - Values to replace in translation
     * @returns {string} Translated string
     */
    const trans = (key, replacements = {}) => {
        // Try to get from translations that may be passed via props
        if (page.props.translations && typeof page.props.translations === 'object') {
            const appLocale = globalLocale.value || 'en';
            let translation = page.props.translations[appLocale]?.[key] || key;

            // If key not found, return the key itself (it will show 'messages.key_name' format)
            if (translation === key) {
                translation = key;
            }

            // Replace placeholders with provided values
            Object.entries(replacements).forEach(([placeholder, value]) => {
                translation = translation.replace(`:${placeholder}`, value);
            });

            return translation;
        }

        // Fallback if no translations provided
        return key;
    };

    /**
     * Get current application locale
     * @returns {string} Current locale code (e.g., 'en', 'id')
     */
    const getLocale = () => {
        return globalLocale.value || 'en';
    };

    /**
     * Set application locale
     * @param {string} locale - Locale code to set
     */
    const setLocale = (locale) => {
        globalLocale.value = locale;
        localStorage.setItem('app_locale', locale);
    };

    return {
        trans,
        getLocale,
        setLocale,
        globalLocale,
    };
}

export default useI18n;
