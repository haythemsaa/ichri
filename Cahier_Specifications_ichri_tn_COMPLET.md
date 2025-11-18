# CAHIER DES SPÉCIFICATIONS FONCTIONNELLES DÉTAILLÉES
## ichri.tn - Plateforme B2B pour Commerces de Proximité en Tunisie

**Version:** 1.0  
**Date:** Novembre 2024  
**Statut:** Document final

---

## RÉSUMÉ EXÉCUTIF

ichri.tn est une plateforme B2B tunisienne qui digitalise l'approvisionnement des épiceries et commerces de proximité. Inspirée de Chari.ma (Maroc), elle permet aux épiciers de:
- Commander 1000+ produits FMCG en quelques clics
- Bénéficier de livraison gratuite en <24h
- Accéder à des services de crédit adaptés
- Optimiser leur gestion via analytics

**Marché cible:** 40,000+ épiceries en Tunisie  
**Objectif Année 1:** 5,000 épiceries actives, 30M TND GMV

---

## TABLE DES MATIÈRES

### PARTIE I - VISION ET STRATÉGIE
1. Présentation Générale
2. Analyse du Marché Tunisien
3. Proposition de Valeur
4. Modèle Économique

### PARTIE II - SPÉCIFICATIONS FONCTIONNELLES
5. Parcours Utilisateurs (User Stories)
6. Modules Applicatifs Détaillés
7. Workflows et Processus Métier

### PARTIE III - ARCHITECTURE ET TECHNOLOGIE
8. Stack Technique
9. Architecture Système
10. Intégrations et APIs

### PARTIE IV - DÉPLOIEMENT ET OPÉRATIONS
11. Plan de Déploiement
12. Stratégie Go-to-Market
13. KPIs et Mesure de Performance

---

## PARTIE I - VISION ET STRATÉGIE

### 1. PRÉSENTATION GÉNÉRALE

**ichri.tn** (signifie "J'achète" en dialecte tunisien) est la première plateforme B2B d'approvisionnement pour les commerces de proximité en Tunisie.

**Problème résolu:**
Les épiciers tunisiens font face à:
- Approvisionnement chronophage (déplacements chez les grossistes)
- Manque de transparence des prix
- Ruptures de stock fréquentes
- Difficulté d'accès au crédit
- Absence d'outils de gestion

**Solution ichri.tn:**
Plateforme tout-en-un offrant:
✅ Catalogue digital de 1000+ produits FMCG
✅ Commande mobile simple et rapide
✅ Livraison gratuite J+1
✅ Prix transparents et compétitifs
✅ Crédit et paiement flexible
✅ Analytics et outils de gestion

### 2. ANALYSE DU MARCHÉ TUNISIEN

#### 2.1 Taille du Marché

**Commerce traditionnel en Tunisie:**
- 40,000+ points de vente (épiceries, supérettes)
- 60-70% du retail alimentaire
- Marché estimé: 8 milliards TND/an
- Croissance: 5-7% par an

**Segmentation géographique:**

| Région | Épiceries | Priorité | Caractéristiques |
|--------|-----------|----------|------------------|
| Grand Tunis | 12,000 | ⭐⭐⭐ | Dense, connecté, compétitif |
| Sfax | 4,000 | ⭐⭐⭐ | Hub commercial, entrepreneur |
| Sousse-Monastir | 5,000 | ⭐⭐⭐ | Tourisme, pouvoir d'achat élevé |
| Nabeul-Hammamet | 3,000 | ⭐⭐ | Saisonnier, touristique |
| Autres | 16,000 | ⭐ | Rural, moins digitalisé |

#### 2.2 Spécificités Tunisiennes

**vs Maroc (Chari.ma):**

**Avantages Tunisie:**
- Géographie plus compacte (distances plus courtes)
- Taux de bancarisation plus élevé (37% vs 30%)
- Adoption smartphone élevée (>75%)
- Marché moins saturé (moins de concurrence B2B)

**Défis Tunisie:**
- Économie plus fragile (inflation, change)
- Crédit plus risqué
- Logistique rurale complexe
- Régulation bancaire stricte (BCT)

### 3. PROPOSITION DE VALEUR

#### 3.1 Pour les Épiciers

**Gains de temps:**
- Commande en 3 minutes vs 2-3h de déplacement
- Livraison à domicile vs transport personnel
- Pas de queues ni d'attente

**Économies:**
- Prix grossiste direct (-5 à 15% vs intermédiaires)
- Pas de coûts de transport
- Optimisation stocks = moins de gaspillage

