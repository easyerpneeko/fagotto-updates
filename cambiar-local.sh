#!/bin/bash
# Script para cambiar la configuración del local
# Uso: ./cambiar-local.sh MAN001

LOCAL_CODE=$1
CONFIG_DIR="src/renderer/config"
SOURCE_FILE="$CONFIG_DIR/app-config-$LOCAL_CODE.json"
TARGET_FILE="$CONFIG_DIR/app-config.json"

# Verificar que se proporcionó un código de local
if [ -z "$LOCAL_CODE" ]; then
    echo "❌ Error: Debes proporcionar un código de local"
    echo ""
    echo "Uso: ./cambiar-local.sh [CODIGO_LOCAL]"
    echo ""
    echo "Ejemplo: ./cambiar-local.sh MAN001"
    exit 1
fi

# Verificar que el archivo de configuración existe
if [ ! -f "$SOURCE_FILE" ]; then
    echo "❌ Error: No existe configuración para el local $LOCAL_CODE"
    echo ""
    echo "Locales disponibles:"
    for file in $CONFIG_DIR/app-config-*.json; do
        name=$(basename "$file" | sed 's/app-config-//' | sed 's/.json//')
        echo "  - $name"
    done
    exit 1
fi

# Leer información del archivo
LOCAL_NAME=$(grep -o '"localNombre": "[^"]*"' "$SOURCE_FILE" | cut -d'"' -f4)
APP_ID=$(grep -o '"appId": "[^"]*"' "$SOURCE_FILE" | cut -d'"' -f4)

echo "📍 Cambiando configuración a:"
echo "   Local: $LOCAL_NAME"
echo "   APPID: $APP_ID"
echo ""

# Copiar el archivo
cp "$SOURCE_FILE" "$TARGET_FILE"

echo "✅ Configuración actualizada correctamente"
echo ""
echo "⚠️  Recuerda reiniciar la aplicación para aplicar los cambios"
echo ""
echo "Comandos:"
echo "  npm run dev    # Modo desarrollo"
echo "  npm run build  # Compilar para producción"
