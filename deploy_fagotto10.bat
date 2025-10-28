@echo off
echo ========================================
echo DEPLOY FAGOTTO 10% - LOCAL a SERVER APP
echo ========================================
echo.

REM Copiar SellsController.php
echo [1/3] Copiando SellsController.php...
copy /Y "c:\xampp\htdocs\app\Http\Controllers\Controllers_local\SellsController.php" "c:\xampp\htdocs\Server app\app\Http\Controllers\Controllers_local\SellsController.php"
if %errorlevel% equ 0 (
    echo      OK - SellsController.php copiado
) else (
    echo      ERROR copiando SellsController.php
)
echo.

REM Copiar ReportsController.php
echo [2/3] Copiando ReportsController.php...
copy /Y "c:\xampp\htdocs\app\Http\Controllers\Controllers_local\ReportsController.php" "c:\xampp\htdocs\Server app\app\Http\Controllers\Controllers_local\ReportsController.php"
if %errorlevel% equ 0 (
    echo      OK - ReportsController.php copiado
) else (
    echo      ERROR copiando ReportsController.php
)
echo.

REM Copiar SettingsSubModuleTableSeeder.php
echo [3/3] Copiando SettingsSubModuleTableSeeder.php...
copy /Y "c:\xampp\htdocs\database\seeds\SettingsSubModuleTableSeeder.php" "c:\xampp\htdocs\Server app\database\seeds\SettingsSubModuleTableSeeder.php"
if %errorlevel% equ 0 (
    echo      OK - SettingsSubModuleTableSeeder.php copiado
) else (
    echo      ERROR copiando SettingsSubModuleTableSeeder.php
)
echo.

REM Copiar archivo SQL
echo [EXTRA] Copiando script SQL...
copy /Y "c:\xampp\htdocs\add_fagotto_columns.sql" "c:\xampp\htdocs\Server app\add_fagotto_columns.sql"
if %errorlevel% equ 0 (
    echo      OK - add_fagotto_columns.sql copiado
) else (
    echo      WARN - No se pudo copiar SQL (quiza no existe)
)
echo.

echo ========================================
echo ARCHIVOS COPIADOS A SERVER APP
echo ========================================
echo.
echo PROXIMOS PASOS:
echo 1. Sube la carpeta "Server app" al servidor de produccion
echo 2. En el servidor ejecuta:
echo    cd /var/www/html/server
echo    sudo chown -R www-data:www-data .
echo    sudo chmod -R 775 storage bootstrap/cache
echo    php artisan cache:clear
echo    php artisan config:clear
echo    php artisan db:seed --class=SettingsSubModuleTableSeeder
echo 3. Ejecuta el SQL: ALTER TABLE sells ADD COLUMN special_payment_info TEXT NULL;
echo 4. Habilita el ajuste desde el admin
echo.
pause