**Services financiers:**
- Crédit 7-30 jours (améliore trésorerie)
- Paiement flexible
- Pas de garanties complexes

**Outils de gestion:**
- Historique d'achats
- Analytics et statistiques
- Carnet de crédit clients (Karny.tn)

#### 3.2 Pour les Marques FMCG

**Visibilité et distribution:**
- Accès direct à 5,000+ épiceries
- Mise en avant produits sur l'app
- Campagnes promo ciblées

**Data et insights:**
- Données de ventes précises (SKU level)
- Comportements d'achat
- Couverture géographique
- ROI marketing mesurable

**Efficacité opérationnelle:**
- Commandes regroupées = moins de visites commerciales
- Paiement sécurisé
- Réduction coûts de distribution

### 4. MODÈLE ÉCONOMIQUE

#### 4.1 Sources de Revenus

**1. Marge commerciale (70% des revenus):**
```
Marge moyenne: 12-18% sur produits
Exemple:
- Prix achat fournisseur: 100 TND
- Prix vente épicier: 115 TND
- Marge ichri.tn: 15 TND
```

**2. Services marketing marques (15%):**
- Bannières promotionnelles: 500-2000 TND/semaine
- Campagnes push ciblées: 0.20 TND/épicier
- Placement produits: 200-1000 TND/mois
- Rapports analytics: 500-5000 TND/trimestre

**3. Services financiers (10%):**
- Intérêts sur crédit (après période promo): 2-5%/mois
- Frais de dossier crédit: 0-2%
- Assurance crédit: 1% du montant

**4. Abonnements premium (5%):**
- Outils analytics avancés: 50 TND/mois
- Multi-boutiques: 100 TND/mois
- API access: 200 TND/mois

#### 4.2 Structure de Coûts

**OPEX (Coûts Opérationnels):**
- **Logistique (40%):** Entrepôts, livreurs, véhicules, carburant
- **Personnel (25%):** Tech, commercial, support, ops
- **Marketing (15%):** Acquisition, retention, brand
- **Tech (10%):** Serveurs, licences, maintenance
- **Général (10%):** Loyers, utilities, admin

#### 4.3 Projections Financières (Année 1)

**Hypothèses:**
- 5,000 épiceries actives fin année 1
- Montée en charge progressive (200/mois)
- Panier moyen: 250 TND
- Fréquence: 3.5 commandes/mois
- Marge moyenne: 15%

**P&L Simplifié:**

| Mois | Épiciers Actifs | GMV (TND) | Revenus (TND) | OPEX (TND) | EBITDA (TND) |
|------|----------------|-----------|---------------|------------|--------------|
| M1-3 | 200-600 | 525K | 79K | 120K | -41K |
| M4-6 | 800-1,400 | 2.1M | 315K | 180K | +135K |
| M7-9 | 1,800-3,000 | 5.3M | 795K | 280K | +515K |
| M10-12 | 3,600-5,000 | 10.5M | 1.6M | 400K | +1.2M |
| **Total An 1** | **5,000** | **30M** | **4.5M** | **2.4M** | **+2.1M** |

**Break-even:** Mois 4  
**EBITDA Année 1:** +2.1M TND (+47% marge)

---

## PARTIE II - SPÉCIFICATIONS FONCTIONNELLES

### 5. PARCOURS UTILISATEURS (USER STORIES)

#### 5.1 Épicier - Première Utilisation

**US-001: Découverte et Inscription**

**Contexte:** Ahmed, épicier à Sfax, entend parler d'ichri.tn par un collègue.

**Parcours:**
1. **Téléchargement:** 
   - Recherche "ichri" sur Google Play
   - Télécharge l'app (15 MB)
   - Ouvre l'app

