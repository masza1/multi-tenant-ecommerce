<template>
    <div
        :class="[
            'min-h-screen flex items-center justify-center px-4',
            getGradientClass(status),
        ]"
    >
        <div class="max-w-xl w-full text-center">
            <div class="flex flex-col items-center">
                <!-- Error Code -->
                <h1 class="text-9xl font-extrabold text-gray-200 mb-4">
                    {{ status }}
                </h1>

                <!-- Error Icon -->
                <div class="mb-8 inline-flex items-center justify-center">
                    <svg
                        :class="['w-24 h-24', getIconColor(status)]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            v-if="status === 404"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        ></path>
                        <path
                            v-else-if="status === 403"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                        ></path>
                        <path
                            v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1"
                            d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        ></path>
                    </svg>
                </div>

                <!-- Error Title -->
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    {{ title }}
                </h2>

                <!-- Error Message -->
                <p class="text-lg text-gray-600 mb-10 leading-relaxed">
                    {{ message }}
                </p>

                <!-- Stack trace (development only) -->
                <div
                    v-if="stack && isDevelopment"
                    class="bg-gray-100 text-gray-700 text-xs rounded-lg p-4 mb-8 overflow-auto max-h-48 border border-gray-300"
                >
                    <h3 class="font-semibold mb-3 text-left">Stack Trace:</h3>
                    <ul class="list-disc list-inside text-left space-y-1">
                        <li
                            v-for="(line, index) in stack"
                            :key="index"
                            class="break-all"
                        >
                            {{ line }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3">
                <Link
                    href="/"
                    class="inline-block px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg transition duration-200"
                >
                    {{ trans("messages.home") }}
                </Link>

                <button
                    @click="goBack"
                    class="inline-block px-8 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition duration-200"
                >
                    {{ trans("messages.back") }}
                </button>
            </div>

            <!-- Support Info -->
            <div class="mt-12 pt-8 border-t border-gray-300">
                <p class="text-gray-600 text-sm">
                    {{
                        status === 404
                            ? trans("messages.not_found")
                            : status === 403
                            ? trans("messages.access_denied")
                            : trans("messages.something_went_wrong")
                    }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { useI18n } from "@/composables/useI18n";

const { trans } = useI18n();

defineProps({
    status: Number,
    title: String,
    message: String,
    stack: Array,
});

const isDevelopment =
    import.meta.env.DEV || window.location.hostname === "localhost";

const getGradientClass = (status) => {
    switch (status) {
        case 404:
            return "bg-gradient-to-br from-blue-50 via-white to-blue-50";
        case 403:
            return "bg-gradient-to-br from-amber-50 via-white to-orange-50";
        default:
            return "bg-gradient-to-br from-red-50 via-white to-orange-50";
    }
};

const getIconColor = (status) => {
    switch (status) {
        case 404:
            return "text-blue-300";
        case 403:
            return "text-amber-300";
        default:
            return "text-red-300";
    }
};

const goBack = () => {
    window.history.back();
};
</script>
