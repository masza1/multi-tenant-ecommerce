<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-50">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold">S</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">Toko</span>
                    </div>

                    <div class="hidden md:flex items-center space-x-6">
                        <a href="#" class="text-gray-700 hover:text-blue-600 font-medium">Beranda</a>
                        <a v-if="$page.props.auth.user" :href="route('cart.index')" class="text-gray-700 hover:text-blue-600 font-medium relative">
                            Keranjang
                            <span v-if="$page.props.auth.cartCount > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
                                {{ $page.props.auth.cartCount }}
                            </span>
                        </a>

                        <template v-if="$page.props.auth.user">
                            <a v-if="$page.props.auth.user.role === 'admin'" :href="route('products.index')" class="text-gray-700 hover:text-blue-600 font-medium">
                                Admin
                            </a>
                            <form method="post" :action="route('logout')" class="inline">
                                <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg">
                                    Logout
                                </button>
                            </form>
                        </template>

                        <template v-else>
                            <a :href="route('login')" class="text-gray-700 hover:text-blue-600 font-medium">Login</a>
                            <a :href="route('register')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">Daftar</a>
                        </template>
                    </div>
                </div>
            </nav>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Katalog Produk</h1>

            <!-- Empty State -->
            <div v-if="!products || products.data.length === 0" class="text-center py-20">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Produk</h3>
                <p class="text-gray-500">Toko ini belum menambahkan produk apapun.</p>
            </div>

            <!-- Product Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div v-for="product in products.data" :key="product.id" class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <!-- Image -->
                    <div class="aspect-square bg-gray-100 overflow-hidden relative">
                        <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0l4.586 4.586m-6-2l1.414-1.414a2 2 0 012.828 0l2.172 2.172"></path>
                            </svg>
                        </div>

                        <!-- Stock Badge -->
                        <div v-if="product.stock === 0" class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            Habis
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-2">
                            {{ product.name }}
                        </h3>

                        <p v-if="product.description" class="text-gray-600 text-sm mb-3 line-clamp-2">
                            {{ product.description }}
                        </p>

                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-blue-600">
                                Rp {{ formatPrice(product.price) }}
                            </span>

                            <form v-if="$page.props.auth.user && product.stock > 0" method="post" :action="route('cart.store')" class="inline">
                                <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                <input type="hidden" name="product_id" :value="product.id" />
                                <input type="hidden" name="quantity" value="1" />
                                <button type="submit" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>

                        <div class="mt-2 text-sm text-gray-500">
                            Stok: {{ product.stock }}
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
defineProps({
    products: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID').format(price);
};
</script>
