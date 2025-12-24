<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50">
        <header class="sticky top-0 z-40 bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <a href="/" class="text-2xl font-bold text-blue-600">StoreHub</a>
                    <div v-if="$page.props.auth.user" class="flex items-center space-x-4">
                        <span class="text-gray-700 font-medium">{{ $page.props.auth.user.name }}</span>
                        <form @submit.prevent="logout" class="inline">
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                                {{ trans('messages.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900">
                    {{ product ? trans('messages.edit_product') : trans('messages.add_product') }}
                </h1>
                <p class="text-gray-600 text-lg mt-2">{{ product ? trans('messages.edit_product') : trans('messages.create_product') }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12">
                <form @submit.prevent="submit">
                    <div class="space-y-8">
                        <!-- SKU & Name Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="sku" class="block text-sm font-semibold text-gray-900 mb-2">
                                    {{ trans('messages.sku') }} <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="sku"
                                    v-model="form.sku"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    :class="{ 'border-red-500 focus:ring-red-500': errors.sku }"
                                    placeholder="e.g., PROD-001"
                                    required
                                />
                                <p v-if="errors.sku" class="mt-2 text-sm text-red-600">{{ errors.sku }}</p>
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">
                                    {{ trans('messages.product_name') }} <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    :class="{ 'border-red-500 focus:ring-red-500': errors.name }"
                                    required
                                />
                                <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">
                                {{ trans('messages.description') }}
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                :class="{ 'border-red-500 focus:ring-red-500': errors.description }"
                            ></textarea>
                            <p v-if="errors.description" class="mt-2 text-sm text-red-600">{{ errors.description }}</p>
                        </div>

                        <!-- Price & Stock Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="price" class="block text-sm font-semibold text-gray-900 mb-2">
                                    {{ trans('messages.price') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center">
                                    <span class="text-gray-600 font-medium mr-3">Rp</span>
                                    <input
                                        id="price"
                                        v-model.number="form.price"
                                        type="number"
                                        step="0.01"
                                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                        :class="{ 'border-red-500 focus:ring-red-500': errors.price }"
                                        required
                                    />
                                </div>
                                <p v-if="errors.price" class="mt-2 text-sm text-red-600">{{ errors.price }}</p>
                            </div>

                            <div>
                                <label for="stock" class="block text-sm font-semibold text-gray-900 mb-2">
                                    {{ trans('messages.stock') }} <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="stock"
                                    v-model.number="form.stock"
                                    type="number"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    :class="{ 'border-red-500 focus:ring-red-500': errors.stock }"
                                    required
                                />
                                <p v-if="errors.stock" class="mt-2 text-sm text-red-600">{{ errors.stock }}</p>
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label for="image" class="block text-sm font-semibold text-gray-900 mb-2">
                                {{ trans('messages.image') }}
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 hover:bg-blue-50 transition">
                                <input
                                    id="image"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleImageChange"
                                />
                                <label v-if="!imagePreview" for="image" class="cursor-pointer">
                                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-3" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-8l-3.172-3.172a4 4 0 00-5.656 0L28 20M9 20l3.172-3.172a4 4 0 015.656 0L28 20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <p class="text-gray-700 font-medium text-lg">{{ trans('messages.click_to_upload') }}</p>
                                    <p class="text-gray-500 text-sm mt-1">PNG, JPG, GIF up to 2MB</p>
                                </label>
                            </div>
                            <p v-if="errors.image" class="mt-2 text-sm text-red-600">{{ errors.image }}</p>
                            <div v-if="imagePreview" class="mt-6">
                                <div class="relative inline-block">
                                    <img :src="imagePreview" alt="Preview" class="w-40 h-40 object-cover rounded-xl shadow-lg" />
                                    <button
                                        type="button"
                                        @click="removeImage"
                                        class="absolute -top-3 -right-3 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg transition"
                                    >
                                        ×
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center p-5 bg-blue-50 rounded-xl border border-blue-200">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="w-5 h-5 text-blue-600 focus:ring-2 focus:ring-blue-500 border-gray-300 rounded cursor-pointer"
                            />
                            <label for="is_active" class="ml-4 text-sm font-semibold text-gray-900 cursor-pointer">
                                {{ trans('messages.active') }}
                            </label>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-12 flex justify-end gap-4 pt-8 border-t border-gray-200">
                        <a
                            href="/admin"
                            class="px-8 py-3 border-2 border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold rounded-lg transition"
                        >
                            {{ trans('messages.cancel') }}
                        </a>
                        <button
                            type="submit"
                            :disabled="processing"
                            class="px-8 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition"
                        >
                            {{ processing ? trans('messages.saving') : trans('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from '@/composables/useI18n';
import { router } from '@inertiajs/vue3';

const { trans } = useI18n();

const props = defineProps({
    product: Object,
});

const processing = ref(false);
const errors = ref({});
const imagePreview = ref(null);

const form = ref({
    sku: props.product?.sku || '',
    name: props.product?.name || '',
    description: props.product?.description || '',
    price: props.product?.price || '',
    stock: props.product?.stock || '',
    image: null,
    is_active: props.product?.is_active !== false,
});

if (props.product?.image_url) {
    imagePreview.value = props.product.image_url;
}

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.value.image = file;
        const reader = new FileReader();
        reader.onload = (event) => {
            imagePreview.value = event.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.value.image = null;
    imagePreview.value = null;
};

const logout = () => {
    router.post('/logout');
};

const submit = () => {
    processing.value = true;
    errors.value = {};

    const data = {
        sku: form.value.sku,
        name: form.value.name,
        description: form.value.description,
        price: form.value.price,
        stock: form.value.stock,
        is_active: form.value.is_active,
        image: form.value.image,
    };

    if (props.product) {
        router.post(`/admin/products/${props.product.id}`, data, {
            onError: (pageErrors) => {
                processing.value = false;
                errors.value = pageErrors;
            },
        });
    } else {
        router.post('/admin/products', data, {
            onError: (pageErrors) => {
                processing.value = false;
                errors.value = pageErrors;
            },
        });
    }
};
</script>

