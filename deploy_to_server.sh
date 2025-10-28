#!/bin/bash

echo "========================================"
echo "DEPLOY FAGOTTO 10% - COMPILACION COMPLETA"
echo "========================================"
echo ""

# Directorio del servidor remoto
SERVER_USER="jimmy"
SERVER_HOST="posfagotto.cl"  # O la IP del servidor
SERVER_PATH="/var/www/html/server"

echo "[1/5] Copiando archivos PHP modificados..."
scp "c:/xampp/htdocs/app/Http/Controllers/Controllers_local/SellsController.php" \
    "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/app/Http/Controllers/Controllers_local/"
scp "c:/xampp/htdocs/app/Http/Controllers/Controllers_local/ReportsController.php" \
    "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/app/Http/Controllers/Controllers_local/"
scp "c:/xampp/htdocs/database/seeds/SettingsSubModuleTableSeeder.php" \
    "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/database/seeds/"
echo "✓ Archivos PHP copiados"
echo ""

echo "[2/5] Copiando archivos compilados (CSS/JS)..."
scp -r "c:/xampp/htdocs/Server app/public/js/" \
    "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/public/"
scp -r "c:/xampp/htdocs/Server app/public/css/" \
    "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/public/"
scp "c:/xampp/htdocs/Server app/public/mix-manifest.json" \
    "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/public/"
echo "✓ Assets compilados copiados"
echo ""

echo "[3/5] Ajustando permisos en el servidor..."
ssh "$SERVER_USER@$SERVER_HOST" << 'ENDSSH'
cd /var/www/html/server
sudo chown -R www-data:www-data storage/ bootstrap/cache/
sudo chmod -R 775 storage/ bootstrap/cache/
echo "✓ Permisos ajustados"
ENDSSH
echo ""

echo "[4/5] Limpiando cachés..."
ssh "$SERVER_USER@$SERVER_HOST" << 'ENDSSH'
cd /var/www/html/server
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo "✓ Cachés limpiados"
ENDSSH
echo ""

echo "[5/5] Ejecutando seeder..."
ssh "$SERVER_USER@$SERVER_HOST" << 'ENDSSH'
cd /var/www/html/server
php artisan db:seed --class=SettingsSubModuleTableSeeder
echo "✓ Seeder ejecutado"
ENDSSH
echo ""

echo "========================================"
echo "✓ DEPLOY COMPLETADO"
echo "========================================"
echo ""
echo "RECUERDA:"
echo "1. Ejecutar SQL: ALTER TABLE sells ADD COLUMN special_payment_info TEXT NULL;"
echo "2. Habilitar el ajuste desde el admin"
echo ""
