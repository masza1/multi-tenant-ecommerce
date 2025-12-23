<template>
    <form @submit.prevent="submitForm" class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-6 sm:p-8">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Category -->
                <div class="sm:col-span-2">
                    <label for="category_id" class="block text-sm font-medium leading-6 text-gray-900">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="category_id"
                        v-model="form.category_id"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    >
                        <option value="">Select a category</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                    <p v-if="errors.category_id" class="mt-2 text-sm text-red-600">{{ errors.category_id }}</p>
                </div>

                <!-- Product Name -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium leading-6 text-gray-900">
                        Product Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        placeholder="e.g. Premium Grinding Mill"
                        @input="generateSlug"
                    />
                    <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
                </div>

                <!-- Slug -->
                <div class="sm:col-span-2">
                    <label for="slug" class="block text-sm font-medium leading-6 text-gray-900">
                        Slug <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="slug"
                        v-model="form.slug"
                        type="text"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        placeholder="premium-grinding-mill"
                    />
                    <p class="mt-1 text-xs text-gray-500">URL-friendly product identifier</p>
                    <p v-if="errors.slug" class="mt-2 text-sm text-red-600">{{ errors.slug }}</p>
                </div>

                <!-- SKU -->
                <div>
                    <label for="sku" class="block text-sm font-medium leading-6 text-gray-900">
                        SKU <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="sku"
                        v-model="form.sku"
                        type="text"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        placeholder="e.g. SKU-12345"
                    />
                    <p v-if="errors.sku" class="mt-2 text-sm text-red-600">{{ errors.sku }}</p>
                </div>

                <!-- Stock -->
                <div>
                    <label for="stock" class="block text-sm font-medium leading-6 text-gray-900">
                        Stock Quantity <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="stock"
                        v-model.number="form.stock"
                        type="number"
                        min="0"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                    <p v-if="errors.stock" class="mt-2 text-sm text-red-600">{{ errors.stock }}</p>
                </div>

                <!-- Low Stock Threshold -->
                <div>
                    <label for="low_stock_threshold" class="block text-sm font-medium leading-6 text-gray-900">
                        Low Stock Threshold <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="low_stock_threshold"
                        v-model.number="form.low_stock_threshold"
                        type="number"
                        min="0"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                    <p class="mt-1 text-xs text-gray-500">Alert when stock falls below this number</p>
                </div>

                <!-- Price -->
                <div>
                    <label for="price" class="block text-sm font-medium leading-6 text-gray-900">
                        Sale Price <span class="text-red-500">*</span>
                    </label>
                    <div class="relative mt-2">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                        <input
                            id="price"
                            v-model.number="form.price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="pl-7 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        />
                    </div>
                    <p v-if="errors.price" class="mt-2 text-sm text-red-600">{{ errors.price }}</p>
                </div>

                <!-- Original Price -->
                <div>
                    <label for="original_price" class="block text-sm font-medium leading-6 text-gray-900">
                        Original Price (Optional)
                    </label>
                    <div class="relative mt-2">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                        <input
                            id="original_price"
                            v-model.number="form.original_price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="pl-7 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        />
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Leave blank if no discount</p>
                    <p v-if="errors.original_price" class="mt-2 text-sm text-red-600">{{ errors.original_price }}</p>
                </div>
            </div>

            <!-- Descriptions -->
            <div class="mt-6 grid grid-cols-1 gap-6">
                <!-- Short Description -->
                <div>
                    <label for="short_description" class="block text-sm font-medium leading-6 text-gray-900">
                        Short Description (Optional)
                    </label>
                    <textarea
                        id="short_description"
                        v-model="form.short_description"
                        rows="2"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        placeholder="Brief product description for listing"
                    />
                    <p class="mt-1 text-xs text-gray-500">Max 500 characters</p>
                </div>

                <!-- Full Description -->
                <div>
                    <label for="description" class="block text-sm font-medium leading-6 text-gray-900">
                        Full Description (Optional)
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        placeholder="Detailed product description, specifications, features, etc."
                    />
                </div>
            </div>

            <!-- Images -->
            <div class="mt-6">
                <label class="block text-sm font-medium leading-6 text-gray-900 mb-4">
                    Product Images (Optional)
                </label>
                <div class="space-y-4">
                    <div
                        v-for="(image, index) in form.images"
                        :key="index"
                        class="p-4 border border-gray-200 rounded-lg"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <label :for="`image_url_${index}`" class="block text-xs font-medium leading-6 text-gray-900">
                                    Image URL <span class="text-red-500">*</span>
                                </label>
                                <input
                                    :id="`image_url_${index}`"
                                    v-model="image.image_url"
                                    type="url"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    placeholder="https://example.com/image.jpg"
                                />
                            </div>
                            <button
                                type="button"
                                @click="removeImage(index)"
                                class="ml-4 text-red-600 hover:text-red-700 font-semibold text-sm"
                            >
                                Remove
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label :for="`alt_text_${index}`" class="block text-xs font-medium leading-6 text-gray-900">
                                    Alt Text
                                </label>
                                <input
                                    :id="`alt_text_${index}`"
                                    v-model="image.alt_text"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    placeholder="Image description"
                                />
                            </div>
                            <div>
                                <label class="flex items-center mt-6">
                                    <input
                                        v-model="image.is_primary"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                    <span class="ml-2 text-sm text-gray-600">Primary image</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="addImage"
                        class="mt-4 inline-flex items-center rounded-md bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100"
                    >
                        + Add Image
                    </button>
                </div>
            </div>

            <!-- Active Status -->
            <div class="mt-6">
                <label class="flex items-center">
                    <input
                        v-model="form.active"
                        type="checkbox"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                    <span class="ml-2 text-sm font-medium text-gray-900">Active (visible in store)</span>
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between border-t border-gray-200 px-4 py-4 sm:px-8">
            <p v-if="processing" class="text-sm text-gray-500">Saving...</p>
            <div class="flex gap-3 ml-auto">
                <Link
                    href="/products"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    :disabled="processing"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                >
                    {{ product ? 'Update Product' : 'Create Product' }}
                </button>
            </div>
        </div>
    </form>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    categories: Array,
    product: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['submit']);

const form = ref({
    category_id: props.product?.category_id || '',
    name: props.product?.name || '',
    slug: props.product?.slug || '',
    description: props.product?.description || '',
    short_description: props.product?.short_description || '',
    price: props.product?.price || '',
    original_price: props.product?.original_price || '',
    sku: props.product?.sku || '',
    stock: props.product?.stock || 0,
    low_stock_threshold: props.product?.low_stock_threshold || 5,
    active: props.product?.active ?? true,
    images: props.product?.images || [],
});

const processing = ref(false);
const errors = usePage().props.errors || {};

const generateSlug = () => {
    form.value.slug = form.value.name
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
};

const addImage = () => {
    form.value.images.push({
        image_url: '',
        alt_text: '',
        is_primary: false,
    });
};

const removeImage = (index) => {
    form.value.images.splice(index, 1);
};

const submitForm = () => {
    processing.value = true;
    emit('submit', form.value);
};

watch(
    () => props.product,
    (newProduct) => {
        if (newProduct) {
            form.value = {
                category_id: newProduct.category_id,
                name: newProduct.name,
                slug: newProduct.slug,
                description: newProduct.description,
                short_description: newProduct.short_description,
                price: newProduct.price,
                original_price: newProduct.original_price,
                sku: newProduct.sku,
                stock: newProduct.stock,
                low_stock_threshold: newProduct.low_stock_threshold,
                active: newProduct.active,
                images: newProduct.images,
            };
        }
    }
);
</script>
