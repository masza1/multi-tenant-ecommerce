<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50">
        <!-- Toast Notifications -->
        <Toast :toasts="toasts" @remove="removeToast" />
        <!-- Header with Language Toggle -->
        <header class="sticky top-0 z-40 bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                <div class="text-2xl font-bold text-blue-600">StoreHub</div>
                <div class="flex gap-4">
                    <button
                        v-for="lang in ['en', 'id']"
                        :key="lang"
                        @click="setLanguage(lang)"
                        :class="[
                            'px-3 py-1 rounded font-medium transition',
                            currentLang === lang
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        ]"
                    >
                        {{ lang.toUpperCase() }}
                    </button>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 mb-6">
                        {{ trans('messages.create_store_free') }}
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">
                            {{ currentLang === 'en' ? 'For Free' : 'Gratis' }}
                        </span>
                    </h1>

                    <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-10">
                        {{ trans('messages.multitenant_platform') }}
                    </p>

                    <button @click="showModal = true" class="inline-block px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition duration-200">
                        {{ trans('messages.get_started') }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">
                    {{ trans('messages.why_choose_us') }}
                </h2>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center p-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">{{ trans('messages.separate_databases') }}</h3>
                        <p class="text-gray-600">{{ trans('messages.database_per_store') }}</p>
                    </div>

                    <div class="text-center p-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">{{ trans('messages.easy_quick') }}</h3>
                        <p class="text-gray-600">{{ trans('messages.setup_minutes') }}</p>
                    </div>

                    <div class="text-center p-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">{{ trans('messages.free_forever') }}</h3>
                        <p class="text-gray-600">{{ trans('messages.no_hidden_fees') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Registration Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">
                        {{ trans('messages.register_new_store') }}
                    </h2>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ trans('messages.store_name') }}
                        </label>
                        <input
                            v-model="form.store_name"
                            type="text"
                            :placeholder="trans('messages.example_store_name')"
                            :class="[
                                'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition',
                                form.errors.store_name
                                    ? 'border-red-500 focus:ring-red-500 bg-red-50'
                                    : 'border-gray-300 focus:ring-blue-500'
                            ]"
                        />
                        <span v-if="form.errors.store_name" class="text-red-600 text-sm block mt-1">
                            ⚠️ {{ form.errors.store_name }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ trans('messages.subdomain') }}
                        </label>
                        <input
                            v-model="form.subdomain"
                            type="text"
                            :placeholder="trans('messages.example_subdomain')"
                            :class="[
                                'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition',
                                form.errors.subdomain
                                    ? 'border-red-500 focus:ring-red-500 bg-red-50'
                                    : 'border-gray-300 focus:ring-blue-500'
                            ]"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            {{ trans('messages.store_available_at') }}
                        </p>
                        <span v-if="form.errors.subdomain" class="text-red-600 text-sm block mt-1">
                            ⚠️ {{ form.errors.subdomain }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ trans('messages.owner_email') }}
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="owner@example.com"
                            :class="[
                                'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition',
                                form.errors.email
                                    ? 'border-red-500 focus:ring-red-500 bg-red-50'
                                    : 'border-gray-300 focus:ring-blue-500'
                            ]"
                        />
                        <span v-if="form.errors.email" class="text-red-600 text-sm block mt-1">
                            ⚠️ {{ form.errors.email }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ trans('messages.admin_name') }}
                        </label>
                        <input
                            v-model="form.admin_name"
                            type="text"
                            :placeholder="trans('messages.admin_name_placeholder')"
                            :class="[
                                'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition',
                                form.errors.admin_name
                                    ? 'border-red-500 focus:ring-red-500 bg-red-50'
                                    : 'border-gray-300 focus:ring-blue-500'
                            ]"
                        />
                        <span v-if="form.errors.admin_name" class="text-red-600 text-sm block mt-1">
                            ⚠️ {{ form.errors.admin_name }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ trans('messages.password') }}
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            :class="[
                                'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition',
                                form.errors.password
                                    ? 'border-red-500 focus:ring-red-500 bg-red-50'
                                    : 'border-gray-300 focus:ring-blue-500'
                            ]"
                        />
                        <span v-if="form.errors.password" class="text-red-600 text-sm block mt-1">
                            ⚠️ {{ form.errors.password }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ trans('messages.password_confirmation') }}
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="••••••••"
                            :class="[
                                'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition',
                                form.errors.password_confirmation
                                    ? 'border-red-500 focus:ring-red-500 bg-red-50'
                                    : 'border-gray-300 focus:ring-blue-500'
                            ]"
                        />
                        <span v-if="form.errors.password_confirmation" class="text-red-600 text-sm block mt-1">
                            ⚠️ {{ form.errors.password_confirmation }}
                        </span>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition disabled:opacity-50"
                        >
                            {{ form.processing ? trans('messages.submit') : trans('messages.create_store') }}
                        </button>
                        <button
                            type="button"
                            @click="showModal = false"
                            class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition"
                        >
                            {{ trans('messages.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useI18n } from '@/composables/useI18n';
import { useFlashToast } from '@/composables/useFlashToast';
import Toast from '@/Components/Toast.vue';

const page = usePage();
const { trans, getLocale, setLocale, globalLocale } = useI18n();
const { toasts, removeToast, error, success } = useFlashToast();
const showModal = ref(false);
const currentLang = computed(() => globalLocale.value || 'en');

const form = useForm({
    store_name: 'Store 1',
    subdomain: 'store-1',
    email: 'store1@test.com',
    admin_name: 'Admin Store 1',
    password: 'admin123',
    password_confirmation: 'admin123',
});

// Watch for tenant redirect URL from session
watch(
    () => page.props.tenant_redirect_url,
    (redirectUrl) => {
        if (redirectUrl) {
            // Wait a bit for toast to show
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 1500);
        }
    }
);

// Watch for error flash messages from server
watch(
    () => page.props.flash?.error || page.props.error,
    (errorMessage) => {
        if (errorMessage) {
            error(errorMessage);
        }
    },
    { immediate: true }
);

// Watch for success flash messages from server
watch(
    () => page.props.flash?.success || page.props.success,
    (successMessage) => {
        if (successMessage) {
            success(successMessage);
        }
    },
    { immediate: true }
);

const submitForm = () => {
    // Clear previous errors
    form.clearErrors();

    form.post(route('tenant.register'), {
        onSuccess: () => {
            // Success - watcher will handle redirect if tenant_redirect_url is present
            form.reset();
            showModal.value = false;
        },
        onError: (errors) => {
            // Handle validation errors
            if (errors.store_name) {
                error(errors.store_name);
            } else if (errors.subdomain) {
                error(errors.subdomain);
            } else if (errors.email) {
                error(errors.email);
            } else if (errors.admin_name) {
                error(errors.admin_name);
            } else if (errors.password) {
                error(errors.password);
            } else if (errors.password_confirmation) {
                error(errors.password_confirmation);
            } else {
                error('Something went wrong. Please try again.');
            }
        },
    });
};

const setLanguage = (lang) => {
    setLocale(lang);
};
</script>
