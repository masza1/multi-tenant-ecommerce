<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50">
        <!-- Hero Section -->
        <section class="relative overflow-hidden py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 mb-6">
                        Buat Toko Online
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">
                            Gratis
                        </span>
                    </h1>

                    <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-10">
                        Platform multi-tenant dengan database terpisah untuk setiap toko.
                        Aman, terisolasi, dan mudah digunakan.
                    </p>

                    <button @click="showModal = true" class="inline-block px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition duration-200">
                        Mulai Sekarang - Gratis!
                    </button>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">
                    Kenapa Pilih Kami?
                </h2>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center p-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Database Terpisah</h3>
                        <p class="text-gray-600">
                            Setiap toko memiliki database sendiri. Data Anda aman dan terisolasi.
                        </p>
                    </div>

                    <div class="text-center p-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Mudah & Cepat</h3>
                        <p class="text-gray-600">
                            Setup toko dalam hitungan menit. Tidak perlu skill teknis.
                        </p>
                    </div>

                    <div class="text-center p-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Gratis Selamanya</h3>
                        <p class="text-gray-600">
                            Tidak ada biaya tersembunyi. 100% gratis untuk semua fitur dasar.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Registration Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Daftar Toko Baru</h2>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko</label>
                        <input v-model="form.store_name" type="text" placeholder="Contoh: Toko Sepatu Keren" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <span v-if="form.errors.store_name" class="text-red-500 text-sm">{{ form.errors.store_name }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subdomain</label>
                        <input v-model="form.subdomain" type="text" placeholder="contoh: toko-sepatu" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p class="text-xs text-gray-500 mt-1">Toko Anda akan tersedia di: [subdomain].localhost</p>
                        <span v-if="form.errors.subdomain" class="text-red-500 text-sm">{{ form.errors.subdomain }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Pemilik</label>
                        <input v-model="form.email" type="email" placeholder="email@contoh.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <span v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</span>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition disabled:opacity-50">
                            {{ form.processing ? 'Membuat...' : 'Buat Toko' }}
                        </button>
                        <button type="button" @click="showModal = false" class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const showModal = ref(false);

const form = useForm({
    store_name: '',
    subdomain: '',
    email: '',
});

const submitForm = () => {
    form.post(route('tenant.register'), {
        onSuccess: () => {
            form.reset();
            showModal.value = false;
        },
    });
};
</script>
