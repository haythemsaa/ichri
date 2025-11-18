# 📚 ichri.tn - API Documentation

## Base URL
```
Production: https://api.ichri.tn/api/v1
Development: http://localhost:8000/api/v1
```

## Authentication
All authenticated endpoints require a JWT Bearer token in the Authorization header:
```
Authorization: Bearer {your_jwt_token}
```

---

## 🔐 Authentication Endpoints

### Register
```http
POST /auth/register
```

**Body:**
```json
{
  "phone": "+21698123456",
  "password": "Password123",
  "first_name": "Ahmed",
  "last_name": "Ben Salah",
  "store_name": "Épicerie Essalem",
  "store_type": "epicerie",
  "address": "Rue République",
  "city": "Sfax"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Utilisateur créé avec succès",
  "data": {
    "user": {...},
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJI..."
  }
}
```

### Login
```http
POST /auth/login
```

**Body:**
```json
{
  "phone": "+21698123456",
  "password": "Password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJI...",
  "user": {
    "id": 1,
    "phone": "+21698123456",
    "store_name": "Épicerie Essalem"
  }
}
```

### Send OTP
```http
POST /auth/send-otp
```

**Body:**
```json
{
  "phone": "+21698123456"
}
```

### Verify OTP
```http
POST /auth/verify-otp
```

**Body:**
```json
{
  "phone": "+21698123456",
  "otp": "123456"
}
```

### Get Current User
```http
GET /auth/me
Authorization: Bearer {token}
```

### Logout
```http
POST /auth/logout
Authorization: Bearer {token}
```

---

## 📦 Catalog Endpoints

### Get Categories
```http
GET /catalog/categories
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Produits Laitiers",
      "slug": "produits-laitiers",
      "icon": "dairy.svg",
      "children": [...]
    }
  ]
}
```

### Get Brands
```http
GET /catalog/brands
```

### Get Products
```http
GET /catalog/products?page=1&per_page=20
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 20)
- `category_id` - Filter by category
- `brand_id` - Filter by brand
- `search` - Search term
- `is_on_sale` - Filter sale items (true/false)
- `sort` - Sort by (price, name, created_at)

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Lait Vitalait 1L",
        "sku": "SKU-VIT-001",
        "price_unit": 2.500,
        "promo_price": 2.200,
        "is_on_sale": true,
        "stock_quantity": 150,
        "category": {...},
        "brand": {...},
        "images": [...]
      }
    ],
    "meta": {
      "current_page": 1,
      "total": 100
    }
  }
}
```

### Get Product Details
```http
GET /catalog/products/{id}
```

### Search Products
```http
GET /catalog/products/search?q=lait
```

### Get Featured Products
```http
GET /catalog/products/featured
```

### Get Products on Sale
```http
GET /catalog/products/on-sale
```

### Get Bestsellers
```http
GET /catalog/products/bestsellers
```

---

## 🛒 Cart Endpoints

### Get Cart
```http
GET /cart
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "product_id": 5,
        "quantity": 3,
        "product": {...}
      }
    ],
    "subtotal": 85.500,
    "delivery_fee": 7.000,
    "total": 92.500,
    "items_count": 5
  }
}
```

### Add to Cart
```http
POST /cart/add
Authorization: Bearer {token}
```

**Body:**
```json
{
  "product_id": 5,
  "quantity": 2
}
```

### Update Cart Item
```http
PUT /cart/update/{id}
Authorization: Bearer {token}
```

**Body:**
```json
{
  "quantity": 5
}
```

### Remove from Cart
```http
DELETE /cart/remove/{id}
Authorization: Bearer {token}
```

### Clear Cart
```http
DELETE /cart/clear
Authorization: Bearer {token}
```

---

## 📋 Order Endpoints

### Get Orders
```http
GET /orders?page=1
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` - Filter by status (pending, confirmed, delivered, etc.)
- `from_date` - Filter from date (YYYY-MM-DD)
- `to_date` - Filter to date (YYYY-MM-DD)

### Create Order
```http
POST /orders
Authorization: Bearer {token}
```

**Body:**
```json
{
  "delivery_address": "123 Rue de la République, Sfax",
  "delivery_latitude": 34.740,
  "delivery_longitude": 10.760,
  "delivery_date": "2024-11-20",
  "payment_method": "cash",
  "notes": "Appeler avant livraison",
  "promotion_code": "RAMADAN2024"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Commande créée avec succès",
  "data": {
    "order": {
      "id": 123,
      "order_number": "ORD-20241118-001",
      "status": "pending",
      "total_amount": 92.500,
      "items": [...]
    }
  }
}
```

### Get Order Details
```http
GET /orders/{id}
Authorization: Bearer {token}
```

### Cancel Order
```http
POST /orders/{id}/cancel
Authorization: Bearer {token}
```

