<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 flex flex-col">
        <!-- Toast Notifications -->
        <Toast :toasts="toasts" @remove="removeToast" />

        <!-- Header -->
        <header class="sticky top-0 z-40 bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                <a href="/" class="text-2xl font-bold text-blue-600">StoreHub</a>

                <div class="flex gap-2">
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

        <!-- Register Card -->
        <div class="flex-1 flex items-center justify-center px-4 py-8">
            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        {{ trans('messages.create_account') }}
                    </h1>
                    <p class="text-gray-600">
                        {{ trans('messages.join_us_today') }}
                    </p>
                </div>

                <!-- Register Form -->
                <form @submit.prevent="submitRegister" class="space-y-4">
                    <!-- Name Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ trans('messages.name') }}
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            :placeholder="trans('messages.enter_name')"
                            :class="[
                                'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition',
                                form.errors.name
                                    ? 'border-red-500 focus:ring-red-500 bg-red-50'
                                    : 'border-gray-300 focus:ring-blue-500'
                            ]"
                        />
                        <span v-if="form.errors.name" class="text-red-600 text-sm block mt-1">
                            ⚠️ {{ form.errors.name }}
                        </span>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ trans('messages.email') }}
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            :placeholder="trans('messages.enter_email')"
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

                    <!-- Password Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ trans('messages.password') }}
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            :placeholder="trans('messages.min_8_characters')"
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

                    <!-- Password Confirmation Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ trans('messages.password_confirmation') }}
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            :placeholder="trans('messages.confirm_password_placeholder')"
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

                    <!-- Note about first user -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-xs text-blue-700">
                            💡 {{ trans('messages.first_user_becomes_admin') }}
                        </p>
                    </div>

                    <!-- Register Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? trans('messages.loading') : trans('messages.register') }}
                    </button>
                </form>

                <!-- Login Link -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-center text-sm text-gray-600">
                        {{ trans('messages.already_have_account') }}
                        <Link
                            :href="route('login')"
                            class="text-blue-600 hover:text-blue-700 font-semibold transition"
                        >
                            {{ trans('messages.login_here') }}
                        </Link>
                    </p>
                </div>
            </div>
        </div>

        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';
import { useFlashToast } from '@/composables/useFlashToast';
import Toast from '@/Components/Toast.vue';

const { trans, setLocale, globalLocale } = useI18n();
const { toasts, removeToast, error } = useFlashToast();
const currentLang = computed(() => globalLocale.value || 'en');

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submitRegister = () => {
    form.clearErrors();
    form.post(route('register'), {
        onError: (errors) => {
            if (errors.name) error(errors.name);
            else if (errors.email) error(errors.email);
            else if (errors.password) error(errors.password);
            else if (errors.password_confirmation) error(errors.password_confirmation);
            else error(trans('messages.registration_failed'));
        },
    });
};

const setLanguage = (lang) => {
    setLocale(lang);
};
</script>

<style scoped>
@keyframes blob {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}
</style>
