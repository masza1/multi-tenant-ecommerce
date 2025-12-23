<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <!-- Error Icon -->
            <div class="mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Error Code -->
            <h1 class="text-6xl font-bold text-white mb-2">{{ status }}</h1>

            <!-- Error Title -->
            <h2 class="text-2xl font-semibold text-gray-100 mb-4">{{ title }}</h2>

            <!-- Error Message -->
            <p class="text-gray-400 mb-8 leading-relaxed">
                {{ message }}
            </p>
            
            <!-- stack trace, show while in development mode -->
            <div v-if="stack" class="bg-gray-900 text-gray-400 text-sm rounded-lg p-4 mb-8 overflow-auto max-h-64">
                <h3 class="font-semibold mb-2">Stack Trace:</h3>
                <ul class="list-disc list-inside">
                    <li v-for="(line, index) in stack" :key="index">{{ line }}</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3">
                <Link
                    href="/"
                    class="inline-block px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition duration-200"
                >
                    Kembali ke Beranda
                </Link>

                <button
                    @click="goBack"
                    class="inline-block px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition duration-200"
                >
                    Kembali
                </button>
            </div>

            <!-- Support Info -->
            <div class="mt-12 pt-8 border-t border-gray-700">
                <p class="text-gray-500 text-sm mb-2">Butuh bantuan?</p>
                <p class="text-gray-400 text-sm">
                    Hubungi support atau coba akses halaman lain
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    status: Number,
    title: String,
    message: String,
});

const goBack = () => {
    window.history.back();
};
</script>
