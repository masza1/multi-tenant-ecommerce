# API Guide - Multi-Tenant eCommerce Platform

Dokumentasi lengkap untuk semua API endpoints yang tersedia di platform.

## 📋 Table of Contents

1. [Authentication](#-authentication)
2. [Products API](#-products-api)
3. [Cart API](#-cart-api)
4. [Users API](#-users-api)
5. [Testing dengan Postman/Curl](#-testing-dengan-postman)
6. [Response Format](#-response-format)
7. [Error Handling](#-error-handling)

---

## 🔐 Authentication

### Register User

**Endpoint:** `POST /register`

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Success Response:** (302 Redirect to Dashboard)
```
HTTP/1.1 302 Found
Location: /dashboard
Set-Cookie: XSRF-TOKEN=...; laravel_session=...
```

**Validation Error Response:** (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

---

### Login

**Endpoint:** `POST /login`

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123",
    "remember": false
}
```

**Success Response:** (302 Redirect)
```
HTTP/1.1 302 Found
Location: /dashboard
```

**Invalid Credentials Response:** (422)
```json
{
    "message": "The provided credentials do not match our records."
}
```

---

### Logout

**Endpoint:** `POST /logout`

**Auth Required:** Yes (Authenticated User)

**Request:**
```
POST /logout HTTP/1.1
Cookie: laravel_session=...
```

**Response:** (302 Redirect to Home)
```
HTTP/1.1 302 Found
Location: /
```

---

### Get Authenticated User

**Endpoint:** `GET /api/user`

**Auth Required:** Yes

**Response:** (200 OK)
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2025-12-23T10:00:00.000000Z",
    "updated_at": "2025-12-23T10:00:00.000000Z"
}
```

---

## 📦 Products API

### List All Products

**Endpoint:** `GET /products`

**Auth Required:** No

**Query Parameters:**
```
?page=1
?per_page=15
?category=electronics
?sort=price_asc
```

**Response:** (200 OK - HTML/Inertia)
```
HTTP/1.1 200 OK
Content-Type: text/html

[Returns Inertia.js rendered page with products list]
```

**Via API (JSON):**
```bash
curl "http://store1.localhost:8000/api/products"
```

Response:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "category_id": 1,
            "name": "Laptop Gaming",
            "slug": "laptop-gaming",
            "description": "High-performance laptop",
            "short_description": "Gaming laptop",
            "price": 15000000,
            "original_price": 20000000,
            "sku": "LAPTOP-001",
            "stock": 50,
            "low_stock_threshold": 5,
            "active": true,
            "created_at": "2025-12-23T10:00:00.000000Z",
            "updated_at": "2025-12-23T10:00:00.000000Z",
            "category": {
                "id": 1,
                "name": "Elektronik"
            }
        }
    ]
}
```

---

### Get Single Product

**Endpoint:** `GET /products/{id}`

**Auth Required:** No

**Response:** (200 OK)
```json
{
    "id": 1,
    "name": "Laptop Gaming",
    "price": 15000000,
    "stock": 50,
    "images": [
        {
            "id": 1,
            "url": "/storage/products/laptop-1.jpg",
            "alt_text": "Laptop Gaming Front View",
            "is_primary": true
        }
    ]
}
```

---

### Create Product

**Endpoint:** `POST /products`

**Auth Required:** Yes (Admin/Authenticated User)

**Request Body:**
```json
{
    "category_id": 1,
    "name": "Smartphone Flagship",
    "slug": "smartphone-flagship",
    "description": "Latest flagship smartphone",
    "short_description": "Flagship phone",
    "price": 12000000,
    "original_price": 15000000,
    "sku": "PHONE-001",
    "stock": 100,
    "low_stock_threshold": 10,
    "active": true,
    "images": [
        {
            "image_url": "https://example.com/phone.jpg",
            "alt_text": "Phone Front",
            "is_primary": true
        }
    ]
}
```

**Success Response:** (302/201)
```json
{
    "success": true,
    "message": "Product created successfully",
    "product": {
        "id": 2,
        "name": "Smartphone Flagship",
        "sku": "PHONE-001",
        ...
    }
}
```

**Validation Error:** (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "sku": ["This SKU is already in use"],
        "price": ["Price must be greater than 0"]
    }
}
```

---

### Update Product

**Endpoint:** `PUT /products/{id}`

**Auth Required:** Yes

**Request Body:** (same as create, partial update allowed)
```json
{
    "name": "Smartphone Flagship Pro",
    "price": 13000000,
    "stock": 95
}
```

**Response:** (200 OK)
```json
{
    "success": true,
    "message": "Product updated successfully",
    "product": { ... }
}
```

---

### Delete Product

**Endpoint:** `DELETE /products/{id}`

**Auth Required:** Yes

**Response:** (200 OK)
```json
{
    "success": true,
    "message": "Product deleted successfully"
}
```

---

## 🛒 Shopping Cart API

### Add Product to Cart

**Endpoint:** `POST /cart`

**Auth Required:** No (Guest or Authenticated)

**Request Body:**
```json
{
    "product_id": 1,
    "quantity": 2
}
```

**Success Response:** (200 OK)
```json
{
    "success": true,
    "message": "Product added to cart",
    "cart": {
        "id": 1,
        "item_count": 2,
        "items": [
            {
                "id": 1,
                "product_id": 1,
                "product_name": "Laptop Gaming",
                "product_sku": "LAPTOP-001",
                "quantity": 2,
                "price": 15000000,
                "discount": 0,
                "subtotal": 30000000
            }
        ],
        "subtotal": 30000000,
        "tax": 3000000,
        "shipping": 0,
        "total": 33000000
    }
}
```

**Validation Error - Insufficient Stock:** (400)
```json
{
    "success": false,
    "message": "Insufficient stock",
    "available_stock": 5,
    "requested_quantity": 10
}
```

---

### Get Cart

**Endpoint:** `GET /api/cart`

**Auth Required:** Yes (Authenticated User) or Session-based (Guest)

**Response:** (200 OK)
```json
{
    "success": true,
    "cart": {
        "id": 1,
        "user_id": 1,
        "status": "active",
        "item_count": 2,
        "subtotal": 30000000,
        "tax": 3000000,
        "shipping": 0,
        "total": 33000000,
        "items": [
            {
                "id": 1,
                "product_id": 1,
                "product_name": "Laptop Gaming",
                "quantity": 2,
                "price": 15000000,
                "discount": 5000000,
                "subtotal": 30000000
            },
            {
                "id": 2,
                "product_id": 2,
                "product_name": "Smartphone",
                "quantity": 1,
                "price": 12000000,
                "discount": 0,
                "subtotal": 12000000
            }
        ]
    }
}
```

---

### Update Cart Item Quantity

**Endpoint:** `PUT /cart-item/{item_id}`

**Auth Required:** Yes

**Request Body:**
```json
{
    "quantity": 5
}
```

**Success Response:** (200 OK)
```json
{
    "success": true,
    "message": "Cart item updated",
    "cart": {
        "item_count": 5,
        "subtotal": 75000000,
        "tax": 7500000,
        "total": 82500000
    }
}
```

**Error - Quantity Exceeds Stock:** (400)
```json
{
    "success": false,
    "message": "Quantity exceeds available stock",
    "max_quantity": 50
}
```

---

### Remove Item from Cart

**Endpoint:** `DELETE /cart-item/{item_id}`

**Auth Required:** Yes

**Response:** (200 OK)
```json
{
    "success": true,
    "message": "Item removed from cart",
    "cart": {
        "item_count": 1,
        "subtotal": 12000000,
        "tax": 1200000,
        "total": 13200000
    }
}
```

---

### Clear Entire Cart

**Endpoint:** `POST /cart/clear`

**Auth Required:** Yes

**Response:** (302 Redirect or 200 OK)
```json
{
    "success": true,
    "message": "Cart cleared",
    "cart": {
        "item_count": 0,
        "subtotal": 0,
        "tax": 0,
        "total": 0
    }
}
```

---

## 👤 Users API

### Get User Profile

**Endpoint:** `GET /profile`

**Auth Required:** Yes

**Response:** (200 OK - Inertia Page)
```
[Returns user profile page with editable form]
```

---

### Update User Profile

**Endpoint:** `PATCH /profile`

**Auth Required:** Yes

**Request Body:**
```json
{
    "name": "John Doe Updated",
    "email": "john.new@example.com"
}
```

**Response:** (302 Redirect)
```
Redirects back to profile page
```

---

### Update Password

**Endpoint:** `PUT /password`

**Auth Required:** Yes

**Request Body:**
```json
{
    "current_password": "old_password",
    "password": "new_password",
    "password_confirmation": "new_password"
}
```

**Response:** (302 Redirect)
```
Redirects with success message
```

---

## 🧪 Testing dengan Postman

### Setup Postman

1. **Create Collection:** "Multi-Tenant eCommerce"
2. **Add Environment Variables:**
   ```
   base_url: http://store1.localhost:8000
   api_token: [leave empty]
   product_id: 1
   cart_item_id: 1
   ```

### Test Register

```
POST {{base_url}}/register
Content-Type: application/json

{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Test Add to Cart

```
POST {{base_url}}/cart
Content-Type: application/json

{
    "product_id": 1,
    "quantity": 2
}
```

### Test Get Cart

```
GET {{base_url}}/api/cart
Content-Type: application/json
```

---

## 📝 Testing dengan Curl

### Register User

```bash
curl -X POST http://store1.localhost:8000/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Add to Cart (Guest)

```bash
curl -X POST http://store1.localhost:8000/cart \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }'
```

### Add to Cart (With Cookie)

```bash
curl -X POST http://store1.localhost:8000/cart \
  -H "Content-Type: application/json" \
  -b "laravel_session=YOUR_SESSION_COOKIE" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }'
```

### Get Cart

```bash
curl -X GET http://store1.localhost:8000/api/cart \
  -H "Accept: application/json" \
  -b "laravel_session=YOUR_SESSION_COOKIE"
```

---

## 📨 Response Format

### Success Response

```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        ...
    }
}
```

### Error Response

```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field_name": ["Error detail 1", "Error detail 2"]
    }
}
```

### Pagination Response

```json
{
    "data": [...],
    "links": {
        "first": "http://...",
        "last": "http://...",
        "prev": "http://...",
        "next": "http://..."
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "path": "http://...",
        "per_page": 15,
        "to": 15,
        "total": 75
    }
}
```

---

## ⚠️ Error Handling

### Common HTTP Status Codes

| Code | Meaning | Example |
|------|---------|---------|
| 200 | OK | Request successful |
| 302 | Found (Redirect) | After form submission |
| 400 | Bad Request | Invalid data |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | Not authorized |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Server Error | Internal error |

### Example Error Response (422 Validation)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email has already been taken."
        ],
        "password": [
            "The password must be at least 8 characters."
        ]
    }
}
```

### Example Error Response (401 Unauthorized)

```json
{
    "message": "Unauthenticated."
}
```

---

## 🔒 Multi-Tenant Isolation

### Important Note

Semua requests otomatis di-scope ke tenant yang diakses:

- Request ke `store1.localhost` → Akses hanya data store1
- Request ke `store2.localhost` → Akses hanya data store2

Database connection otomatis di-switch berdasarkan subdomain yang diakses.

### Test Isolation

```bash
# Store 1 - Create product
curl -X POST http://store1.localhost:8000/products \
  -d '{"name": "Product 1", ...}'

# Store 2 - Get products (Product 1 NOT visible)
curl -X GET http://store2.localhost:8000/api/products

# Response dari Store 2 tidak akan include Product 1
```

---

## 🔄 Cart Calculation Examples

### Example 1: Simple Cart

```
Product: Laptop - Price: 15,000,000 (Original: 20,000,000)
Quantity: 1

Subtotal: 15,000,000
Discount: 5,000,000 (original_price - price)
Tax (10%): 1,500,000
Shipping: 0 (free, subtotal > 100)
Total: 16,500,000
```

### Example 2: Multiple Items

```
Item 1: Product A - 5,000,000 x 2 = 10,000,000
Item 2: Product B - 3,000,000 x 1 = 3,000,000

Subtotal: 13,000,000
Tax (10%): 1,300,000
Shipping: 10,000 (under 100)
Total: 14,310,000
```

---

## 📊 API Testing Checklist

- [ ] Register user
- [ ] Login user
- [ ] Get authenticated user
- [ ] View products list
- [ ] View single product
- [ ] Add product to cart (guest)
- [ ] Add product to cart (authenticated)
- [ ] Get cart contents
- [ ] Update cart item quantity
- [ ] Remove item from cart
- [ ] Clear cart
- [ ] Test cart calculations
- [ ] Test cross-tenant isolation
- [ ] Test validation errors
- [ ] Test authentication errors

---

## 🚀 Next Steps

1. Test all endpoints using Postman
2. Verify multi-tenant isolation
3. Check response formats
4. Test error scenarios
5. Integrate with frontend

---

**Happy Testing! 🎉**

Version: 1.0.0
Last Updated: December 23, 2025
