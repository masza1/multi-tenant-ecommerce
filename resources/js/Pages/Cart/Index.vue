<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <a :href="route('shop.index')" class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold">S</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">Toko</span>
                    </a>
                    <form method="post" :action="route('logout')" class="inline">
                        <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg">
                            Logout
                        </button>
                    </form>
                </div>
            </nav>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Keranjang Belanja</h1>

            <div v-if="!cartItems || cartItems.length === 0" class="text-center py-20">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Keranjang Kosong</h3>
                <p class="text-gray-500 mb-4">Belum ada produk di keranjang Anda.</p>
                <a :href="route('shop.index')" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                    Lanjut Belanja
                </a>
            </div>

            <div v-else class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="item in cartItems" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ item.product.name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                Rp {{ formatPrice(item.product.price) }}
                            </td>
                            <td class="px-6 py-4">
                                <form :action="route('cart.update', item.id)" method="patch" class="inline flex items-center space-x-2">
                                    <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                    <input type="number" name="quantity" :value="item.quantity" min="1" class="w-16 px-2 py-1 border border-gray-300 rounded" />
                                    <button type="submit" class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm">Update</button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                Rp {{ formatPrice(item.subtotal) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form :action="route('cart.destroy', item.id)" method="delete" class="inline">
                                    <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="px-6 py-4 bg-gray-50 border-t">
                    <div class="flex justify-end items-center space-x-4">
                        <span class="text-lg font-semibold text-gray-900">
                            Total: Rp {{ formatPrice(total) }}
                        </span>
                        <button class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                            Checkout
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
defineProps({
    cartItems: Array,
    total: Number,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID').format(price);
};
</script>
