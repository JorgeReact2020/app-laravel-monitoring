# Configuration Uptime Kuma

Guide de configuration d'Uptime Kuma pour intégrer avec Laravel Server Monitor.

## Installation Uptime Kuma

### Docker (Recommandé)
```bash
docker run -d --restart=always -p 3001:3001 -v uptime-kuma:/app/data --name uptime-kuma louislam/uptime-kuma:1
```

### NPM
```bash
npm install pm2 -g && pm2 install pm2-logrotate
git clone https://github.com/louislam/uptime-kuma.git
cd uptime-kuma
npm run setup
pm2 start server/server.js --name uptime-kuma
```

## Configuration des Webhooks

### 1. Ajouter un nouveau monitor
- Aller dans Uptime Kuma → Add New Monitor
- Type: HTTP(s)
- Friendly Name: Nom de votre site
- URL: URL à surveiller (doit correspondre exactement à l'URL dans Laravel Monitor)

### 2. Configurer les notifications
- Aller dans Settings → Notifications
- Add New Notification
- Notification Type: **Webhook**
- Friendly Name: Laravel Monitor
- Post URL: `https://your-laravel-app.com/webhook/uptime-kuma`
- Content Type: `application/json`
- HTTP Method: POST

### 3. Assigner la notification au monitor
- Éditer votre monitor
- Section Notifications → Cocher "Laravel Monitor"
- Sauvegarder

## Format des données envoyées

Uptime Kuma envoie les données dans ce format :

### Site DOWN
```json
{
  "heartbeat": {
    "status": 0,
    "msg": "connect ECONNREFUSED 1.2.3.4:443",
    "ping": null,
    "important": true,
    "time": "2025-01-15 10:30:45.123 +00:00"
  },
  "monitor": {
    "id": 1,
    "name": "Mon Site Web",
    "url": "https://example.com",
    "hostname": null,
    "port": null,
    "maxretries": 0,
    "weight": 2000,
    "active": true,
    "type": "http",
    "interval": 60
  },
  "msg": "Down"
}
```

### Site UP
```json
{
  "heartbeat": {
    "status": 1,
    "msg": "200 - OK",
    "ping": 142.23,
    "important": false,
    "time": "2025-01-15 10:35:12.456 +00:00"
  },
  "monitor": {
    "id": 1,
    "name": "Mon Site Web",
    "url": "https://example.com",
    "hostname": null,
    "port": null,
    "maxretries": 0,
    "weight": 2000,
    "active": true,
    "type": "http",
    "interval": 60
  },
  "msg": "Up"
}
```

## Paramètres recommandés

### Monitor Settings
- **Heartbeat Interval**: 60 secondes (pour équilibrer réactivité et charge)
- **Retries**: 1 (Laravel Monitor fait sa propre double vérification)
- **Request Timeout**: 48 secondes
- **Follow Redirect**: Activé
- **Ignore TLS Error**: Selon votre cas

### Advanced Settings
- **Upside Down Mode**: Désactivé
- **Tags**: Utilisez des tags pour organiser vos monitors
- **Expected Status Codes**: 200-299

## Sécurisation des Webhooks

### 1. URL Secret (Optionnel)
Vous pouvez utiliser une URL secrète :
```
https://your-app.com/webhook/uptime-kuma?secret=your-secret-key
```

Puis valider dans Laravel :
```php
// Dans WebhookController
if ($request->get('secret') !== config('app.webhook_secret')) {
    abort(403);
}
```

### 2. IP Whitelist
Configurez votre firewall pour n'accepter que les requêtes depuis l'IP d'Uptime Kuma.

### 3. HTTPS Obligatoire
Utilisez toujours HTTPS pour les webhooks en production.

## Troubleshooting

### Webhook non reçu
1. Vérifier que l'URL est accessible publiquement
2. Contrôler les logs Uptime Kuma et Laravel
3. Tester l'URL webhook manuellement avec curl :

```bash
curl -X POST https://your-app.com/webhook/uptime-kuma \
  -H "Content-Type: application/json" \
  -d '{
    "heartbeat": {"status": 0, "msg": "test", "time": "2025-01-01T12:00:00Z"},
    "monitor": {"name": "Test", "url": "https://example.com"}
  }'
```

### Monitor ne détecte pas les pannes
1. Réduire l'intervalle de vérification
2. Augmenter le timeout
3. Vérifier la connectivité réseau d'Uptime Kuma

### Faux positifs
1. Augmenter le nombre de retries dans Uptime Kuma
2. Laravel Monitor fait déjà 3 vérifications supplémentaires
3. Vérifier la stabilité de votre connexion réseau

## Exemple de configuration complète

### Monitor HTTP
```
Type: HTTP(s)
Friendly Name: Site Production
URL: https://monsite.com
Method: GET
Request Timeout: 48
Heartbeat Interval: 60
Retries: 1
```

### Notification Webhook
```
Notification Type: Webhook
Friendly Name: Laravel Auto Reboot
Post URL: https://monitor.monsite.com/webhook/uptime-kuma
Content Type: application/json
HTTP Method: POST
```

Cette configuration permet une surveillance efficace avec redémarrage automatique en cas de panne confirmée.