# 🚀 ichri.tn - Quick Start Guide (Production Ready)

> **Complete B2B E-Commerce Platform for Grocery Stores in Tunisia**
> Get up and running in under 10 minutes!

---

## ⚡ 1-Minute Quick Start

```bash
# Clone & Setup
git clone <repository-url> ichri
cd ichri
./setup-quick.sh

# That's it! Application will be running on http://localhost:8000
```

---

## 📋 System Requirements

### Minimum Requirements
- **PHP**: 8.2+ with extensions (pdo_mysql, redis, gd, curl, mbstring, xml, zip)
- **Composer**: 2.x
- **MySQL**: 8.0+ or MariaDB 10.6+
- **Redis**: 6.x+ (Required for cache, queue, sessions)
- **Node.js**: 18+ (Frontend only)
- **Web Server**: Nginx or Apache

### Recommended for Production
- **PHP**: 8.3 with OPcache enabled
- **MySQL**: 8.0+ with InnoDB
- **Redis**: 7.x with persistence enabled
- **Elasticsearch**: 8.x (Search optimization)
- **Supervisor**: For queue workers
- **SSL Certificate**: Let's Encrypt or commercial

---

## 🔧 Installation Methods

### Method 1: Automated Script (Recommended)

```bash
chmod +x setup-quick.sh
./setup-quick.sh
```

**What it does:**
- ✅ Checks system requirements
- ✅ Installs dependencies
- ✅ Creates `.env` from template
- ✅ Generates APP_KEY and JWT_SECRET
- ✅ Runs database migrations
- ✅ Seeds demo data (optional)
- ✅ Optimizes for performance
- ✅ Starts development server

---

### Method 2: Manual Installation

#### Step 1: Install Dependencies

```bash
cd backend
composer install --optimize-autoloader
```

#### Step 2: Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

**Edit `.env` with your credentials:**

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ichri
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Redis (Required)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

# Application
APP_URL=http://localhost:8000
APP_ENV=production
APP_DEBUG=false
```

#### Step 3: Database Setup

```bash
php artisan migrate --force
php artisan db:seed --force  # Optional: demo data
```

#### Step 4: Storage & Permissions

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

#### Step 5: Optimize

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload -o
```

#### Step 6: Start Server

```bash
# Development
php artisan serve

# Production (use Nginx/Apache + PHP-FPM)
# See PRODUCTION_DEPLOY.md
```

---

### Method 3: Docker Deployment

```bash
# Start all services
docker-compose up -d

# Run migrations
docker-compose exec backend php artisan migrate --force

# Seed database
docker-compose exec backend php artisan db:seed --force

# Access application
# Backend API: http://localhost:8000
# Frontend: http://localhost:3000
# Adminer: http://localhost:8080
```

**Services included:**
- Backend (Laravel 11 + PHP 8.3)
- MySQL 8.0
- Redis 7.x
- Nginx
- Frontend (React/Next.js)

---

## 🧪 Verify Installation

### Health Check Endpoints

```bash
# Basic health check
curl http://localhost:8000/api/v1/health

# Detailed system check
curl http://localhost:8000/api/v1/health/detailed

# System metrics
curl http://localhost:8000/api/v1/health/metrics
```

**Expected Response:**
```json
{
  "status": "ok",
  "timestamp": "2024-11-19T00:00:00+00:00",
  "version": "2.0.0"
}
```

### Run Tests

```bash
cd backend
php artisan test

# With coverage
php artisan test --coverage
```

**Expected:** 96+ tests passing with 95%+ coverage

---

## 📡 API Testing

### Using Postman

1. Import `postman_collection.json` from root directory
2. Collection includes 30+ pre-configured requests
3. Organized by feature: Auth, Products, Orders, Loyalty, Teams, etc.

### Quick API Examples

**Register User:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ahmed Ben Ali",
    "email": "ahmed@example.com",
    "password": "password123",
    "phone": "+21620123456"
  }'
```

**Get Products:**
```bash
curl http://localhost:8000/api/v1/catalog/products
```

**Create Order (Authenticated):**
```bash
curl -X POST http://localhost:8000/api/v1/orders \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 5}
    ],
    "delivery_address": "123 Rue de la République, Tunis"
  }'
```

---

## 🎯 Features Ready for Immediate Use

### ✅ Phase 1: Core B2B E-Commerce
- User authentication (JWT + OTP)
- Product catalog with categories & brands
- Shopping cart & checkout
- Order management (8 statuses)
- Real-time order tracking
- Multi-role users (admin, store_owner, employee)

### ✅ Phase 2.0: Karny (Customer Credit Management)
- Customer credit ledger system
- Payment tracking
- QR code customer identification
- Credit limit enforcement
- Payment history & analytics

### ✅ Phase 2.1: Digital Services
- Mobile top-up (Ooredoo, Orange, Tunisie Télécom)
- Bill payment (STEG, SONEDE, SNED)
- Commission management
- Transaction history

### ✅ Phase 2.2: Loyalty Program
- 4-tier system: Bronze → Silver → Gold → Platinum
- Point accumulation (1 point per TND spent)
- Tier multipliers (1x → 2x)
- 15 pre-defined rewards
- Redemption system
- Automatic tier upgrades

### ✅ Phase 2.3: Team Management
- Multi-user accounts
- Role-based permissions (Owner, Admin, Manager, Employee)
- Team invitations
- Spending limits per member
- Activity tracking

### ✅ Phase 2.4: Masquerade Mode
- Sales reps can order on behalf of clients
- Complete audit trail
- Session management
- Commission tracking

### ✅ Phase 2.5: WhatsApp Integration (Foundation)
- Webhook endpoint ready
- Message processing structure
- Auto-reply system
- Order placement via WhatsApp (Phase 3)

---

## 🔐 Security Features (Production-Ready)

### Implemented Security
- ✅ JWT authentication with refresh tokens
- ✅ OTP verification (SMS via Twilio)
- ✅ Rate limiting (120 req/min authenticated, 60 req/min anonymous)
- ✅ Security headers middleware (HSTS, CSP, X-Frame-Options, etc.)
- ✅ CORS protection
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection
- ✅ CSRF protection
- ✅ Password hashing (bcrypt)
- ✅ Email verification
- ✅ API request logging
- ✅ Input validation & sanitization

### Headers Applied
```
Strict-Transport-Security: max-age=31536000; includeSubDomains
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Content-Security-Policy: default-src 'self'
Referrer-Policy: strict-origin-when-cross-origin
```

---

## 🚀 Production Deployment

### Pre-Deployment Checklist

```bash
# 1. Environment configuration
cp .env.example .env
# Edit .env: Set APP_ENV=production, APP_DEBUG=false