### Reorder
```http
POST /orders/{id}/reorder
Authorization: Bearer {token}
```

---

## 🔥 Karny (Credit Management) Endpoints

### Get All Karny Customers
```http
GET /karny/customers
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "customers": [
      {
        "id": 1,
        "name": "Ahmed Ben Ali",
        "phone": "+21698765432",
        "qr_code": "KARNY-ABC123DEF456",
        "credit_limit": 500.000,
        "current_balance": 150.000,
        "transactions_count": 8
      }
    ]
  }
}
```

### Create Karny Customer
```http
POST /karny/customers
Authorization: Bearer {token}
```

**Body:**
```json
{
  "name": "Ahmed Ben Ali",
  "phone": "+21698765432",
  "credit_limit": 500.000
}
```

### Get Customer Details
```http
GET /karny/customers/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "customer": {
      "id": 1,
      "name": "Ahmed Ben Ali",
      "qr_code": "KARNY-ABC123DEF456",
      "current_balance": 150.000,
      "transactions": [
        {
          "id": 1,
          "type": "credit",
          "amount": 50.000,
          "description": "Pain, lait, café",
          "due_date": "2024-11-25",
          "status": "pending"
        }
      ]
    }
  }
}
```

### Add Credit
```http
POST /karny/customers/{id}/credit
Authorization: Bearer {token}
```

**Body:**
```json
{
  "amount": 50.000,
  "description": "Pain, lait, cigarettes",
  "due_date": "2024-11-25"
}
```

### Add Payment
```http
POST /karny/customers/{id}/payment
Authorization: Bearer {token}
```

**Body:**
```json
{
  "amount": 30.000,
  "transaction_id": 5
}
```

### Get Karny Statistics
```http
GET /karny/statistics
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_customers": 25,
    "total_credit_outstanding": 3250.000,
    "total_overdue": 450.000,
    "overdue_count": 5,
    "best_payers": [...],
    "worst_payers": [...]
  }
}
```

### Search by QR Code
```http
GET /karny/qr/{qrCode}
Authorization: Bearer {token}
```

**Example:**
```http
GET /karny/qr/KARNY-ABC123DEF456
```

---

## 💰 Digital Services Endpoints

### Get Available Services
```http
GET /digital-services/services
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "services": [
      {
        "type": "mobile_topup",
        "name": "Recharge Mobile",
        "providers": ["ooredoo", "orange", "tunisie_telecom"],
        "commission_percentage": 3.0,
        "amounts": [5, 10, 20, 30]
      },
      {
        "type": "electricity_bill",
        "name": "Facture STEG",
        "providers": ["steg"],
        "commission_percentage": 1.5
      }
    ]
  }
}
```

### Process Digital Service
```http
POST /digital-services/process
Authorization: Bearer {token}
```

**Body (Mobile Top-up):**
```json
{
  "service_type": "mobile_topup",
  "provider": "ooredoo",
  "recipient_number": "98123456",
  "amount": 10.000
}
```

**Body (Bill Payment):**
```json
{
  "service_type": "electricity_bill",
  "provider": "steg",
  "recipient_number": "12345678901",
  "amount": 85.500
}
```

**Response:**
```json
{
  "success": true,
  "message": "Transaction traitée avec succès",
  "data": {
    "transaction": {
      "transaction_ref": "DS-20241118-ABC12345",
      "service_type": "mobile_topup",
      "provider": "ooredoo",
      "amount": 10.000,
      "commission": 0.300,
      "status": "completed"
    }
  }
}
```

### Get Transaction History
```http
GET /digital-services/history?page=1
Authorization: Bearer {token}
```

**Query Parameters:**
- `service_type` - Filter by type
- `provider` - Filter by provider
- `from_date` - From date
- `to_date` - To date

### Get Statistics
```http
GET /digital-services/statistics
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_transactions": 145,
    "total_amount": 4520.000,
    "total_commission_earned": 98.500,
    "by_service_type": {
      "mobile_topup": {
        "count": 98,
        "amount": 2150.000,
        "commission": 64.500
      },
      "electricity_bill": {
        "count": 32,
        "amount": 1850.000,
        "commission": 27.750
      }
    }
  }
}
```

---

## 🎁 Promotion Endpoints

### Get Active Promotions
```http
GET /promotions
```

**Response:**
```json
{
  "success": true,
  "data": {
    "promotions": [
      {
        "id": 1,
        "name": "Ramadan Kareem 2024",
        "code": "RAMADAN2024",
        "type": "percentage",
        "discount_value": 15,
        "min_purchase": 50,
        "start_date": "2024-11-15",
        "end_date": "2024-12-15",
        "is_featured": true
      }
    ]
  }
}
```

### Get Featured Promotions
```http
GET /promotions/featured
```

### Validate Promo Code
```http
POST /promotions/validate
Authorization: Bearer {token}
```

