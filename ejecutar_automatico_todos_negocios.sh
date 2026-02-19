#!/bin/bash

# ====================================================================
# Script BASH para ejecutar el INSERT en TODAS las bases de datos automáticamente
# ====================================================================

# Configuración de conexión MySQL
MYSQL_USER="root"
MYSQL_PASSWORD="tu_password"  # CAMBIAR POR TU PASSWORD
MYSQL_HOST="localhost"
MYSQL_PORT="3306"

# Archivo SQL a ejecutar
SQL_FILE="database/add_productos_pasta_all_locals.sql"

# ====================================================================
# Obtener todas las bases de datos que empiezan con 'fagotto_local_'
# ====================================================================

echo "🔍 Buscando bases de datos de negocios..."

# Listar todas las BDs que coinciden con el patrón
DATABASES=$(mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -h"$MYSQL_HOST" -P"$MYSQL_PORT" -e "SHOW DATABASES LIKE 'fagotto_local_%';" | grep -v Database)

if [ -z "$DATABASES" ]; then
    echo "❌ No se encontraron bases de datos con el patrón 'fagotto_local_%'"
    exit 1
fi

echo "✅ Se encontraron las siguientes bases de datos:"
echo "$DATABASES"
echo ""

# Contador de éxitos y fallos
SUCCESS_COUNT=0
FAIL_COUNT=0

# ====================================================================
# Ejecutar el script en cada base de datos
# ====================================================================

for DB in $DATABASES; do
    echo "📊 Procesando: $DB"
    
    # Ejecutar el script SQL en esta base de datos
    mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -h"$MYSQL_HOST" -P"$MYSQL_PORT" "$DB" < "$SQL_FILE"
    
    if [ $? -eq 0 ]; then
        echo "   ✅ Completado en $DB"
        ((SUCCESS_COUNT++))
    else
        echo "   ❌ Error en $DB"
        ((FAIL_COUNT++))
    fi
    echo ""
done

# ====================================================================
# Resumen final
# ====================================================================

echo "======================================================================"
echo "📋 RESUMEN DE EJECUCIÓN"
echo "======================================================================"
echo "✅ Éxitos: $SUCCESS_COUNT bases de datos"
echo "❌ Fallos: $FAIL_COUNT bases de datos"
echo "======================================================================"

# Verificar productos insertados
echo ""
echo "🔍 Verificando productos insertados en cada base de datos..."
echo ""

for DB in $DATABASES; do
    COUNT=$(mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -h"$MYSQL_HOST" -P"$MYSQL_PORT" -N -e "SELECT COUNT(*) FROM $DB.products WHERE name IN ('Pasta Bigoli Boloñesa', 'Pasta Fettucine Champiñon');")
    echo "   📦 $DB: $COUNT productos"
done

echo ""
echo "✅ Proceso completado!"
