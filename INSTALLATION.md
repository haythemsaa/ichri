# Guide d'Installation ichri.tn

Ce guide vous aidera à installer et démarrer l'application ichri.tn localement.

## 📋 Prérequis

- **Docker** 20.10+
- **Docker Compose** 2.0+
- **Git**
- **Node.js** 18+ (pour le développement local)
- **PHP** 8.2+ (pour le développement local)
- **Composer** (pour le développement local)

## 🚀 Installation Rapide avec Docker

### 1. Cloner le repository

```bash
git clone https://github.com/haythemsaa/ichri.git
cd ichri
```

### 2. Lancer l'application

```bash
# Utiliser le script de déploiement
./deploy.sh development

# Ou utiliser docker-compose directement
docker-compose up -d
```

L'application sera disponible sur:
- **API Backend:** http://localhost:8000
- **Dashboard Web:** http://localhost:3000
- **MySQL:** localhost:3306
- **Redis:** localhost:6379
- **ElasticSearch:** http://localhost:9200

### 3. Initialiser la base de données

```bash
# Entrer dans le container backend
docker-compose exec backend bash

# Générer la clé d'application
php artisan key:generate

# Générer le secret JWT
php artisan jwt:secret

# Exécuter les migrations
php artisan migrate

# Charger les données de test
php artisan db:seed
```

### 4. Tester l'API

```bash
# Test de santé
curl http://localhost:8000/api/v1/health

# Réponse attendue:
# {"status":"ok","timestamp":"2024-11-18T10:30:00+00:00","version":"1.0.0"}
```

## 💻 Installation pour Développement Local

### Backend (Laravel)

```bash
cd backend

# Installer les dépendances
composer install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Générer le secret JWT
php artisan jwt:secret

# Configurer la base de données dans .env
# DB_HOST=localhost
# DB_DATABASE=ichri
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Exécuter les migrations
php artisan migrate --seed

# Lancer le serveur de développement
php artisan serve
```

L'API sera disponible sur http://localhost:8000

### Application Mobile (React Native)

```bash
cd mobile

# Installer les dépendances
npm install

# iOS
cd ios && pod install && cd ..
npx react-native run-ios

# Android
npx react-native run-android
```

### Dashboard Web (Next.js)

```bash
cd web

# Installer les dépendances
npm install

# Lancer le serveur de développement
npm run dev
```

Le dashboard sera disponible sur http://localhost:3000

## 🔧 Configuration

### Variables d'environnement

#### Backend (.env)

```env
APP_NAME=ichri.tn
APP_ENV=local
APP_KEY=base64:...
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ichri
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=localhost
REDIS_PORT=6379

ELASTICSEARCH_HOST=localhost
ELASTICSEARCH_PORT=9200

# JWT
JWT_SECRET=...
JWT_TTL=1440

# Twilio (SMS)
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=+216XXXXXXXX

# Payment Gateways
CARTUNISIE_MERCHANT_ID=
CARTUNISIE_API_KEY=

# Google Maps
GOOGLE_MAPS_API_KEY=
```

#### Mobile (src/config/api.js)

```javascript
const API_BASE_URL = __DEV__
  ? 'http://localhost:8000/api/v1'  // Pour émulateur Android: http://10.0.2.2:8000/api/v1
  : 'https://api.ichri.tn/api/v1';
```

#### Web (.env.local)

```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
```

## 🧪 Tests

### Backend

```bash
cd backend

# Tests PHPUnit
php artisan test

# Tests avec couverture
php artisan test --coverage
```

### Mobile

```bash
cd mobile

# Tests Jest
npm test

# Tests avec couverture
npm test -- --coverage
```

### Web

```bash
cd web

# Tests Jest
npm test

# Tests E2E avec Playwright
npm run test:e2e
```

## 📊 Base de données

### Seeders disponibles

```bash
# Charger toutes les données de test
php artisan db:seed

# Seeders spécifiques
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=BrandSeeder
```

### Migrations

```bash
# Exécuter les migrations
php artisan migrate

# Rollback
php artisan migrate:rollback

# Reset et re-migration
php artisan migrate:fresh --seed
```

## 🐛 Debugging

### Logs

```bash
# Backend logs
tail -f backend/storage/logs/laravel.log

# Docker logs
docker-compose logs -f backend
docker-compose logs -f web

# Logs d'un service spécifique
docker-compose logs -f mysql
```

### Problèmes courants

#### Port déjà utilisé

```bash
# Vérifier les ports utilisés
sudo lsof -i :8000
sudo lsof -i :3000

# Tuer le processus
kill -9 <PID>
```

#### Permission denied sur storage/

```bash
cd backend
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

#### ElasticSearch ne démarre pas

```bash
# Augmenter la mémoire virtuelle (Linux)
sudo sysctl -w vm.max_map_count=262144

# Permanent
echo "vm.max_map_count=262144" | sudo tee -a /etc/sysctl.conf
```

## 📱 Configuration Mobile

### iOS

```bash
cd mobile/ios
pod install
```

### Android

Assurez-vous que:
- Android Studio est installé
- SDK Android 33+ est installé
- Un émulateur ou appareil est connecté

```bash
# Lister les appareils
adb devices

# Lancer l'app
npx react-native run-android
```

## 🚀 Déploiement

### Production

```bash
# Utiliser le script de déploiement
./deploy.sh production

# Ou manuellement
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Staging

```bash
./deploy.sh staging
```

## 📚 Documentation API

Une fois l'application lancée, accédez à:
- **Swagger UI:** http://localhost:8000/api/documentation
- **Postman Collection:** disponible dans `/docs/postman/`

## 🆘 Support

- **GitHub Issues:** https://github.com/haythemsaa/ichri/issues
- **Email:** support@ichri.tn
- **Documentation:** https://docs.ichri.tn

## 📝 License

MIT License - voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

Fait avec ❤️ en Tunisie 🇹🇳
