# Getting Started - Multi-Tenant eCommerce Platform

Panduan lengkap untuk menggunakan Multi-Tenant eCommerce Platform dari awal sampai akhir.

## 📋 Daftar Isi

1. [Setup Awal](#-setup-awal)
2. [Konfigurasi Database](#-konfigurasi-database)
3. [Menjalankan Aplikasi](#-menjalankan-aplikasi)
4. [Membuat Tenant Pertama](#-membuat-tenant-pertama)
5. [Membuat Admin & User](#-membuat-admin--user)
6. [Mengelola Produk](#-mengelola-produk)
7. [Testing Shopping Cart](#-testing-shopping-cart)
8. [Menjalankan Test Suite](#-menjalankan-test-suite)
9. [Troubleshooting](#-troubleshooting)

---

## 🚀 Setup Awal

### Langkah 1: Install Dependencies

```bash
# Navigasi ke project directory
cd MultiTenantDB

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install --legacy-peer-deps
```

### Langkah 2: Generate Application Key

```bash
# Copy environment template
cp .env.example .env

# Generate encryption key
php artisan key:generate
```

### Langkah 3: Edit .env File

Buka `.env` dan sesuaikan:

```env
APP_NAME="Multi-Tenant eCommerce"
APP_URL=http://localhost

# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=54322
DB_DATABASE=multitenant_central
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
```

---

## 🗄️ Konfigurasi Database

### Prasyarat
PostgreSQL harus running di port 54322

### Langkah 1: Buat Database Central

```bash
# Buat database untuk central metadata
createdb -h localhost -p 54322 -U postgres multitenant_central

# Buat database untuk testing
createdb -h localhost -p 54322 -U postgres multitenant_test
```

### Langkah 2: Run Migrations

```bash
# Migrate central database
php artisan migrate

# Output seharusnya menunjukkan:
# Migrated: 0001_01_01_000000_create_users_table
# Migrated: 0001_01_01_000001_create_cache_table
# Migrated: 2019_09_15_000010_create_tenants_table
# ... etc
```

### Langkah 3: Verifikasi Database

```bash
# Login ke PostgreSQL
psql -h localhost -p 54322 -U postgres -d multitenant_central

# Check tables
\dt

# Should see: tenants, domains, users, migrations tables

# Exit
\q
```

---

## ▶️ Menjalankan Aplikasi

### Terminal 1: Start Laravel Server

```bash
# Development server
php artisan serve

# Aplikasi akan accessible di: http://localhost:8000
```

### Terminal 2: Watch Frontend Assets

```bash
# Rebuild CSS/JS setiap kali ada perubahan
npm run dev

# Atau build untuk production
npm run build
```

### Akses URL Awal

- Main App: `http://localhost:8000`
- Login: `http://localhost:8000/login`
- Register: `http://localhost:8000/register`

---

## 👥 Membuat Tenant Pertama

Tenant adalah instance/toko terpisah yang akan memiliki database sendiri.

### Metode 1: Via Laravel Tinker (Recommended untuk development)

```bash
# Buka interactive shell
php artisan tinker

# Buat tenant pertama
$tenant = App\Models\Tenant::create([
    'id' => 'store1',
    'data' => [
        'name' => 'Toko Online Pertama',
        'email' => 'store1@example.com',
    ],
]);

# Tambah domain untuk tenant
$tenant->addDomain('store1.localhost');

# Verifikasi
App\Models\Tenant::all();

# Exit tinker
exit
```

### Metode 2: Via Browser (Jika Admin Panel Tersedia)

1. Buka `http://localhost:8000/admin/tenants/create`
2. Isi form dengan:
   - **Tenant ID:** `store1`
   - **Name:** `Toko Online Pertama`
   - **Email:** `store1@example.com`
3. Klik "Create"
4. Sistem otomatis membuat database baru: `tenant_store1`

### Verifikasi Tenant Database Dibuat

```bash
# Check databases yang tersedia
psql -h localhost -p 54322 -U postgres -l | grep tenant

# Output seharusnya ada: tenant_store1
```

### Setup Host untuk Subdomain

Edit `/etc/hosts` (Mac/Linux) atau `C:\Windows\System32\drivers\etc\hosts` (Windows):

```
127.0.0.1 localhost
127.0.0.1 store1.localhost
127.0.0.1 store2.localhost
```

---

## 👤 Membuat Admin & User

### Buat User untuk Tenant

```bash
php artisan tinker

# Initialize tenant context
tenancy()->initialize(Tenant::find('store1'));

# Buat user baru
$user = App\Models\User::create([
    'name' => 'Admin Toko 1',
    'email' => 'admin@store1.com',
    'password' => bcrypt('password123'),
]);

# Verify user created
App\Models\User::all();

# End tenancy
tenancy()->end();

exit
```

### Login ke Tenant

1. Buka `http://store1.localhost:8000/login`
2. Gunakan credentials:
   - **Email:** `admin@store1.com`
   - **Password:** `password123`
3. Seharusnya redirect ke dashboard

---

## 📦 Mengelola Produk

### Membuat Produk Baru (Lewat Browser)

1. Login sebagai user tenant
2. Buka `http://store1.localhost:8000/products/create`
3. Isi form:

| Field | Contoh |
|-------|--------|
| **Category** | Elektronik |
| **Product Name** | Laptop Gaming |
| **Slug** | laptop-gaming |
| **Description** | Laptop berkualitas tinggi untuk gaming |
| **Short Description** | Laptop gaming performa tinggi |
| **Price** | 15000000 |
| **Original Price** | 20000000 |
| **SKU** | LAPTOP-001 |
| **Stock** | 50 |
| **Low Stock Threshold** | 5 |
| **Active** | ✓ (checked) |

4. Klik "Create Product"

### Membuat Produk via Tinker

```bash
php artisan tinker

# Initialize tenant
tenancy()->initialize(Tenant::find('store1'));

# Buat category
$category = App\Models\Category::create([
    'name' => 'Elektronik',
    'slug' => 'elektronik',
]);

# Buat product
$product = App\Models\Product::create([
    'category_id' => $category->id,
    'name' => 'Smartphone Flagship',
    'slug' => 'smartphone-flagship',
    'description' => 'Smartphone terbaru dengan teknologi terdepan',
    'short_description' => 'Smartphone flagship terbaru',
    'price' => 12000000,
    'original_price' => 15000000,
    'sku' => 'PHONE-001',
    'stock' => 100,
    'low_stock_threshold' => 10,
    'active' => true,
]);

# Verify
App\Models\Product::all();

tenancy()->end();
exit
```

### Melihat Produk

1. Buka `http://store1.localhost:8000/products`
2. List semua produk untuk tenant tersebut
3. Setiap tenant hanya melihat produk miliknya sendiri

---

## 🛒 Testing Shopping Cart

### Scenario 1: Guest User

1. Buka `http://store1.localhost:8000` (tanpa login)
2. Buka `http://store1.localhost:8000/products`
3. Lihat produk yang ada
4. Buka `http://store1.localhost:8000/cart`
5. Test menambah produk (via form atau API)

### Scenario 2: Login User

1. Login dengan user yang sudah dibuat
2. Buka products page
3. Tambah produk ke cart
4. Lihat cart summary dengan:
   - Subtotal
   - Tax (10%)
   - Shipping (gratis di atas $100)
   - Total

### Scenario 3: Guest to User Cart Merge

1. **Sebagai guest:**
   - Buka shopping cart
   - Tambah beberapa produk

2. **Kemudian register/login:**
   - Register user baru atau login
   - Cart guest otomatis merge ke user cart

3. **Verifikasi:**
   - Lihat cart sudah ada items yang ditambahkan saat guest

---

## 🧪 Menjalankan Test Suite

### Run Semua Tests

```bash
# Run all 77 tests
php artisan test

# Expected output:
# Tests: 77 passed (181 assertions)
```

### Run Tests Spesifik

```bash
# Tenant isolation tests
php artisan test tests/Unit/TenantIsolationTest.php

# Product tests
php artisan test tests/Feature/ProductTest.php

# Cart tests
php artisan test tests/Feature/CartTest.php

# Authentication tests
php artisan test tests/Feature/AuthenticationTest.php
```

### Run dengan Coverage

```bash
# Run dengan coverage report
php artisan test --coverage

# Coverage minimal 80% - seharusnya bisa mencapai 90%+
```

### Test Categories

#### Unit Tests (Data Isolation)
- Tenant memiliki database terpisah
- Tenant 1 tidak bisa akses data Tenant 2
- Domain identification bekerja

#### Feature Tests

**Products:**
- Create product dengan validation
- Update product
- Delete product
- Unique SKU & Slug
- Relationships (category, images)

**Cart:**
- Add to cart (guest & user)
- Update quantity
- Remove item
- Cart merge on login
- Stock validation
- Tax & shipping calculation

**Authentication:**
- Register user baru
- Login/logout
- Password reset
- Email verification
- Multi-guard auth

---

## 🔍 Membuat Tenant Kedua (Testing Isolation)

### Buat Tenant 2

```bash
php artisan tinker

# Buat tenant kedua
$tenant2 = App\Models\Tenant::create([
    'id' => 'store2',
    'data' => [
        'name' => 'Toko Online Kedua',
        'email' => 'store2@example.com',
    ],
]);

# Tambah domain
$tenant2->addDomain('store2.localhost');

exit
```

### Verifikasi Isolation

```bash
# Check database untuk tenant 2
psql -h localhost -p 54322 -U postgres -l | grep tenant

# Output: tenant_store1, tenant_store2 (terpisah!)
```

### Test Data Isolation

1. Login ke `store1.localhost:8000`
   - Buat beberapa produk
   - Lihat produk

2. Login ke `store2.localhost:8000`
   - Produk store1 TIDAK terlihat
   - Buat produk sendiri untuk store2
   - Hanya produk store2 yang terlihat

3. Kembali ke `store1.localhost:8000`
   - Produk store2 TIDAK terlihat
   - Hanya produk store1 yang ada

✅ **Data Isolation berhasil!**

---

## 🚨 Troubleshooting

### Problem: Database Connection Error

```
SQLSTATE[HY000] [2002] Connection refused
```

**Solution:**
```bash
# Verifikasi PostgreSQL running
psql -h localhost -p 54322 -U postgres

# Jika tidak terhubung, start PostgreSQL (Docker)
docker ps | grep postgres

# Atau verifikasi DB credentials di .env
```

### Problem: CSS/Styling Tidak Muncul

```bash
# Rebuild frontend assets
npm run build

# Atau development mode
npm run dev

# Clear browser cache
# Mac: Cmd+Shift+R
# Windows: Ctrl+Shift+R
```

### Problem: Tenant Database Tidak Dibuat

```bash
# Check if stancl/tenancy configured correctly
php artisan config:publish stancl/tenancy

# Check log files
tail -f storage/logs/laravel.log

# Manual create tenant database
createdb -h localhost -p 54322 -U postgres tenant_store1
```

### Problem: Tests Gagal

```bash
# Jika test database tidak ada
createdb -h localhost -p 54322 -U postgres multitenant_test

# Run tests
php artisan test

# Jika masih gagal, cek log
php artisan test --verbose
```

### Problem: Login Tidak Bekerja

1. **Verifikasi user ada di database:**
   ```bash
   php artisan tinker

   tenancy()->initialize(Tenant::find('store1'));
   App\Models\User::where('email', 'admin@store1.com')->first();
   tenancy()->end();
   exit
   ```

2. **Verifikasi password benar:**
   ```bash
   php artisan tinker

   $user = App\Models\User::find(1);
   Hash::check('password123', $user->password);  // Should return true
   exit
   ```

3. **Clear sessions:**
   ```bash
   php artisan cache:clear
   php artisan session:clear
   ```

---

## 📊 Project Structure Reference

```
resources/js/
├── Pages/
│   ├── Products/          # Product management
│   ├── Cart/              # Shopping cart
│   ├── Auth/              # Authentication
│   └── Profile/           # User profile
├── Components/            # Reusable components
└── Layouts/               # Page layouts

app/Http/
├── Controllers/
│   ├── Tenant/            # Tenant routes
│   ├── Auth/              # Auth controllers
│   └── Central/           # Central admin
├── Requests/              # Form validation
└── Resources/             # API responses

database/
├── migrations/            # Central DB migrations
├── migrations/tenant/     # Tenant DB migrations
└── factories/             # Test data

tests/
├── Feature/               # Feature tests
└── Unit/                  # Unit tests
```

---

## 🎯 Common Workflows

### Workflow 1: Setup Toko Baru

1. Create tenant via tinker/admin
2. Create admin user untuk tenant
3. Login sebagai admin
4. Create categories
5. Add products dengan images
6. Test shopping cart
7. Verify data isolation dengan tenant lain

### Workflow 2: Testing Feature

1. Create test tenant
2. Create test user
3. Create test products
4. Run specific test suite:
   ```bash
   php artisan test tests/Feature/CartTest.php
   ```
5. Check test output dan coverage

### Workflow 3: Development

1. Start `npm run dev` di terminal 1
2. Start `php artisan serve` di terminal 2
3. Make changes to Vue components atau PHP code
4. Browser auto-refresh atau manual refresh
5. Run tests sebelum commit:
   ```bash
   php artisan test
   ```

---

## ✅ Checklist Setup Lengkap

- [ ] PostgreSQL running di port 54322
- [ ] Composer dependencies installed
- [ ] NPM dependencies installed
- [ ] `.env` file configured
- [ ] Application key generated (`php artisan key:generate`)
- [ ] Databases created (multitenant_central, multitenant_test)
- [ ] Migrations ran (`php artisan migrate`)
- [ ] Frontend built (`npm run build`)
- [ ] Host entries added (/etc/hosts)
- [ ] Laravel server running (`php artisan serve`)
- [ ] Frontend watcher running (`npm run dev`)
- [ ] Can access `http://localhost:8000`
- [ ] Tests passing (`php artisan test`)

---

## 🚀 Next Steps

1. **Explore Admin Panel** - Lihat tenant management interface
2. **Create Multiple Stores** - Test data isolation dengan 2+ tenants
3. **Add More Features** - Implement orders, payments, etc
4. **Deploy** - Setup production environment
5. **Monitor** - Setup logging dan monitoring

---

## 📞 Need Help?

- Check logs: `storage/logs/laravel.log`
- Run tests with verbose: `php artisan test --verbose`
- Check Tailwind CSS: Press F12 → Elements → Check `<head>` for CSS
- Verify tenant context: Check Request::url() contains correct subdomain

---

**Happy coding! 🎉**

Version: 1.0.0
Last Updated: December 23, 2025
