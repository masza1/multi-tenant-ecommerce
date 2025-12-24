<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50">
        <AppHeader />
        <Toast :toasts="toasts" @remove="removeToast" />

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-2">{{ trans('messages.catalog') }}</h1>
                <p class="text-gray-600 text-lg">{{ trans('messages.no_products_added_yet') }}</p>
            </div>

            <!-- Empty State -->
            <div v-if="!products || products.data.length === 0" class="bg-white rounded-2xl shadow-lg p-16 text-center">
                <svg class="w-32 h-32 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h3 class="text-2xl font-semibold text-gray-900 mb-2">{{ trans('messages.no_products') }}</h3>
                <p class="text-gray-600 text-lg">{{ trans('messages.no_products_added_yet') }}</p>
            </div>

            <!-- Product Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div v-for="product in products.data" :key="product.id" class="bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden group">
                    <!-- Image -->
                    <div class="aspect-square bg-gray-100 overflow-hidden relative">
                        <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0l4.586 4.586m-6-2l1.414-1.414a2 2 0 012.828 0l2.172 2.172"></path>
                            </svg>
                        </div>

                        <!-- Stock Badge -->
                        <div v-if="product.stock === 0" class="absolute top-3 right-3 bg-red-500 text-white px-4 py-1 rounded-full text-sm font-semibold shadow-lg">
                            {{ trans('messages.out_of_stock') }}
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-5">
                        <h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-2 h-14">
                            {{ product.name }}
                        </h3>

                        <p v-if="product.description" class="text-gray-600 text-sm mb-4 line-clamp-2 h-10">
                            {{ product.description }}
                        </p>

                        <div class="flex items-center justify-between mb-4">
                            <span class="text-2xl font-bold text-blue-600">
                                Rp {{ formatPrice(product.price) }}
                            </span>
                        </div>

                        <div class="mb-4 flex items-center space-x-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm text-gray-600">{{ trans('messages.stock') }}: {{ product.stock }}</span>
                        </div>

                        <button v-if="$page.props.auth.user && product.stock > 0" @click="addToCart(product.id)" class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>{{ trans('messages.add_to_cart') }}</span>
                        </button>

                        <div v-else-if="$page.props.auth.user && product.stock === 0" class="w-full px-4 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg text-center">
                            {{ trans('messages.out_of_stock') }}
                        </div>

                        <div v-else class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg text-center">
                            {{ trans('messages.in_stock') }}
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import AppHeader from '@/Components/AppHeader.vue';
import Toast from '@/Components/Toast.vue';
import { useI18n } from '@/composables/useI18n';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

const { trans, __ } = useI18n();
const { toast, toasts, removeToast } = useToast();

defineProps({
    products: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const addToCart = (productId) => {
    router.post(route('cart.store'), {
        product_id: productId,
        quantity: 1,
    }, {
        onSuccess: (page) => {
            if (page.props.flash?.error) {
                toast.error(page.props.flash.error);
            } else if (page.props.flash?.success) {
                toast.success(page.props.flash.success);
            }
        },
        onError: (error) => {
            if (error.error) {
                toast.error(error.error);
            } else {
                toast.error(__('messages.something_went_wrong'));
            }
        }
    });
};
</script>