**Body:**
```json
{
  "code": "RAMADAN2024"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "valid": true,
    "promotion": {
      "id": 1,
      "name": "Ramadan Kareem 2024",
      "discount_value": 15,
      "min_purchase": 50
    }
  }
}
```

### Apply Promotion to Cart
```http
POST /promotions/apply
Authorization: Bearer {token}
```

**Body:**
```json
{
  "promotion_id": 1,
  "order_amount": 120.000,
  "cart_items": [
    {
      "product_id": 5,
      "quantity": 3,
      "unit_price": 15.000
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "discount_amount": 18.000,
    "final_amount": 102.000,
    "promotion": {...}
  }
}
```

---

## 👤 User Endpoints

### Get Profile
```http
GET /user/profile
Authorization: Bearer {token}
```

### Update Profile
```http
PUT /user/profile
Authorization: Bearer {token}
```

**Body:**
```json
{
  "first_name": "Ahmed",
  "last_name": "Ben Salah",
  "store_name": "Épicerie Essalem",
  "address": "123 Rue République",
  "city": "Sfax"
}
```

### Upload Document
```http
POST /user/documents
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Body:**
```
document: (file)
type: patente|cin|store_photo
```

### Get Credit Info
```http
GET /user/credit
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "credit_score": 75,
    "credit_level": "gold",
    "credit_limit": 5000.000,
    "credit_used": 1250.000,
    "credit_available": 3750.000,
    "is_eligible_for_credit": true
  }
}
```

### Get User Statistics
```http
GET /user/statistics
Authorization: Bearer {token}
```

### Get Favorites
```http
GET /favorites
Authorization: Bearer {token}
```

### Toggle Favorite
```http
POST /favorites/toggle/{productId}
Authorization: Bearer {token}
```

---

## 👥 Team Management (Multi-User Accounts)

### Get Team Members
```http
GET /team/members
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "members": [
      {
        "id": 1,
        "account_id": 10,
        "user_id": 15,
        "role": "admin",
        "permissions": ["can_order", "can_view_reports"],
        "spending_limit": 1000.000,
        "is_active": true,
        "user": {...},
        "invited_by": {...}
      }
    ]
  }
}
```

### Invite Team Member
```http
POST /team/invite
Authorization: Bearer {token}
```

**Body:**
```json
{
  "email": "employee@example.com",
  "phone": "+21698765432",
  "role": "employee",
  "spending_limit": 500.000,
  "permissions": ["can_order", "can_view_products"]
}
```

**Roles:** `admin`, `manager`, `employee`, `viewer`

**Response (201):**
```json
{
  "success": true,
  "message": "Invitation envoyée avec succès",
  "data": {
    "invitation": {
      "id": 1,
      "account_id": 10,
      "email": "employee@example.com",
      "role": "employee",
      "token": "abc123...",
      "expires_at": "2024-12-01T10:00:00Z"
    }
  }
}
```

### Update Team Member
```http
PUT /team/members/{id}
Authorization: Bearer {token}
```

**Body:**
```json
{
  "role": "manager",
  "spending_limit": 1500.000,
  "is_active": true,
  "permissions": ["can_order", "can_view_reports", "can_manage_inventory"]
}
```

### Remove Team Member
```http
DELETE /team/members/{id}
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Membre retiré de l'équipe"
}
```

---

## 🎭 Masquerade (Sales Rep Ordering)

### Start Masquerade Session
```http
POST /masquerade/start
Authorization: Bearer {token}
```

**Requires:** `admin` or `sales_rep` role

**Body:**
```json
{
  "user_id": 123,
  "reason": "Help customer place first order"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Session masquerade démarrée",
  "data": {
    "session_id": 1,
    "target_user": {...},
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJI...",
    "token_type": "bearer",
    "warning": "Vous agissez en tant que Épicerie Test"
  }
}
```

### End Masquerade Session
```http
POST /masquerade/end
Authorization: Bearer {token}
```

**Body:**
```json
{
  "session_id": 1
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Session masquerade terminée",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJI...",
    "user": {...}
  }
}
```

### Get Masquerade History
```http
GET /masquerade/history
Authorization: Bearer {token}
```

**Requires:** `admin` role

**Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "admin_user_id": 5,
        "target_user_id": 123,
        "reason": "Customer onboarding",
        "started_at": "2024-11-15T10:00:00Z",
        "ended_at": "2024-11-15T10:30:00Z",
        "ip_address": "192.168.1.1",
        "actions_log": [...],
        "admin": {...},
        "target": {...}
      }
    ]
  }
}
```

---

## 🎁 Loyalty Program

### Get Loyalty Points
```http
GET /loyalty/points
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "points": 1500,
    "lifetime_points": 6000,
    "tier": "gold",
    "tier_multiplier": 1.5,
    "tier_expires_at": "2025-11-15T00:00:00Z",
    "next_tier": "platinum",
    "points_to_next_tier": 9000
  }
}
```

