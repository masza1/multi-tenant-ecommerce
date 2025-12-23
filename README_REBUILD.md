# Multi-Tenant E-Commerce SaaS - REBUILD

Laravel 12 + Vue.js + Inertia.js multi-tenant e-commerce platform dengan database terpisah per tenant.

## ✅ Features (Completed)

- ✅ Multi-database architecture (satu DB per tenant)
- ✅ Subdomain-based tenancy (toko1.localhost, toko2.localhost, dll)
- ✅ Auto tenant database creation saat registrasi
- ✅ User authentication isolated per tenant
- ✅ Product management (CRUD) per tenant
- ✅ Shopping cart dengan persistence (database-backed)
- ✅ Responsive UI dengan Tailwind CSS
- ✅ Admin dashboard untuk product management
- ✅ Data isolation terverifikasi antar tenant

## 🏗️ Architecture

### Database
- **Landlord Database** (`multitenant_central`): Menyimpan tenant dan domain data
- **Tenant Databases** (`tenant_{id}`): Satu database terpisah per tenant dengan users, products, carts

### Routing
- **Central** (routes/web.php): Landing page + tenant registration
- **Tenant** (routes/tenant.php): Shop + auth + cart + product admin (domain-based)

### Tech Stack
- Laravel 12
- Vue 3 + Inertia.js
- Tailwind CSS
- PostgreSQL
- Stancl/Tenancy (multi-tenancy)

## 🚀 Setup & Installation

### 1. Prerequisites
```bash
PHP 8.2+
PostgreSQL 14+
Node.js 18+
Composer
```

### 2. Clone & Install
```bash
git clone <repo-url>
cd MultiTenantDB
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=54322
DB_DATABASE=multitenant_central
DB_USERNAME=postgres
DB_PASSWORD=

APP_URL=http://localhost
TENANT_DOMAIN=.localhost
```

### 4. Database Setup
```bash
php artisan migrate --path=database/migrations/landlord
```

### 5. Run Development Server
```bash
php artisan serve
npm run dev
```

### 6. Setup Localhost Domains
Add to `/etc/hosts` (Mac/Linux) or `C:\Windows\System32\drivers\etc\hosts` (Windows):
```
127.0.0.1 localhost
127.0.0.1 demo.localhost
127.0.0.1 another-store.localhost
```

## 📖 Usage Guide

### Create Tenant (Register New Store)
1. Visit `http://localhost`
2. Click "Mulai Sekarang - Gratis!"
3. Fill form:
   - **Store Name**: e.g., "Toko Sepatu"
   - **Subdomain**: e.g., "toko-sepatu"
   - **Email**: owner@example.com
4. Submit → Auto redirects to `http://toko-sepatu.localhost/register`

### First User Setup
1. Visit `http://toko-sepatu.localhost/register`
2. Register first user (auto becomes **admin**)
3. Login with credentials
4. Access admin dashboard at `http://toko-sepatu.localhost/products`

### Admin Features
- Add/Edit/Delete products
- Upload product images
- Manage inventory (stock)
- View all products

### Customer Features
- Browse products (public catalog)
- Add to cart
- Manage cart (update qty, remove items)
- View cart total

## 🧪 Testing

### Manual Testing Scenarios

**Test 1: Multi-Tenant Isolation**
```bash
# Create Tenant A
Visit http://localhost → Register "Store A" with subdomain "store-a"
Register user in store-a.localhost
Add product "Product A"

# Create Tenant B
Register "Store B" with subdomain "store-b"
Register user in store-b.localhost
Add product "Product B"

# Verify Isolation
Visit store-a.localhost → Should only see "Product A"
Visit store-b.localhost → Should only see "Product B"
```

**Test 2: User Isolation**
```bash
# Login Test
user-a@store-a → can login at store-a.localhost
user-a@store-a → cannot login at store-b.localhost (different database)
```

**Test 3: Cart Persistence**
```bash
# Add to cart
Login → Add product to cart
Refresh page → Cart items still present
Logout/Login → Cart items still present
```

### Automated Tests
```bash
php artisan test
```

Tests included:
- Tenant isolation verification
- User authentication per tenant
- Cart persistence
- Product visibility per tenant

## 📁 Project Structure

