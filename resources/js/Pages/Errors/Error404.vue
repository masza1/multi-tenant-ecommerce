<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
        <div class="text-center max-w-md">
            <!-- 404 Number -->
            <h1 class="text-9xl font-bold text-gray-200 mb-4">404</h1>

            <!-- Title -->
            <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ title }}</h2>

            <!-- Message -->
            <p class="text-gray-600 mb-8">{{ message }}</p>

            <!-- Navigation Buttons -->
            <div class="flex gap-4 justify-center">
                <button
                    @click="goToLanding"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    {{ safeTranslate("home") }}
                </button>
                <button
                    @click="goBack"
                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                >
                    {{ safeTranslate("back") }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useI18n } from "@/composables/useI18n";

const { trans } = useI18n();

defineProps({
    title: {
        type: String,
        default: "Halaman Tidak Ditemukan",
    },
    message: {
        type: String,
        default: "Halaman yang Anda cari tidak ada atau telah dipindahkan.",
    },
});

// Fallback translations for error pages
const fallbackMessages = {
    home: "Back to Home",
    back: "Go Back",
};

// Helper function to safely get translation
const safeTranslate = (key) => {
    try {
        const translated = trans(`messages.${key}`);
        // If trans returns the key itself, it means translation failed
        if (translated === `messages.${key}`) {
            return fallbackMessages[key] || key;
        }
        return translated;
    } catch {
        return fallbackMessages[key] || key;
    }
};

const goToLanding = () => {
    const port = window.location.port ? `:${window.location.port}` : "";
    const hostname = window.location.hostname;

    let baseDomain;

    // Special handling for localhost
    if (hostname === 'localhost' || hostname.endsWith('.localhost')) {
        baseDomain = 'localhost';
    } else {
        // Extract base domain without subdomain
        // For tenant.example.com: becomes example.com
        // For tenant.example.co.uk: becomes example.co.uk (takes last 2 parts)
        baseDomain = hostname.split('.').slice(-2).join('.');
    }

    const baseUrl = `${window.location.protocol}//${baseDomain}${port}/`;
    window.location.href = baseUrl;
};

const goBack = () => {
    window.history.back();
};
</script>
