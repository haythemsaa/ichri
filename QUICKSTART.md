# 🚀 Quick Start Guide - ichri.tn

## Prerequisites

- **Docker** & **Docker Compose** installed
- **Git** installed
- **8GB RAM** minimum
- **10GB** free disk space

---

## 🏃 Start in 5 Minutes

### 1. Clone the Repository
```bash
git clone https://github.com/haythemsaa/ichri.git
cd ichri
```

### 2. Start with Docker (Recommended)
```bash
# Start all services
docker-compose up -d

# Wait 30 seconds for services to initialize...

# Check if everything is running
docker-compose ps
```

### 3. Setup Backend
```bash
# Enter backend container
docker-compose exec backend bash

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Run migrations
php artisan migrate

# Seed database with demo data
php artisan db:seed

# Exit container
exit
```

### 4. Access the Application

✅ **API Backend**: http://localhost:8000
✅ **Web Dashboard**: http://localhost:3000
✅ **API Documentation**: http://localhost:8000/api/documentation

---

## 🧪 Test Accounts

### Grocer Account
- **Phone**: `+21698123456`
- **Password**: `Password123`

### Admin Account
- **Email**: `admin@ichri.tn`
- **Password**: `admin123`

---

## 📱 Test the API

### 1. Login
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+21698123456",
    "password": "Password123"
  }'
```

Copy the `token` from the response.

### 2. Get Products
```bash
curl http://localhost:8000/api/v1/catalog/products \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 3. Create Karny Customer
```bash
curl -X POST http://localhost:8000/api/v1/karny/customers \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ahmed Ben Ali",
    "phone": "+21698765432",
    "credit_limit": 500
  }'
```

---

## 🧪 Run Tests

```bash
# Backend tests
cd backend
php artisan test

# With coverage
php artisan test --coverage

# Specific test suite
php artisan test --filter=KarnyTest
```

---

## 🛑 Stop Everything

```bash
# Stop all services
docker-compose down

# Stop and remove volumes (⚠️ deletes all data)
docker-compose down -v
```

---

## 🔧 Development Mode

### Backend (Laravel)
```bash
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate keys
php artisan key:generate
php artisan jwt:secret

# Run migrations
php artisan migrate

# Start dev server
php artisan serve
```

### Mobile (React Native)
```bash
cd mobile

# Install dependencies
npm install

# Start Metro bundler
npm start

# Run on Android
npm run android

# Run on iOS
npm run ios
```

### Web (Next.js)
```bash
cd web

# Install dependencies
npm install

# Start dev server
npm run dev
```

---

## 📚 Next Steps

- 📖 Read [API_DOCUMENTATION.md](./API_DOCUMENTATION.md)
- 🧪 Check [TESTING.md](./backend/TESTING.md)
- 🔥 Explore [NOUVELLES_FONCTIONNALITES.md](./NOUVELLES_FONCTIONNALITES.md)
- 📊 Review [ANALYSE_CONCURRENTIELLE.md](./docs/ANALYSE_CONCURRENTIELLE.md)

---

## 🆘 Troubleshooting

### Port Already in Use
```bash
# Find process using port 8000
lsof -i :8000

# Kill the process
kill -9 PID
```

### Database Connection Error
```bash
# Restart MySQL container
docker-compose restart mysql

# Check MySQL logs
docker-compose logs mysql
```

### Clear Cache
```bash
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan config:clear
docker-compose exec backend php artisan route:clear
```

---

## 💬 Support

- 📧 Email: support@ichri.tn
- 💬 Slack: ichri-tn.slack.com
- 📚 Docs: https://docs.ichri.tn

---

**🇹🇳 Built with ❤️ in Tunisia**
