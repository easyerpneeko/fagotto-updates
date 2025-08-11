@echo off
echo ============================================
echo       FAGOTTO AUTO-UPDATE DEPLOY v1.11.12
echo ============================================
echo.
echo 1. Creando release en GitHub...
echo.

REM Verificar que gh CLI esté instalado
where gh >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: GitHub CLI no está instalado.
    echo Instalar desde: https://cli.github.com/
    pause
    exit /b 1
)

REM Crear release
gh release create v1.11.12 --repo easyerpneeko/fagotto-updates --title "Version 1.11.12 - Precios Diferenciados y UX Mejorado" --notes "🎉 Nueva versión con mejoras importantes:

✨ CARACTERÍSTICAS NUEVAS:
• Precios diferenciados para salsas:
  - ALFREDO y BOLOÑESA: $1,508 por vaso
  - Otras salsas: $1,875 por vaso
• Mensaje informativo con precios en catálogo

🔧 MEJORAS DE UX:
• Unidades de medida consistentes
• Botellas de Huevos: X botellas (no unidades)
• Aceite Vegetal: X unidades (no kg)
• Pliego stickers: X Pliego
• Productos individuales: unidades vs kg

🎨 INTERFAZ WEB MODERNIZADA:
• Diseño tipo dashboard con efectos glassmorphism
• Animaciones suaves y efectos hover
• Protección de modales mantiene funcionalidad

✅ Esta actualización permite vender ALFREDO y BOLOÑESA a mejor precio para los clientes!"

echo.
echo 2. Subiendo archivos...
echo.

REM Subir archivos
gh release upload v1.11.12 "build\fagotto-erd-aplication Setup 1.11.12.exe" --repo easyerpneeko/fagotto-updates
gh release upload v1.11.12 "build\fagotto-erd-aplication Setup 1.11.12.exe.blockmap" --repo easyerpneeko/fagotto-updates  
gh release upload v1.11.12 "build\latest.yml" --repo easyerpneeko/fagotto-updates

echo.
echo ============================================
echo       DEPLOY COMPLETADO EXITOSAMENTE
echo ============================================
echo.
echo El auto-update debería funcionar ahora.
echo Las aplicaciones anteriores detectarán la v1.11.12 automáticamente.
echo.
pause
