@echo off
echo ========================================
echo   TOTEM Fagotto - Build Script
echo ========================================
echo.

REM Verificar Node.js
echo [1/5] Verificando Node.js...
node --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Node.js no esta instalado
    echo Por favor instala Node.js 18 LTS desde: https://nodejs.org/
    pause
    exit /b 1
)

for /f "tokens=1,2,3 delims=v." %%a in ('node --version') do set NODE_MAJOR=%%a
if %NODE_MAJOR% LSS 14 (
    echo [ADVERTENCIA] Node.js version muy antigua
    echo Se requiere Node.js 14+, se recomienda 18 LTS
    echo Descarga desde: https://nodejs.org/
    pause
)

echo [OK] Node.js instalado
echo.

REM Verificar PHP
echo [2/5] Verificando PHP...
php --version >nul 2>&1
if errorlevel 1 (
    echo [ADVERTENCIA] PHP no esta en PATH
    echo La aplicacion necesitara PHP portable incluido
    echo O el usuario debe tener PHP instalado
) else (
    echo [OK] PHP instalado
)
echo.

REM Instalar dependencias
echo [3/5] Instalando dependencias...
if not exist node_modules (
    echo Ejecutando npm install...
    call npm install
    if errorlevel 1 (
        echo [ERROR] Fallo la instalacion de dependencias
        pause
        exit /b 1
    )
) else (
    echo [OK] Dependencias ya instaladas
)
echo.

REM Verificar icono
echo [4/5] Verificando icono...
if not exist electron\assets\icon.png (
    echo [ADVERTENCIA] Icono PNG no encontrado
    echo Por favor genera electron\assets\icon.png desde el SVG
    echo Opcion 1: https://svgtopng.com/
    echo Opcion 2: npm install -g electron-icon-builder
    echo           electron-icon-builder --input=electron/assets/icon.svg --output=electron/assets
    pause
) else (
    echo [OK] Icono encontrado
)
echo.

REM Build
echo [5/5] Compilando instalador para Windows...
echo Esto puede tardar varios minutos...
echo.
call npm run build:win

if errorlevel 1 (
    echo.
    echo [ERROR] El build fallo
    echo Revisa los errores arriba
    pause
    exit /b 1
)

echo.
echo ========================================
echo   BUILD COMPLETADO EXITOSAMENTE
echo ========================================
echo.
echo El instalador esta en: dist\TOTEM Fagotto Setup 1.0.0.exe
echo.
echo Pasos siguientes:
echo 1. Prueba el instalador en una maquina limpia
echo 2. Configura el archivo .env despues de instalar
echo 3. Verifica que PHP este instalado o incluido
echo.
pause
