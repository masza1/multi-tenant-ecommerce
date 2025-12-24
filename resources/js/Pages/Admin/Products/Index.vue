<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <a href="/" class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold">S</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">Admin Toko</span>
                    </a>
                </div>
            </nav>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ trans('messages.manage_products') }}</h1>
                <a :href="route('products.create')" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                    + {{ trans('messages.add_product') }}
                </a>
            </div>

            <div v-if="!products || products.data.length === 0" class="text-center py-20 bg-white rounded-lg">
                <p class="text-gray-600 mb-4">{{ trans('messages.no_products_yet') }}</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.product') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.price') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.stock') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.status') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img v-if="product.image_url" :src="product.image_url" class="w-12 h-12 rounded object-cover mr-3" />
                                    <div>
                                        <div class="font-medium text-gray-900">{{ product.name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-900">
                                Rp {{ formatPrice(product.price) }}
                            </td>
                            <td class="px-6 py-4 text-gray-900">
                                {{ product.stock }}
                            </td>
                            <td class="px-6 py-4">
                                <span :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ product.is_active ? trans('messages.active') : trans('messages.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a :href="route('products.edit', product.id)" class="text-blue-600 hover:text-blue-900 mr-4">
                                    {{ trans('messages.edit') }}
                                </a>
                                <form :action="route('products.destroy', product.id)" method="delete" class="inline">
                                    <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                    <button type="submit" :onclick="`return confirm('${trans('messages.confirm_delete')}')`" class="text-red-600 hover:text-red-900">
                                        {{ trans('messages.delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</template>

<script setup>
import { useI18n } from '@/composables/useI18n';

const { trans } = useI18n();

defineProps({
    products: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID').format(price);
};
</script>
