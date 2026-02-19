# ====================================================================
# Script PowerShell para ejecutar INSERT en TODAS las bases de datos automáticamente
# ====================================================================

# Configuración de conexión MySQL
$MYSQL_USER = "root"
$MYSQL_PASSWORD = "tu_password"  # CAMBIAR POR TU PASSWORD
$MYSQL_HOST = "localhost"
$MYSQL_PORT = "3306"

# Archivo SQL a ejecutar
$SQL_FILE = "database\add_productos_pasta_all_locals.sql"

# Ruta de mysql.exe (ajustar según tu instalación)
$MYSQL_PATH = "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"

# ====================================================================
# Validar que exista mysql.exe
# ====================================================================

if (-not (Test-Path $MYSQL_PATH)) {
    Write-Host "❌ No se encontró mysql.exe en: $MYSQL_PATH" -ForegroundColor Red
    Write-Host "Por favor ajusta la ruta en el script" -ForegroundColor Yellow
    exit 1
}

if (-not (Test-Path $SQL_FILE)) {
    Write-Host "❌ No se encontró el archivo SQL: $SQL_FILE" -ForegroundColor Red
    exit 1
}

Write-Host "🔍 Buscando bases de datos de negocios..." -ForegroundColor Cyan

# ====================================================================
# Obtener todas las bases de datos que empiezan con 'fagotto_local_'
# ====================================================================

$query = "SHOW DATABASES LIKE 'fagotto_local_%';"
$result = & $MYSQL_PATH -u$MYSQL_USER -p$MYSQL_PASSWORD -h$MYSQL_HOST -P$MYSQL_PORT -N -e $query

if (-not $result) {
    Write-Host "❌ No se encontraron bases de datos con el patrón 'fagotto_local_%'" -ForegroundColor Red
    exit 1
}

# Convertir resultado en array de bases de datos
$DATABASES = $result -split "`n" | Where-Object { $_ -ne "" }

Write-Host "✅ Se encontraron $($DATABASES.Count) bases de datos:" -ForegroundColor Green
$DATABASES | ForEach-Object { Write-Host "   - $_" -ForegroundColor White }
Write-Host ""

# Contador de éxitos y fallos
$SUCCESS_COUNT = 0
$FAIL_COUNT = 0
$RESULTS = @()

# ====================================================================
# Ejecutar el script en cada base de datos
# ====================================================================

foreach ($DB in $DATABASES) {
    Write-Host "📊 Procesando: $DB" -ForegroundColor Cyan
    
    # Ejecutar el script SQL en esta base de datos
    $output = & $MYSQL_PATH -u$MYSQL_USER -p$MYSQL_PASSWORD -h$MYSQL_HOST -P$MYSQL_PORT $DB -e "SOURCE $SQL_FILE" 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✅ Completado en $DB" -ForegroundColor Green
        $SUCCESS_COUNT++
        $RESULTS += [PSCustomObject]@{
            BaseDatos = $DB
            Estado = "✅ Éxito"
        }
    } else {
        Write-Host "   ❌ Error en $DB" -ForegroundColor Red
        Write-Host "   Error: $output" -ForegroundColor Yellow
        $FAIL_COUNT++
        $RESULTS += [PSCustomObject]@{
            BaseDatos = $DB
            Estado = "❌ Fallo"
        }
    }
    Write-Host ""
}

# ====================================================================
# Resumen final
# ====================================================================

Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "📋 RESUMEN DE EJECUCIÓN" -ForegroundColor White
Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "✅ Éxitos: $SUCCESS_COUNT bases de datos" -ForegroundColor Green
Write-Host "❌ Fallos: $FAIL_COUNT bases de datos" -ForegroundColor Red
Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host ""

# Mostrar tabla de resultados
$RESULTS | Format-Table -AutoSize

# ====================================================================
# Verificar productos insertados
# ====================================================================

Write-Host "🔍 Verificando productos insertados en cada base de datos..." -ForegroundColor Cyan
Write-Host ""

$VERIFICATION = @()

foreach ($DB in $DATABASES) {
    $countQuery = "SELECT COUNT(*) FROM $DB.products WHERE name IN ('Pasta Bigoli Boloñesa', 'Pasta Fettucine Champiñon');"
    $count = & $MYSQL_PATH -u$MYSQL_USER -p$MYSQL_PASSWORD -h$MYSQL_HOST -P$MYSQL_PORT -N -e $countQuery
    
    $VERIFICATION += [PSCustomObject]@{
        BaseDatos = $DB
        ProductosInsertados = $count
    }
}

$VERIFICATION | Format-Table -AutoSize

Write-Host ""
Write-Host "✅ Proceso completado!" -ForegroundColor Green
Write-Host ""
Write-Host "💡 Tip: Si algún negocio falló, revisa los errores arriba" -ForegroundColor Yellow
