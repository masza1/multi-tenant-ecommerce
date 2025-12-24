import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Global reactive locale state
const globalLocale = ref(localStorage.getItem('app_locale') || 'en');

/**
 * Simple i18n composable for accessing translations
 * Uses Laravel's translation system via backend
 * Primary language: English (en)
 * Fallback language: English (en)
 */
export function useI18n() {
    const page = usePage();

    /**
     * Complete translation mapping for English and Indonesian
     */
    const translations = {
        en: {
            'messages.home': 'Home',
            'messages.cart': 'Cart',
            'messages.admin': 'Admin',
            'messages.logout': 'Logout',
            'messages.login': 'Login',
            'messages.register': 'Register',
            'messages.my_store': 'My Store',
            'messages.store': 'Store',
            'messages.create_store_free': 'Create Your Store For Free',
            'messages.multitenant_platform': 'Multi-tenant e-commerce platform with separate database per store',
            'messages.get_started': 'Get Started Now - Free!',
            'messages.why_choose_us': 'Why Choose Us?',
            'messages.separate_databases': 'Separate Databases',
            'messages.database_per_store': 'Each store has its own database. Your data is safe and isolated.',
            'messages.easy_quick': 'Easy & Fast',
            'messages.setup_minutes': 'Set up your store in minutes. No technical skills required.',
            'messages.free_forever': 'Free Forever',
            'messages.no_hidden_fees': 'No hidden fees. 100% free for all basic features.',
            'messages.register_new_store': 'Register New Store',
            'messages.store_name': 'Store Name',
            'messages.example_store_name': 'e.g., Awesome Shoe Store',
            'messages.subdomain': 'Subdomain',
            'messages.example_subdomain': 'e.g., awesome-shoes',
            'messages.store_available_at': 'Your store will be available at: [subdomain].localhost',
            'messages.owner_email': 'Owner Email',
            'messages.create_store': 'Create Store',
            'messages.cancel': 'Cancel',
            'messages.add_to_cart': 'Add to Cart',
            'messages.shopping_cart': 'Shopping Cart',
            'messages.checkout': 'Checkout',
            'messages.admin_dashboard': 'Admin Dashboard',
            'messages.manage_products': 'Manage Products',
            'messages.create_product': 'Create Product',
            'messages.edit_product': 'Edit Product',
            'messages.delete_product': 'Delete Product',
            'messages.price': 'Price',
            'messages.stock': 'Stock',
            'messages.total': 'Total',
            'messages.submit': 'Submit',
            'messages.back': 'Go Back',
            'messages.not_found': 'Not found.',
            'messages.access_denied': 'Access denied.',
            'messages.something_went_wrong': 'Something went wrong. Please try again.',
        },
        id: {
            'messages.home': 'Beranda',
            'messages.cart': 'Keranjang',
            'messages.admin': 'Admin',
            'messages.logout': 'Logout',
            'messages.login': 'Login',
            'messages.register': 'Daftar',
            'messages.my_store': 'Toko Saya',
            'messages.store': 'Toko',
            'messages.create_store_free': 'Buat Toko Online Anda Gratis',
            'messages.multitenant_platform': 'Platform multi-tenant dengan database terpisah untuk setiap toko',
            'messages.get_started': 'Mulai Sekarang - Gratis!',
            'messages.why_choose_us': 'Kenapa Pilih Kami?',
            'messages.separate_databases': 'Database Terpisah',
            'messages.database_per_store': 'Setiap toko memiliki database sendiri. Data Anda aman dan terisolasi.',
            'messages.easy_quick': 'Mudah & Cepat',
            'messages.setup_minutes': 'Setup toko dalam hitungan menit. Tidak perlu skill teknis.',
            'messages.free_forever': 'Gratis Selamanya',
            'messages.no_hidden_fees': 'Tidak ada biaya tersembunyi. 100% gratis untuk semua fitur dasar.',
            'messages.register_new_store': 'Daftar Toko Baru',
            'messages.store_name': 'Nama Toko',
            'messages.example_store_name': 'Contoh: Toko Sepatu Keren',
            'messages.subdomain': 'Subdomain',
            'messages.example_subdomain': 'Contoh: toko-sepatu',
            'messages.store_available_at': 'Toko Anda akan tersedia di: [subdomain].localhost',
            'messages.owner_email': 'Email Pemilik',
            'messages.create_store': 'Buat Toko',
            'messages.cancel': 'Batal',
            'messages.add_to_cart': 'Tambah ke Keranjang',
            'messages.shopping_cart': 'Keranjang Belanja',
            'messages.checkout': 'Checkout',
            'messages.admin_dashboard': 'Dashboard Admin',
            'messages.manage_products': 'Kelola Produk',
            'messages.create_product': 'Buat Produk',
            'messages.edit_product': 'Edit Produk',
            'messages.delete_product': 'Hapus Produk',
            'messages.price': 'Harga',
            'messages.stock': 'Stok',
            'messages.total': 'Total',
            'messages.submit': 'Kirim',
            'messages.back': 'Kembali',
            'messages.not_found': 'Tidak ditemukan.',
            'messages.access_denied': 'Akses ditolak.',
            'messages.something_went_wrong': 'Terjadi kesalahan. Silakan coba lagi.',
        },
    };

    /**
     * Get translation string
     * @param {string} key - Translation key (e.g., 'messages.login')
     * @param {object} replacements - Values to replace in translation
     * @returns {string} Translated string
     */
    const trans = (key, replacements = {}) => {
        const appLocale = globalLocale.value || 'en';
        let translation = translations[appLocale]?.[key] || translations.en[key] || key;

        // Replace placeholders with provided values
        Object.entries(replacements).forEach(([placeholder, value]) => {
            translation = translation.replace(`:${placeholder}`, value);
        });

        return translation;
    };

    /**
     * Get current application locale
     * @returns {string} Current locale code (e.g., 'en', 'id')
     */
    const getLocale = () => {
        return globalLocale.value || 'en';
    };

    /**
     * Set application locale
     * @param {string} locale - Locale code to set
     */
    const setLocale = (locale) => {
        globalLocale.value = locale;
        localStorage.setItem('app_locale', locale);
    };

    return {
        trans,
        getLocale,
        setLocale,
        globalLocale,
    };
}

export default useI18n;
