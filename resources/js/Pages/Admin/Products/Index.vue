<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50">
        <AppHeader />

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex justify-between items-center mb-12">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900">{{ trans('messages.manage_products') }}</h1>
                    <p class="text-gray-600 text-lg mt-2">{{ trans('messages.product_management') }}</p>
                </div>
                <a href="/admin/products/create" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg transition">
                    + {{ trans('messages.add_product') }}
                </a>
            </div>

            <div v-if="!products || products.data.length === 0" class="bg-white rounded-2xl shadow-lg p-16 text-center">
                <svg class="w-32 h-32 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h3 class="text-2xl font-semibold text-gray-900 mb-2">{{ trans('messages.no_products_yet') }}</h3>
                <p class="text-gray-600 text-lg">{{ trans('messages.no_products_yet') }}</p>
            </div>

            <div v-else class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">{{ trans('messages.sku') }}</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">{{ trans('messages.product') }}</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">{{ trans('messages.price') }}</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">{{ trans('messages.stock') }}</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">{{ trans('messages.status') }}</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">{{ trans('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-mono text-gray-700 bg-gray-100 px-3 py-1 rounded">{{ product.sku }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <img v-if="product.image_url" :src="product.image_url" class="w-12 h-12 rounded-lg object-cover" />
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ product.name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-900 font-medium">
                                    Rp {{ formatPrice(product.price) }}
                                </td>
                                <td class="px-6 py-4 text-gray-900">
                                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                        {{ product.stock }} {{ trans('messages.stock') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-block px-3 py-1 rounded-full text-sm font-semibold">
                                        {{ product.is_active ? trans('messages.active') : trans('messages.inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a :href="route('products.edit', product.id)" class="text-blue-600 hover:text-blue-900 font-medium transition">
                                            {{ trans('messages.edit') }}
                                        </a>
                                        <form :action="route('products.destroy', product.id)" method="delete" class="inline">
                                            <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                            <button type="submit" :onclick="`return confirm('${trans('messages.confirm_delete')}')`" class="text-red-600 hover:text-red-900 font-medium transition">
                                                {{ trans('messages.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { useI18n } from '@/composables/useI18n';
import AppHeader from '@/Components/AppHeader.vue';

const { trans } = useI18n();

defineProps({
    products: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID').format(price);
};
</script>
