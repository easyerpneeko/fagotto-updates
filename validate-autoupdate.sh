#!/bin/bash

echo "=== Validando configuración de Auto-Update ==="

# Verificar que el repositorio fagotto-updates existe
echo "1. Verificando repositorio GitHub..."
echo "   - Owner: easyerpneeko"
echo "   - Repo: fagotto-updates"
echo "   - Private: true"

# Verificar archivos necesarios
echo ""
echo "2. Verificando archivos locales..."

if [ -f "gh_token.json" ]; then
    echo "   ✓ gh_token.json existe"
else
    echo "   ✗ gh_token.json no existe"
fi

if [ -f "build/latest.yml" ]; then
    echo "   ✓ build/latest.yml existe"
else
    echo "   ✗ build/latest.yml no existe"
fi

if [ -f "build/fagotto-erd-aplication Setup 1.0.1.exe" ]; then
    echo "   ✓ Archivo ejecutable existe"
else
    echo "   ✗ Archivo ejecutable no existe"
fi

# Verificar configuración en package.json
echo ""
echo "3. Verificando configuración en package.json..."
echo "   - Versión actual: $(grep -o '"version": "[^"]*"' package.json | cut -d'"' -f4)"
echo "   - Configuración publish: $(grep -A5 '"publish"' package.json | head -1)"

echo ""
echo "=== Próximos pasos ==="
echo "1. Crear un release en GitHub con tag v1.0.1"
echo "2. Subir los archivos build/fagotto-erd-aplication Setup 1.0.1.exe y build/latest.yml"
echo "3. Probar el auto-update"

echo ""
echo "=== Comandos útiles ==="
echo "npm run deploy      # Construir y publicar automáticamente"
echo "npm run build       # Solo construir (no publicar)"
echo "npm run test-updater # Probar configuración de auto-update"