**Tiers:**
- **Bronze**: 0+ points, 1.0x multiplier
- **Silver**: 1,000+ points, 1.2x multiplier
- **Gold**: 5,000+ points, 1.5x multiplier
- **Platinum**: 15,000+ points, 2.0x multiplier

### Get Loyalty Transactions
```http
GET /loyalty/transactions?type=earn&per_page=20
Authorization: Bearer {token}
```

**Query Parameters:**
- `type` (optional): `earn`, `redeem`, `expire`, `bonus`, `adjustment`
- `per_page` (optional): Number per page (default: 20)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "type": "earn",
        "points": 150,
        "balance_after": 1500,
        "source_type": "Order",
        "source_id": 456,
        "description": "Order #456 - 100 TND",
        "created_at": "2024-11-15T10:00:00Z"
      }
    ]
  }
}
```

### Get Tiers Information
```http
GET /loyalty/tiers
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "tiers": [
      {
        "name": "bronze",
        "threshold": 0,
        "multiplier": 1.0,
        "benefits": ["Points de base"]
      },
      {
        "name": "silver",
        "threshold": 1000,
        "multiplier": 1.2,
        "benefits": ["+20% de points", "Livraison prioritaire"]
      }
    ],
    "current_tier": "gold",
    "lifetime_points": 6000
  }
}
```

### Get Available Rewards
```http
GET /loyalty/rewards?type=discount
Authorization: Bearer {token}
```

**Query Parameters:**
- `type` (optional): `discount`, `product`, `cashback`, `free_delivery`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "rewards": [
      {
        "id": 1,
        "name": "10 TND Discount",
        "description": "Get 10 TND off your next order",
        "type": "discount",
        "points_cost": 500,
        "config": {"amount": 10},
        "quantity_available": 100,
        "is_active": true,
        "can_afford": true,
        "points_needed": 0
      }
    ],
    "user_points": 1500
  }
}
```

### Get Single Reward
```http
GET /loyalty/rewards/{id}
Authorization: Bearer {token}
```

### Redeem Reward
```http
POST /loyalty/rewards/{id}/redeem
Authorization: Bearer {token}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Récompense échangée avec succès!",
  "data": {
    "redemption": {
      "id": 1,
      "user_id": 10,
      "reward_id": 5,
      "points_spent": 500,
      "status": "approved",
      "redemption_code": "ABC123XYZ789",
      "expires_at": "2024-12-15T00:00:00Z"
    },
    "remaining_points": 1000
  }
}
```

### Get User Redemptions
```http
GET /loyalty/redemptions?status=approved
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (optional): `pending`, `approved`, `used`, `expired`, `cancelled`
- `per_page` (optional): Number per page (default: 20)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "reward_id": 5,
        "points_spent": 500,
        "status": "approved",
        "redemption_code": "ABC123XYZ789",
        "expires_at": "2024-12-15T00:00:00Z",
        "used_at": null,
        "reward": {...}
      }
    ]
  }
}
```

### Get Single Redemption
```http
GET /loyalty/redemptions/{id}
Authorization: Bearer {token}
```

### Use Redemption Code
```http
POST /loyalty/redemptions/{id}/use
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Code utilisé avec succès",
  "data": {
    "id": 1,
    "status": "used",
    "used_at": "2024-11-15T10:30:00Z"
  }
}
```

### Cancel Redemption
```http
POST /loyalty/redemptions/{id}/cancel
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Échange annulé et points remboursés",
  "data": {...}
}
```

---

## ⚠️ Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "errors": {
    "phone": ["Le format du téléphone est invalide"],
    "amount": ["Le montant doit être supérieur à 0"]
  }
}
```

### Unauthorized (401)
```json
{
  "success": false,
  "message": "Non authentifié"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Ressource non trouvée"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Erreur serveur. Veuillez réessayer."
}
```

---

## 📊 Rate Limiting

- **Anonymous**: 60 requests per minute
- **Authenticated**: 120 requests per minute

Exceeded limits return `429 Too Many Requests`.

---

## 🌐 Postman Collection

Import this collection into Postman:
```
https://api.ichri.tn/postman-collection.json
```

---

**Version**: 2.1.0 (Phase 2.5)
**Last Updated**: November 2024
**Contact**: support@ichri.tn

---

## 🆕 Phase 2 Features (v2.1.0)

**New in this version:**
- 👥 **Team Management**: Multi-user accounts with role-based permissions
- 🎭 **Masquerade**: Sales rep ordering and customer support
- 🎁 **Loyalty Program**: Advanced tier-based points and rewards system
- 📱 **WhatsApp Integration**: Foundation ready (full implementation coming in Phase 2.5+)