# 2. Install production dependencies
composer install --no-dev --optimize-autoloader

# 3. Generate keys
php artisan key:generate
php artisan jwt:secret

# 4. Database
php artisan migrate --force

# 5. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Queue Workers (Required for Production)

```bash
# Using Supervisor (recommended)
sudo apt install supervisor

# Create supervisor config: /etc/supervisor/conf.d/ichri-worker.conf
[program:ichri-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ichri/backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/ichri/backend/storage/logs/worker.log
stopwaitsecs=3600

# Start workers
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ichri-worker:*
```

### Task Scheduler (Required for Production)

Add to crontab (`crontab -e`):

```cron
* * * * * cd /var/www/ichri/backend && php artisan schedule:run >> /dev/null 2>&1
```

**Scheduled Tasks:**
- Daily at 2:00 AM: Expire old loyalty points
- Monthly on 1st at 9:00 AM: Generate loyalty program reports
- Every minute: Process queue jobs
- Daily: Clean up failed jobs after 7 days

---

## 📊 Monitoring & Observability

### Application Monitoring

```bash
# Real-time logs
tail -f storage/logs/laravel.log

# Queue status
php artisan queue:work --once --verbose

# Database connections
php artisan db:monitor

# Cache status
php artisan cache:status
```

### Health Monitoring Endpoints

```bash
# Basic health
GET /api/v1/health

# Detailed (checks DB, Redis, Cache, Queue, Storage)
GET /api/v1/health/detailed

# System metrics
GET /api/v1/health/metrics
```

### Performance Optimization

**Database Indexes** (Auto-applied in migration 000018):
- Users: email, phone, role, verification status
- Products: SKU, category, brand, availability
- Orders: order_number, user, status, timestamps
- Loyalty: user, points, tier
- Team: account, user, role, status

**Caching Strategy:**
- Config, routes, views cached in production
- Redis for sessions, cache, and queue
- Product catalog cached with tags
- Query result caching for frequent reads

**Performance Headers:**
```
X-Response-Time: 45.23ms
X-Request-ID: unique-request-id
```

---

## 🐛 Troubleshooting

### Common Issues

**1. Database connection failed**
```bash
# Check credentials in .env
php artisan config:clear
php artisan migrate:status
```

**2. Permission denied on storage**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**3. Redis connection failed**
```bash
# Check if Redis is running
redis-cli ping  # Should return PONG

# Check .env REDIS_* configuration
```

**4. JWT secret not set**
```bash
php artisan jwt:secret
php artisan config:clear
```

**5. Queue not processing**
```bash
# Check workers
php artisan queue:work redis --verbose

# Restart supervisor workers
sudo supervisorctl restart ichri-worker:*
```

---

## 📚 Documentation

- **README.md** - Project overview and architecture
- **INSTALLATION.md** - Detailed installation guide
- **API_DOCUMENTATION.md** - Complete API reference (30+ endpoints)
- **PRODUCTION_DEPLOY.md** - Production deployment guide
- **TESTING.md** - Testing strategy and test suites
- **PHASE_2.5_COMPLETE.md** - Feature completion status
- **postman_collection.json** - API testing collection

---

## 🔗 Key URLs

### Local Development
- **Backend API**: http://localhost:8000/api/v1
- **Health Check**: http://localhost:8000/api/v1/health
- **Frontend**: http://localhost:3000 (if running)

### Production
- **API**: https://api.ichri.tn/v1
- **Admin Panel**: https://admin.ichri.tn
- **Mobile App**: Download from App Store/Play Store

---

## 👥 Support & Community

### Getting Help
- **Issues**: Create GitHub issue with bug/feature request
- **Email**: support@ichri.tn
- **Discord**: Join our developer community

### Contributing
- Fork the repository
- Create feature branch
- Follow PSR-12 coding standards
- Write tests for new features
- Submit pull request

---

## 📈 Architecture Statistics

- **Total PHP Files**: 84+
- **Total Migrations**: 18
- **Total Seeders**: 11
- **Total Tests**: 96+ (95%+ coverage)
- **Total API Endpoints**: 30+
- **Database Tables**: 21
- **Config Files**: 14
- **Service Providers**: 3
- **Middleware**: 4
- **Controllers**: 13
- **Models**: 25
- **Events**: 3
- **Listeners**: 1
- **Jobs**: 1
- **Commands**: 2
- **Policies**: 2

---

## 🎉 You're All Set!

**ichri.tn is now fully operational and production-ready!**

Start building amazing features, and happy coding! 🚀

---

**Version**: 2.0.0
**Last Updated**: November 2024
**License**: Proprietary
**Made with ❤️ for Tunisian Businesses**