```
app/
├── Http/Controllers/
│   ├── LandingController.php (central)
│   ├── TenantRegisterController.php (central)
│   └── Tenant/
│       ├── ShopController.php
│       ├── CartController.php
│       ├── ProductController.php
│       └── Auth/AuthController.php
├── Models/
│   ├── Tenant.php
│   ├── User.php
│   ├── Product.php
│   └── Cart.php
├── Middleware/
│   └── CheckRole.php
└── Policies/
    └── CartPolicy.php

database/
├── migrations/
│   ├── landlord/ (tenants, domains)
│   └── tenant/ (users, products, carts)
└── seeders/

resources/js/Pages/
├── Landing.vue
├── Auth/
│   ├── Login.vue
│   └── Register.vue
├── Shop/
│   ├── Index.vue
│   └── Show.vue
└── Cart/
    └── Index.vue

routes/
├── web.php (central: /, /register-tenant)
└── tenant.php (tenant: /, /login, /register, /products, /cart, /products/:id)
```

## 🔧 Key Changes from Original

1. **Simplified Models** - Removed unnecessary complexity
2. **Domain-based Tenancy** - Using stancl/tenancy's domain middleware
3. **Clean Controllers** - Direct mapping to routes without extra overhead
4. **Minimal Frontend** - Focus on MVP with clean, functional UI
5. **Proper Isolation** - Each tenant has completely separate database

## ⚠️ Important Notes

### Local Development with Multiple Tenants
Update `/etc/hosts` for each tenant domain:
```
127.0.0.1 tenant1.localhost
127.0.0.1 tenant2.localhost
127.0.0.1 tenant3.localhost
```

### Database Names
Tenant databases follow pattern: `tenant_{tenant_id}`
Example: If tenant ID is "toko-sepatu", database will be `tenant_toko_sepatu`

### Storage for Images
Product images stored in `storage/app/public/products/`
Create symlink:
```bash
php artisan storage:link
```

### Session Configuration
Sessions stored in tenant database. Ensure sessions migration runs with tenant migrations.

## 📚 API Reference

### Controllers

#### LandingController
- `GET /` - Landing page with tenant registration

#### TenantRegisterController
- `POST /register-tenant` - Register new tenant (creates DB + migrates)

#### ShopController (Tenant)
- `GET /` - List active products
- `GET /products/{id}` - Show product detail

#### CartController (Tenant)
- `GET /cart` - View cart items
- `POST /cart` - Add product to cart
- `PATCH /cart/{id}` - Update cart item quantity
- `DELETE /cart/{id}` - Remove from cart

#### ProductController (Tenant, Admin only)
- `GET /products` - List products
- `GET /products/create` - Create form
- `POST /products` - Store product
- `GET /products/{id}/edit` - Edit form
- `PATCH /products/{id}` - Update product
- `DELETE /products/{id}` - Delete product

#### AuthController (Tenant)
- `GET /register` - Registration form
- `POST /register` - Register user
- `GET /login` - Login form
- `POST /login` - Login
- `POST /logout` - Logout

## 🐛 Troubleshooting

### Issue: "Database does not exist"
**Solution:** Ensure tenant migrations ran:
```bash
php artisan tenants:migrate
```

### Issue: Subdomain not accessible
**Solution:** Add to `/etc/hosts`:
```
127.0.0.1 subdomain.localhost
```

### Issue: Image uploads not working
**Solution:** Create storage symlink:
```bash
php artisan storage:link
```

### Issue: CSRF token errors
**Solution:** Clear cache:
```bash
php artisan cache:clear
```

## 📝 Next Steps (Future Enhancements)

- [ ] Checkout functionality
- [ ] Payment integration
- [ ] Order management
- [ ] User profile management
- [ ] Product reviews/ratings
- [ ] Search & filtering
- [ ] Dashboard analytics
- [ ] Email notifications
- [ ] Multi-currency support
- [ ] Admin export/import

## 📄 License

MIT

## 👥 Contributing

Contributions welcome! Please follow the Laravel/Vue best practices.

## 📞 Support

For issues or questions, check GitHub issues or contact maintainer.

---

**Status:** MVP Ready for Testing ✅

**Version:** 1.0.0

**Last Updated:** 2025-12-24
