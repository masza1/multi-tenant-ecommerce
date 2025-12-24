<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50">
        <TenantHeader />

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-12">{{ trans('messages.shopping_cart') }}</h1>

            <div v-if="!cartItems || cartItems.length === 0" class="bg-white rounded-2xl shadow-lg p-16 text-center">
                <svg class="w-32 h-32 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h3 class="text-2xl font-semibold text-gray-900 mb-2">{{ trans('messages.cart_empty') }}</h3>
                <p class="text-gray-600 text-lg mb-8">{{ trans('messages.no_products_in_cart') }}</p>
                <a :href="route('shop.index')" class="inline-block px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                    {{ trans('messages.continue_shopping') }}
                </a>
            </div>

            <div v-else>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">{{ trans('messages.product') }}</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">{{ trans('messages.price') }}</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">{{ trans('messages.qty') }}</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">{{ trans('messages.subtotal') }}</th>
                                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">{{ trans('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="item in cartItems" :key="item.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <img v-if="item.product.image_url" :src="item.product.image_url" :alt="item.product.name" class="w-12 h-12 rounded-lg object-cover" />
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900">{{ item.product.name }}</div>
                                                <div class="text-xs text-gray-500">SKU: {{ item.product.sku }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        Rp {{ formatPrice(item.product.price) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <form :action="route('cart.update', item.id)" method="patch" class="inline flex items-center space-x-2">
                                            <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                            <input type="number" name="quantity" :value="item.quantity" min="1" class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-center focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                            <button type="submit" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-medium transition">{{ trans('messages.update') }}</button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                        Rp {{ formatPrice(item.subtotal) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form :action="route('cart.destroy', item.id)" method="delete" class="inline">
                                            <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium transition">{{ trans('messages.remove') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-6 bg-white rounded-2xl shadow-lg p-8">
                    <div>
                        <p class="text-gray-600 text-lg mb-2">{{ trans('messages.total') }}</p>
                        <p class="text-4xl font-bold text-blue-600">Rp {{ formatPrice(total) }}</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a :href="route('shop.index')" class="px-8 py-3 border-2 border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold rounded-lg transition text-center">
                            {{ trans('messages.continue_shopping') }}
                        </a>
                        <button class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                            {{ trans('messages.checkout') }}
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import TenantHeader from '@/Components/TenantHeader.vue';
import { useI18n } from '@/composables/useI18n';

const { trans } = useI18n();

defineProps({
    cartItems: Array,
    total: Number,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID').format(price);
};
</script>
