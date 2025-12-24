<template>
    <header class="sticky top-0 z-40 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <a href="/" class="text-2xl font-bold text-blue-600">StoreHub</a>

            <div class="flex gap-4 items-center">
                <!-- Language Buttons -->
                <div class="flex gap-2">
                    <button 
                        @click="setLocale('en')"
                        :class="['px-3 py-1 rounded font-medium transition', 
                            currentLang === 'en' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
                    >
                        EN
                    </button>
                    <button 
                        @click="setLocale('id')"
                        :class="['px-3 py-1 rounded font-medium transition', 
                            currentLang === 'id' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
                    >
                        ID
                    </button>
                </div>

                <template v-if="$page.props.auth.user">
                    <a v-if="$page.props.auth.user.role === 'admin' && hasRoute('admin.products.index')" :href="route('admin.products.index')" class="px-3 py-1 text-gray-700 hover:text-blue-600 font-medium transition">
                        {{ trans('messages.admin') }}
                    </a>

                    <a v-if="hasRoute('cart.index')" :href="route('cart.index')" class="px-3 py-1 text-gray-700 hover:text-blue-600 font-medium relative transition">
                        {{ trans('messages.cart') }}
                        <span v-if="$page.props.auth.cartCount > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                            {{ $page.props.auth.cartCount }}
                        </span>
                    </a>

                    <button @click="logout" class="px-3 py-1 rounded font-medium bg-red-600 text-white hover:bg-red-700 transition">
                        {{ trans('messages.logout') }}
                    </button>
                </template>

                <template v-else>
                    <a v-if="hasRoute('login')" :href="route('login')" class="px-3 py-1 rounded font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                        {{ trans('messages.login') }}
                    </a>
                    <a v-if="hasRoute('register')" :href="route('register')" class="px-3 py-1 rounded font-medium bg-blue-600 text-white hover:bg-blue-700 transition">
                        {{ trans('messages.register') }}
                    </a>
                </template>
            </div>
        </div>
    </header>
</template>

<script setup>
import { useI18n } from '@/composables/useI18n';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { trans, setLocale, globalLocale } = useI18n();
const page = usePage();
const currentLang = computed(() => globalLocale.value || 'en');

const hasRoute = (name) => {
    try {
        route(name);
        return true;
    } catch {
        return false;
    }
};

const logout = () => {
    router.post('/logout');
};
</script>