2. **Onboarding (3 écrans):**
   - Écran 1: "Commandez vos produits en 3 clics"
   - Écran 2: "Livraison gratuite en moins de 24h"
   - Écran 3: "Crédit jusqu'à 30 jours"
   - [Commencer] ou [J'ai déjà un compte]

3. **Inscription (2 minutes):**
   - Numéro téléphone: +216 98 XXX XXX
   - Code SMS (6 chiffres)
   - Nom complet: Ahmed Ben Salah
   - Nom boutique: Épicerie Essalem
   - Adresse: Rue République, Sfax
   - Type: Épicerie
   - Mot de passe (8+ caractères)
   - ✅ J'accepte les CGU
   - [Créer mon compte]

4. **Documents (optionnel - peut passer):**
   - Photo Patente
   - Photo CIN
   - Photo devanture
   - [Envoyer] ou [Plus tard]

5. **Bienvenue:**
   - "Bienvenue Ahmed! 🎉"
   - "Votre compte est créé"
   - "Profitez de -10% sur votre 1ère commande"
   - [Découvrir les produits]

**Résultat:** Compte créé, peut commander immédiatement

---

**US-002: Première Commande**

**Parcours:**
1. **Découverte catalogue:**
   - Page d'accueil avec catégories
   - Clique sur "Produits laitiers"
   - Voit 247 produits

2. **Recherche produit:**
   - Tape "lait" dans la barre de recherche
   - Voit 45 résultats
   - Filtre par marque: "Vitalait"

3. **Ajout au panier:**
   - Sélectionne "Lait Vitalait 1L"
   - Voit le prix: 1.450 TND
   - Change quantité à 24 (1 carton)
   - [Ajouter au panier]
   - Continue ses achats

4. **Validation panier:**
   - Ajoute 10 autres produits
   - Total panier: 285 TND, 15 articles
   - Voit "Livraison GRATUITE ✅"
   - [Commander]

5. **Choix livraison:**
   - Adresse confirmée: Épicerie Essalem, Sfax
   - Livraison standard: Demain avant 18h (GRATUIT)
   - [Continuer]

6. **Paiement:**
   - Sélectionne: Cash à la livraison
   - Coche: ✅ J'accepte les CGV
   - [Valider la commande]

7. **Confirmation:**
   - "Commande confirmée! #ICH2024-000001"
   - Reçoit SMS: "Votre commande sera livrée demain"
   - [Suivre ma commande]

**Résultat:** Première commande passée en 5 minutes

---

**US-003: Suivi et Réception**

**Le lendemain:**

1. **Notifications:**
   - 10h: Push "Votre commande est en préparation"
   - 15h: Push "Mohamed part livrer votre commande"
   - 16h: SMS "Mohamed arrive dans 15 minutes"

2. **Tracking live:**
   - Ouvre l'app
   - Voit la carte avec position du livreur
   - Distance: 1.2 km
   - ETA: 8 minutes

3. **Arrivée livreur:**
   - Push "Mohamed est arrivé"
   - Vérifie les produits
   - Tous conformes ✅

4. **Paiement:**
   - Paie 285 TND en espèces
   - Livreur rend la monnaie
   - Signature électronique

5. **Évaluation:**
   - Note la livraison: ⭐⭐⭐⭐⭐
   - Commentaire: "Excellent service, très rapide!"
   - [Envoyer]

6. **Post-livraison:**
   - Reçoit reçu par SMS
   - Email avec facture PDF
   - Gagne 28 points fidélité

**Résultat:** Première expérience positive, client conquis

#### 5.2 Épicier - Utilisation Récurrente

**US-004: Réapprovisionnement Hebdomadaire**

Ahmed commande maintenant chaque semaine:

1. **Ouvre l'app:**
   - Voit "Bonjour Ahmed"
   - Section "Commandez à nouveau"
   - Affiche sa dernière commande

2. **Commande rapide:**
   - [Recommander] sur la dernière commande
   - Système ajoute les 15 produits au panier
   - Ajuste quelques quantités
   - Ajoute 2 nouveaux produits en promo

3. **Paiement avec crédit:**
   - Total: 310 TND
   - Sélectionne: Crédit ichri.tn (30 jours)
   - Crédit disponible: 1,850 TND
   - [Valider]

4. **Livraison le lendemain:**
   - Processus habituel
   - Tout se passe bien

**Temps total:** 2 minutes (vs 5 min première fois)

---

### 6. MODULES APPLICATIFS DÉTAILLÉS

#### 6.1 Module Authentification

**Fonctionnalités:**
- Inscription (téléphone + SMS OTP)
- Connexion (téléphone + mot de passe)
- Mot de passe oublié
- Biométrie (empreinte/Face ID)
- Gestion profil
- Documents KYC

**Règles métier:**
- Téléphone unique par compte
- Code SMS valide 5 minutes
- Mot de passe: 8+ caractères, 1 majuscule, 1 chiffre
- Blocage après 5 tentatives échouées
- Session valide 30 jours

#### 6.2 Module Catalogue

**Structure:**
```
Catégories (12)
├── Produits Laitiers (247 produits)
│   ├── Lait (45)
│   ├── Yaourts (89)
│   ├── Fromages (78)
│   └── Beurre & Crème (35)
├── Boissons (312)
│   ├── Eau (23)
│   ├── Sodas (145)
│   ├── Jus (98)
│   └── Boissons chaudes (46)
...
```

**Fiche produit contient:**
- Photos (4-5 images HD)
- Nom, marque, format
- Code-barres / SKU
- Prix unitaire, pack, carton
- Promotions actives
- Stock (Disponible / Faible / Rupture)
- Description
- Informations nutritionnelles
- Note moyenne + avis clients

**Recherche:**
- Full-text search (ElasticSearch)
- Autocomplétion
- Recherche vocale
- Scan code-barres
- Filtres: prix, marque, promo, etc.

#### 6.3 Module Commandes

**Workflow complet:**

```
1. PANIER
   - Ajout produits
   - Calcul total temps réel
   - Suggestions cross-sell

2. VALIDATION
   ├─ Vérification stocks
   ├─ Sélection adresse
   ├─ Choix livraison (Standard/Express)
   └─ Instructions spéciales

3. PAIEMENT
   ├─ Cash
   ├─ Paiement mobile
   ├─ Crédit ichri.tn
   └─ Virement bancaire

4. CONFIRMATION
   ├─ Génération numéro commande
   ├─ Envoi SMS/Email/Push
   └─ Création workflow livraison

5. PRÉPARATION
   ├─ Picking entrepôt
   ├─ Contrôle qualité
   └─ Emballage + étiquette

6. EXPÉDITION
   ├─ Attribution livreur
   ├─ Optimisation tournée
   └─ Départ livraison

7. LIVRAISON
   ├─ Tracking GPS temps réel
   ├─ Notifications clients
   └─ Arrivée sur place

8. RÉCEPTION
   ├─ Vérification produits
   ├─ Paiement (si cash)
   ├─ Signature électronique
   └─ Confirmation livrée

9. POST-LIVRAISON
   ├─ Envoi reçu/facture
   ├─ Demande évaluation
   └─ Points fidélité
```

**États de commande:**
- 🟡 En attente confirmation
- 🟢 Confirmée
- 🔵 En préparation
- 🟣 Prête
- 🚚 En livraison
- ✅ Livrée
- ❌ Annulée
- 🔴 Problème

#### 6.4 Module Paiement & Crédit

**Modes de paiement:**

1. **Cash (60% des transactions):**
   - Paiement au livreur
   - Pas de frais
   - Reçu automatique

2. **Paiement mobile (25%):**
   - Cartunisie (carte bancaire)
   - PayMe (wallet)
   - D-Dinar (Poste)
   - 3D Secure obligatoire

3. **Crédit ichri.tn (13%):**
   - Délai: 7-30 jours
   - Limite: 500-15,000 TND selon profil
   - Taux: 0% (6 premiers mois)
   - Après: 2-5%/mois

4. **Virement bancaire (2%):**
   - Pour grosses commandes >1000 TND
   - Délai 24-48h

**Système de crédit:**

**Scoring automatique:**
```python
Score = (
    Ancienneté_compte * 20 +
    Nb_commandes * 15 +
    Panier_moyen * 10 +
    Taux_paiement_temps * 30 +
    Documents_KYC * 25
) / 100

Si Score >= 60 → Éligible crédit
```

**Niveaux:**
| Niveau | Limite | Délai | Conditions |
|--------|--------|-------|------------|
| Bronze | 500-1,500 | 7j | Nouveau client, documents OK |
| Silver | 1,500-5,000 | 15j | 10+ commandes |
| Gold | 5,000-15,000 | 30j | 50+ commandes |
| Platinum | Sur mesure | 60j | Gros clients, négociation |

---

## PARTIE III - ARCHITECTURE ET TECHNOLOGIE

### 7. STACK TECHNIQUE

#### 7.1 Frontend

**Mobile (React Native):**
- React Native 0.72+
- Redux Toolkit (state management)
- React Navigation 6
- axios (HTTP client)
- react-native-maps (geolocalisation)
- react-native-camera (scan codes-barres)
- OneSignal (push notifications)

**Web (React):**
- React 18+
- Next.js 14 (SSR)
- TailwindCSS
- ShadcnUI components

#### 7.2 Backend

**API (Laravel 11):**
- PHP 8.2+
- Laravel 11
- MySQL 8.0 (base principale)
- Redis (cache, queues)
- ElasticSearch (recherche)
- PostgreSQL (analytics)

**Architecture:**
```
API REST/GraphQL
├── Auth Service
├── Catalog Service
├── Order Service
├── Payment Service
├── Delivery Service
├── Analytics Service
└── Notification Service
```

#### 7.3 Infrastructure

**Cloud Provider:** AWS (ou OVH Tunisie pour data sovereignty)

**Services:**
- EC2 / ECS (compute)
- RDS (database)
- S3 (storage images/documents)
- CloudFront (CDN)
- ElastiCache (Redis)
- SQS (message queue)
- Lambda (serverless functions)

**DevOps:**
- Docker + Kubernetes
- GitHub Actions (CI/CD)
- Terraform (Infrastructure as Code)
- Datadog (monitoring)
- Sentry (error tracking)

### 8. ARCHITECTURE SYSTÈME

**Schéma high-level:**

```
┌─────────────────────────────────────────┐
│         UTILISATEURS                    │
├─────────────┬───────────────────────────┤
│ Mobile App  │  Web App  │  Livreur App  │
└─────────────┴───────────┴───────────────┘
              │
         [API Gateway]
              │
    ┌─────────┴─────────┐
    │   Load Balancer   │
    └─────────┬─────────┘
              │
    ┌─────────┴──────────────────────┐
    │     Microservices Cluster      │
    ├────────────────────────────────┤
    │ Auth│Catalog│Orders│Payments   │
    │ Delivery│Analytics│Notifications│
    └────────────────────────────────┘
              │
    ┌─────────┴─────────┐
    │    Databases      │
    ├───────────────────┤
    │ MySQL│Redis│ES    │
    │ PostgreSQL        │
    └───────────────────┘
```

**Flux de commande:**

```
Client Mobile
    ↓
API Gateway
    ↓
Order Service
    ├→ Catalog Service (check stock)
    ├→ Payment Service (process payment)
    ├→ Notification Service (SMS/push)
    └→ Warehouse System (create picking task)
    ↓
Livreur assigné
    ↓
Tracking GPS
    ↓
Livraison confirmée
```

### 9. INTÉGRATIONS ET APIS

#### 9.1 APIs Externes

**Paiement:**
- Cartunisie API (cartes bancaires)
- PayMe API (wallet mobile)
- D-Dinar API (Poste Tunisienne)

**SMS/Communication:**
- Twilio / Karix (SMS)
- OneSignal (push notifications)
- SendGrid (emails)

**Géolocalisation:**
- Google Maps API (routing, geocoding)
- OpenStreetMap (carte Tunisie)

**Autres:**
- AWS S3 (storage)
- Cloudinary (image optimization)

#### 9.2 APIs ichri.tn (pour partenaires)

**REST API publique:**

```
POST /api/v1/auth/login
POST /api/v1/orders
GET  /api/v1/orders/{id}
GET  /api/v1/catalog/products
GET  /api/v1/catalog/products/{id}
GET  /api/v1/analytics/sales
```

**Webhooks:**
```
order.created
order.confirmed
order.shipped
order.delivered
payment.completed
```

---

## PARTIE IV - DÉPLOIEMENT ET OPÉRATIONS

### 10. PLAN DE DÉPLOIEMENT

#### Phase 1: MVP (Mois 1-2)

**Scope:**
- App mobile Android (Tunis + Sfax uniquement)
- Catalogue 500 produits
- 2 entrepôts (Tunis, Sfax)
- Paiement cash uniquement
- Livraison J+1
- 100 épiciers beta

**Objectifs:**
- Valider product-market fit
- Tester logistique
- Ajuster pricing
- Collecter feedback

#### Phase 2: Launch (Mois 3-4)

**Scope:**
- App iOS lancée
- 1000+ produits
- Paiement mobile activé
- Crédit pour clients vérifiés
- 500 épiciers actifs

**Marketing:**
- Campagne terrain (sales rep)
- Pub Facebook/Instagram
- Partenariat influenceurs locaux
- Promo lancement (-20%)

#### Phase 3: Scale (Mois 5-12)

**Scope:**
- Expansion Sousse, Nabeul, Bizerte
- 1500+ produits
- Magasins physiques (click & collect)
- App web lancée
- Programme fidélité
- 5,000 épiciers actifs

**Optimisation:**
- Amélioration supply chain
- Automatisation processes
- Data analytics avancés
- Partenariats marques

### 11. STRATÉGIE GO-TO-MARKET

#### 11.1 Acquisition Épiciers

**Canaux:**

1. **Sales directe (60%):**
   - Équipe de 10 commerciaux terrain
   - Zone géographique assignée
   - Visite physique épiceries
   - Démo app sur place
   - Incentive: 50 TND/inscription

2. **Digital (25%):**
   - Facebook Ads (ciblage géo + intérêts)
   - Google Ads (mots-clés: "grossiste Tunisie")
   - TikTok (vidéos témoignages)
   - SEO local

3. **Bouche-à-oreille (10%):**
   - Programme parrainage
   - 20 TND pour parrain + filleul
   - Témoignages vidéos

4. **Partenariats (5%):**
   - Chambres de commerce
   - Associations d'épiciers
   - Événements professionnels

**Coût d'acquisition cible:** <100 TND/épicier

#### 11.2 Rétention

**Stratégies:**

1. **Onboarding soigné:**
   - Appel de bienvenue J+1
   - Tutorial app personnalisé
   - Promo -10% première commande
   - Support dédié 30 premiers jours

2. **Engagement régulier:**
   - Push notifications intelligentes
   - Offres personnalisées
   - Programme fidélité (points)
   - Challenges et incentives

3. **Excellence service:**
   - Livraison à l'heure (>95%)
   - Support réactif (<2 min)
   - Qualité produits garantie
   - Gestion proactive problèmes

**Objectif rétention:**
- Mois 1: >80%
- Mois 3: >70%
- Mois 6: >65%

### 12. KPIS ET MESURE DE PERFORMANCE

#### 12.1 KPIs Business

**Acquisition:**
- Nombre d'inscriptions / mois
- Coût d'acquisition (CAC)
- Taux de conversion (visite → inscription)
- Sources d'acquisition

**Activation:**
- % épiciers avec 1ère commande (objectif >70%)
- Délai inscription → 1ère commande (objectif <7j)
- Panier première commande (objectif >200 TND)

**Rétention:**
- Épiciers actifs (commande dans les 30 derniers jours)
- Taux de rétention M1, M3, M6, M12
- Taux de churn mensuel (objectif <5%)
- Fréquence de commande (objectif >3/mois)

**Revenus:**
- GMV (Gross Merchandise Value)
- Panier moyen (objectif 250-300 TND)
- Nombre de commandes / mois
- Revenus / épicier / mois (objectif 250 TND)

**Profitabilité:**
- Marge brute (objectif >15%)
- EBITDA margin (objectif >30% Année 2)
- LTV / CAC ratio (objectif >3)

#### 12.2 KPIs Opérationnels

**Logistique:**
- Taux de livraison à l'heure (objectif >95%)
- Délai moyen de livraison (objectif <22h)
- Coût par livraison (objectif <12 TND)
- Taux de retour / annulation (objectif <2%)

**Qualité:**
- NPS - Net Promoter Score (objectif >60)
- Note moyenne app (objectif >4.5/5)
- Taux de réclamations (objectif <3%)
- Temps de résolution réclamations (objectif <24h)

**Tech:**
- Uptime app/API (objectif >99.5%)
- Temps de chargement pages (objectif <2s)
- Taux d'erreurs (objectif <0.1%)
- Adoption features (usage >70%)

---

## ANNEXES

### ANNEXE A: GLOSSAIRE

**FMCG:** Fast-Moving Consumer Goods (produits de grande consommation)  
**GMV:** Gross Merchandise Value (valeur marchande brute)  
**SKU:** Stock Keeping Unit (référence produit)  
**NPS:** Net Promoter Score (indicateur de recommandation)  
**CAC:** Customer Acquisition Cost (coût d'acquisition client)  
**LTV:** Lifetime Value (valeur vie client)  
**KYC:** Know Your Customer (vérification identité)  
**BCT:** Banque Centrale de Tunisie

### ANNEXE B: CONTACTS ET RESSOURCES

**Équipe Projet:**
- Chef de Projet: [Nom]
- CTO: [Nom]
- Responsable Produit: [Nom]
- Responsable Commercial: [Nom]

**Partenaires Clés:**
- Développement: [Agence]
- Hébergement: [Provider]
- Paiement: Cartunisie, PayMe
- Logistique: [Partenaires transport]

**Liens Utiles:**
- Documentation API: api.ichri.tn/docs
- Dashboard Admin: admin.ichri.tn
- Support: support@ichri.tn
- Site web: www.ichri.tn

---

**FIN DU DOCUMENT**

*Ce document est confidentiel et propriété d'ichri.tn.  
Toute reproduction ou distribution non autorisée est interdite.*

Version 1.0 - Novembre 2024
