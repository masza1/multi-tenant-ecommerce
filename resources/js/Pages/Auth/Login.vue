<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 flex flex-col">
        <!-- Toast Notifications -->
        <Toast :toasts="toasts" @remove="removeToast" />

        <!-- Header -->
        <AppHeader />

        <!-- Login Card -->
        <div class="flex-1 flex items-center justify-center px-4 py-8">
            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        {{ trans('messages.welcome_back') }}
                    </h1>
                    <p class="text-gray-600">
                        {{ trans('messages.login_to_your_store') }}
                    </p>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submitLogin" class="space-y-4">
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
                            :placeholder="trans('messages.enter_password')"
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

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input
                            v-model="form.remember"
                            id="remember"
                            type="checkbox"
                            class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                        />
                        <label for="remember" class="ml-2 text-sm text-gray-700">
                            {{ trans('messages.remember_me') }}
                        </label>
                    </div>

                    <!-- Login Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? trans('messages.loading') : trans('messages.login') }}
                    </button>
                </form>

                <!-- Register Link -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-center text-sm text-gray-600">
                        {{ trans('messages.dont_have_account') }}
                        <Link
                            :href="route('register')"
                            class="text-blue-600 hover:text-blue-700 font-semibold transition"
                        >
                            {{ trans('messages.register_here') }}
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
import { onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';
import { useFlashToast } from '@/composables/useFlashToast';
import Toast from '@/Components/Toast.vue';
import AppHeader from '@/Components/AppHeader.vue';

const { trans } = useI18n();
const { toasts, removeToast, success, error } = useFlashToast();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// Check for success message in URL
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const successKey = urlParams.get('success');

    if (successKey) {
        const message = trans(`messages.${successKey}`);
        success(message);
        window.history.replaceState({}, '', window.location.pathname);
    }
});

const submitLogin = () => {
    form.clearErrors();
    form.post(route('login'), {
        onError: (errors) => {
            if (errors.email) error(errors.email);
            else if (errors.password) error(errors.password);
            else error(trans('messages.login_failed'));
        },
    });
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
