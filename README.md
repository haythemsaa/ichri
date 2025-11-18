# ichri.tn - Plateforme B2B pour Commerces de Proximité

<div align="center">

![ichri.tn](https://via.placeholder.com/800x200/4F46E5/FFFFFF?text=ichri.tn)

**La première plateforme B2B d'approvisionnement pour les épiceries en Tunisie**

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel)](https://laravel.com)
[![React Native](https://img.shields.io/badge/React_Native-0.72-61DAFB?logo=react)](https://reactnative.dev)
[![Next.js](https://img.shields.io/badge/Next.js-14-000000?logo=next.js)](https://nextjs.org)

</div>

## 📋 À Propos

ichri.tn (signifie "J'achète" en dialecte tunisien) digitalise l'approvisionnement des épiceries et commerces de proximité en Tunisie. La plateforme permet aux épiciers de:

- 🛒 Commander 1000+ produits FMCG en quelques clics
- 🚚 Bénéficier de livraison gratuite en moins de 24h
- 💳 Accéder à des services de crédit adaptés
- 📊 Optimiser leur gestion via analytics

### Marché Cible
- **40,000+** épiceries en Tunisie
- **Objectif Année 1:** 5,000 épiceries actives
- **GMV Année 1:** 30M TND

## 🏗️ Architecture

```
ichri/
├── backend/              # API Laravel 11
│   ├── app/
│   │   ├── Services/
│   │   │   ├── Auth/
│   │   │   ├── Catalog/
│   │   │   ├── Order/
│   │   │   ├── Payment/
│   │   │   ├── Delivery/
│   │   │   └── Analytics/
│   │   └── Models/
│   └── database/
├── mobile/               # Application React Native
│   ├── src/
│   │   ├── screens/
│   │   ├── components/
│   │   ├── navigation/
│   │   └── services/
│   └── android/
├── web/                  # Dashboard Next.js 14
│   ├── app/
│   ├── components/
│   └── lib/
├── infrastructure/       # Configuration DevOps
│   ├── docker/
│   ├── kubernetes/
│   └── terraform/
└── docs/                 # Documentation
```

## 🚀 Stack Technique

### Backend
- **Framework:** Laravel 11 (PHP 8.2+)
- **Base de données:** MySQL 8.0
- **Cache:** Redis
- **Recherche:** ElasticSearch
- **Analytics:** PostgreSQL
- **Queue:** Redis/SQS

### Mobile
- **Framework:** React Native 0.72+
- **State Management:** Redux Toolkit
- **Navigation:** React Navigation 6
- **Maps:** react-native-maps
- **Notifications:** OneSignal

### Web
- **Framework:** Next.js 14
- **Styling:** TailwindCSS
- **UI Components:** ShadcnUI
- **State:** React Context + SWR

### Infrastructure
- **Cloud:** AWS / OVH Tunisie
- **Containers:** Docker + Kubernetes
- **CI/CD:** GitHub Actions
- **Monitoring:** Datadog, Sentry

## 📦 Installation

### Prérequis
- PHP 8.2+
- Node.js 18+
- Composer
- Docker & Docker Compose
- MySQL 8.0
- Redis

### Installation Rapide avec Docker

```bash
# Cloner le repository
git clone https://github.com/haythemsaa/ichri.git
cd ichri

# Lancer avec Docker Compose
docker-compose up -d

# L'API sera disponible sur http://localhost:8000
# L'app web sur http://localhost:3000
```

### Installation Manuelle

#### Backend (Laravel)

```bash
cd backend

# Installer les dépendances
composer install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate --seed

# Lancer le serveur
php artisan serve
```

#### Mobile (React Native)

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

#### Web (Next.js)

```bash
cd web

# Installer les dépendances
npm install

# Lancer en mode dev
npm run dev

# Build production
npm run build
npm start
```

## 🔑 Fonctionnalités Principales

### Pour les Épiciers

✅ **Authentification Sécurisée**
- Inscription via numéro de téléphone + SMS OTP
- Connexion biométrique (empreinte/Face ID)
- Gestion de profil et documents KYC

✅ **Catalogue Produits**
- 1000+ produits FMCG
- Recherche full-text avec autocomplétion
- Scan code-barres
- Filtres avancés (prix, marque, promo)

✅ **Gestion des Commandes**
- Panier intelligent avec suggestions
- Réapprovisionnement rapide
- Suivi GPS temps réel
- Historique complet

✅ **Paiement Flexible**
- Cash à la livraison
- Paiement mobile (Cartunisie, PayMe, D-Dinar)
- Crédit ichri.tn (7-30 jours)
- Virement bancaire

✅ **Livraison Optimisée**
- Livraison gratuite en <24h
- Tracking GPS en temps réel
- Notifications multi-canal (SMS, Push, Email)
- Signature électronique

### Pour l'Administration

📊 **Dashboard Analytique**
- KPIs en temps réel
- Rapports de ventes
- Gestion des épiciers
- Suivi logistique

👥 **Gestion des Utilisateurs**
- Validation des inscriptions
- Scoring de crédit
- Historique des transactions

📦 **Gestion du Catalogue**
- CRUD produits
- Gestion des stocks
- Promotions et prix
- Import/Export CSV

🚚 **Gestion des Livraisons**
- Attribution automatique des livreurs
- Optimisation des tournées
- Suivi en temps réel
- Gestion des incidents

## 📱 Captures d'Écran

### Application Mobile

<div align="center">

| Onboarding | Catalogue | Panier | Suivi |
|------------|-----------|--------|-------|
| ![Onboarding](https://via.placeholder.com/200x400/4F46E5/FFFFFF?text=Onboarding) | ![Catalog](https://via.placeholder.com/200x400/4F46E5/FFFFFF?text=Catalogue) | ![Cart](https://via.placeholder.com/200x400/4F46E5/FFFFFF?text=Panier) | ![Tracking](https://via.placeholder.com/200x400/4F46E5/FFFFFF?text=Suivi) |

</div>

## 🔐 API Documentation

L'API REST est documentée avec OpenAPI (Swagger).

**Base URL:** `https://api.ichri.tn/api/v1`

### Endpoints Principaux

```bash
# Authentication
POST   /auth/register          # Inscription
POST   /auth/login             # Connexion
POST   /auth/verify-otp        # Vérification SMS
POST   /auth/refresh           # Refresh token

# Catalog
GET    /catalog/products       # Liste produits
GET    /catalog/products/{id}  # Détail produit
GET    /catalog/categories     # Catégories
GET    /catalog/search         # Recherche

# Orders
POST   /orders                 # Créer commande
GET    /orders                 # Liste commandes
GET    /orders/{id}            # Détail commande
PUT    /orders/{id}/cancel     # Annuler commande

# Payments
POST   /payments/process       # Traiter paiement
GET    /payments/methods       # Méthodes de paiement
GET    /credit/check           # Vérifier éligibilité crédit

# Delivery
GET    /deliveries/{id}/track  # Tracking livraison
PUT    /deliveries/{id}/status # Mettre à jour statut
```

Documentation complète: [api.ichri.tn/docs](https://api.ichri.tn/docs)

## 🧪 Tests

```bash
# Backend - Tests PHPUnit
cd backend
php artisan test

# Backend - Tests Pest
php artisan test --pest

# Mobile - Tests Jest
cd mobile
npm test

# Web - Tests Jest + Playwright
cd web
npm test
npm run test:e2e
```

## 📊 KPIs et Métriques

### Business KPIs
- **GMV (Gross Merchandise Value):** Objectif 30M TND An 1
- **Épiciers actifs:** Objectif 5,000 An 1
- **Panier moyen:** Objectif 250-300 TND
- **Fréquence de commande:** Objectif 3.5/mois
- **Taux de rétention M3:** Objectif >70%

### Operational KPIs
- **Livraison à l'heure:** Objectif >95%
- **Délai moyen livraison:** Objectif <22h
- **NPS (Net Promoter Score):** Objectif >60
- **Note app:** Objectif >4.5/5
- **Uptime API:** Objectif >99.5%

## 🚀 Déploiement

### Environnements

- **Production:** `https://ichri.tn`
- **Staging:** `https://staging.ichri.tn`
- **Development:** `http://localhost:8000`

### CI/CD Pipeline

```yaml
# GitHub Actions
1. Tests automatiques (PHPUnit, Jest)
2. Analyse de code (PHPStan, ESLint)
3. Build Docker images
4. Deploy sur environnement cible
5. Tests end-to-end
6. Notifications Slack
```

### Commandes de Déploiement

```bash
# Déploiement staging
./deploy.sh staging

# Déploiement production
./deploy.sh production

# Rollback
./deploy.sh rollback
```

## 🤝 Contribution

Les contributions sont les bienvenues! Merci de suivre ces étapes:

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

### Standards de Code

- **PHP:** PSR-12, Laravel Best Practices
- **JavaScript:** ESLint + Prettier
- **Commits:** Conventional Commits
- **Tests:** Couverture >80%

## 📄 License

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👥 Équipe

- **Chef de Projet:** [Nom]
- **CTO:** [Nom]
- **Responsable Produit:** [Nom]
- **Responsable Commercial:** [Nom]

## 📞 Contact

- **Email:** support@ichri.tn
- **Site web:** [www.ichri.tn](https://www.ichri.tn)
- **Documentation:** [docs.ichri.tn](https://docs.ichri.tn)
- **Status:** [status.ichri.tn](https://status.ichri.tn)

## 🙏 Remerciements

Inspiré par Chari.ma (Maroc) - Merci pour avoir montré la voie!

---

<div align="center">

**[Site Web](https://ichri.tn)** • **[Documentation](https://docs.ichri.tn)** • **[API](https://api.ichri.tn/docs)** • **[Support](mailto:support@ichri.tn)**

Fait avec ❤️ en Tunisie 🇹🇳

</div>
