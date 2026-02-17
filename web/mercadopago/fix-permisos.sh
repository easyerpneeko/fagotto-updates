#!/bin/bash
# Script para arreglar permisos de MercadoPago
# Ejecutar: chmod +x fix-permisos.sh && ./fix-permisos.sh

echo "🔧 Arreglando permisos de MercadoPago..."

# Ir al directorio de mercadopago
cd /var/www/html/mercadopago || exit 1

# Crear directorio logs si no existe
mkdir -p storage/logs

# Establecer permisos correctos
chmod 755 storage
chmod 755 storage/logs

# Dar permisos de escritura al usuario de Apache
chown -R www-data:www-data storage/logs

# Verificar permisos
echo ""
echo "✅ Permisos establecidos:"
ls -la storage/
ls -la storage/logs/

echo ""
echo "✅ Listo. Ahora api-enviar-pago.php puede escribir logs."
