@echo off
echo ============================================
echo       FAGOTTO AUTO-UPDATE DEPLOY v1.0.3
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
gh release create v1.0.3 --repo easyerpneeko/fagotto-updates --title "Version 1.0.3" --notes "Actualización automática - Version 1.0.3 - Prueba de auto-update"

echo.
echo 2. Subiendo archivos...
echo.

REM Subir archivos
gh release upload v1.0.3 "build\fagotto-erd-aplication Setup 1.0.3.exe" --repo easyerpneeko/fagotto-updates
gh release upload v1.0.3 "build\fagotto-erd-aplication Setup 1.0.3.exe.blockmap" --repo easyerpneeko/fagotto-updates  
gh release upload v1.0.3 "build\latest.yml" --repo easyerpneeko/fagotto-updates

echo.
echo ============================================
echo       DEPLOY COMPLETADO EXITOSAMENTE
echo ============================================
echo.
echo El auto-update debería funcionar ahora.
echo La aplicación v1.0.2 debería detectar la v1.0.3 automáticamente.
echo.
pause
