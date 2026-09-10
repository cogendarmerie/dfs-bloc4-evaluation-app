```bash
#!/bin/bash

set -e

BRANCH="${1:-main}"

APP_DIR="/var/www/html"

echo "======================================"
echo " Déploiement OpsTrack Field Service"
echo "======================================"

cd "$APP_DIR"

echo ""
echo "==> Branche cible : $BRANCH"

echo ""
echo "==> Vérification du dépôt"
git status --short

echo ""
echo "==> Commit actuellement installé"
git rev-parse HEAD

echo ""
echo "==> Récupération du dépôt distant"
git fetch origin "$BRANCH"

echo ""
echo "==> Mise à jour du code"
git reset --hard "origin/$BRANCH"

echo ""
echo "==> Nouveau commit"
git rev-parse HEAD

echo ""
echo "==> Installation des dépendances PHP"
composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

echo ""
echo "==> Migration de la base de données"
php artisan migrate --force

echo ""
echo "==> Nettoyage du cache"
php artisan optimize:clear

echo ""
echo "==> Reconstruction du cache Laravel"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "==> Installation des dépendances Node.js"
npm ci

echo ""
echo "==> Compilation du frontend"
npm run build

echo ""
echo "==> Rechargement du serveur web"

if systemctl is-active --quiet apache2; then
    sudo systemctl reload apache2
else
    echo "Apache n'est pas actif ou n'est pas utilisé."
fi

echo ""
echo "======================================"
echo " Déploiement terminé avec succès"
echo "======================================"

echo ""
echo "Commit déployé :"
git rev-parse HEAD
```
