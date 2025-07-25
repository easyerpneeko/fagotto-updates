#!/bin/bash
cd /var/www/html/server

# Verificar las rutas de Uber Eats
echo "=== RUTAS DE UBER EATS ==="
php artisan route:list | grep -i uber || echo "No se encontraron rutas de Uber Eats"

echo ""
echo "=== ÚLTIMAS LÍNEAS DE API.PHP ==="
tail -30 routes/api.php

echo ""
echo "=== CONTENIDO DEL .ENV ==="
grep -E "UBER_|uber" .env || echo "No hay configuración de Uber en .env"
