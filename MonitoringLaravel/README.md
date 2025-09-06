# Laravel Server Monitor

Application Laravel complète pour la gestion automatique des redémarrages de serveurs DigitalOcean lors de pannes détectées par Uptime Kuma.

## 🚀 Fonctionnalités

### Monitoring Automatique
- **Réception de webhooks** : Intégration directe avec Uptime Kuma
- **Double vérification** : Validation des pannes avec 3 tentatives espacées
- **Gestion des faux positifs** : Évite les redémarrages inutiles

### Notifications SMS
- **Liens signés temporaires** : Sécurisation des actions de redémarrage (1h de validité)
- **Integration Twilio** : Envoi automatique de SMS
- **Templates personnalisables** : Messages d'alerte configurables

### Interface de Redémarrage
- **Pages web sécurisées** : Accessible uniquement via liens signés
- **Confirmation utilisateur** : Bouton de validation avant redémarrage
- **Suivi en temps réel** : Statut des opérations avec auto-refresh

### Gestion DigitalOcean
- **API v2** : Appels directs pour reboot des droplets
- **Vérification post-redémarrage** : Contrôle automatique du retour en ligne
- **Gestion d'erreurs** : Retry automatique et logging détaillé

### Dashboard Administrateur
- **Vue d'ensemble** : Statistiques temps réel des sites et incidents
- **Historique complet** : Logs de tous les redémarrages et pannes
- **Analytics** : Métriques d'uptime et tendances
- **Gestion des sites** : CRUD complet avec validation

## 📋 Prérequis

- PHP 8.1+
- Composer
- MySQL/PostgreSQL
- Node.js & NPM (pour la compilation des assets)
- Compte Twilio (pour SMS)
- Token DigitalOcean API
- Instance Uptime Kuma

## 🛠 Installation

### 1. Cloner le projet
```bash
git clone https://github.com/votre-repo/laravel-server-monitor.git
cd laravel-server-monitor
```

### 2. Installer les dépendances
```bash
composer install
npm install
npm run build
```

### 3. Configuration de base
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configuration de la base de données
Éditer `.env` avec vos paramètres DB :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_monitor
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Migration et seeding
```bash
php artisan migrate
```

### 6. Configuration des services externes

#### Twilio SMS
```env
TWILIO_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM=+33123456789
```

#### DigitalOcean
```env
DIGITALOCEAN_TOKEN=your_do_token
```

#### Queue Configuration
```env
QUEUE_CONNECTION=database
```

### 7. Démarrer les services
```bash
# Serveur web
php artisan serve

# Worker pour les jobs
php artisan queue:work
```

## 🔧 Configuration Uptime Kuma

### Webhook URL
Configurez dans Uptime Kuma :
```
URL: https://your-app.com/webhook/uptime-kuma
Method: POST
```

### Format des données attendues
```json
{
  "heartbeat": {
    "status": 0,
    "msg": "Connection timeout",
    "time": "2025-01-01T12:00:00Z"
  },
  "monitor": {
    "name": "Mon Site Web",
    "url": "https://example.com"
  }
}
```

## 📱 Utilisation

### 1. Ajouter un site à monitorer
- Aller dans Dashboard → Sites → Ajouter un site
- Renseigner : nom, URL, droplet ID, téléphone de notification
- Configurer timeout et intervalle de vérification

### 2. Workflow automatique
1. **Uptime Kuma détecte une panne** → Webhook vers l'application
2. **Double vérification** → 3 tentatives de ping avec délai
3. **Envoi SMS** → Lien sécurisé vers interface de redémarrage
4. **Confirmation utilisateur** → Clic sur le lien et validation
5. **Redémarrage DigitalOcean** → Appel API et suivi du statut
6. **Vérification finale** → Contrôle du retour en ligne

## 🧪 Tests

```bash
# Tests complets
php artisan test

# Tests spécifiques
php artisan test --filter=WebhookTest
```

## 📊 API Endpoints

### Webhook (Public)
- `POST /webhook/uptime-kuma` - Réception des alertes Uptime Kuma

### Redémarrage (Liens signés)
- `GET /reboot/site/{site}/incident/{incident}` - Interface de confirmation
- `POST /reboot/site/{site}/incident/{incident}` - Exécution du redémarrage

### Dashboard (Authentifié)
- `GET /dashboard` - Vue d'ensemble
- `GET /sites` - Gestion des sites

## 🔒 Sécurité

- Laravel Breeze pour l'authentification admin
- URLs temporaires avec signature cryptographique
- Validation stricte des webhooks
- Protection CSRF et rate limiting

## 📄 Licence

Ce projet est sous licence MIT.

---

**Développé avec ❤️ par Claude Code**
