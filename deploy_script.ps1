# Script de Deployment - Funcionalidad Facturas por Sucursal
# Para usar con FTP o copiar manualmente

Write-Host "=== DEPLOYMENT SCRIPT - FACTURAS SUCURSAL ===" -ForegroundColor Green
Write-Host ""

# Definir rutas
$localPath = Get-Location
$webPath = Join-Path $localPath "web"
$serverPath = Join-Path $localPath "Server app"

Write-Host "Ruta actual: $localPath" -ForegroundColor Yellow
Write-Host ""

# Verificar archivos locales
Write-Host "🔍 VERIFICANDO ARCHIVOS LOCALES..." -ForegroundColor Cyan
Write-Host ""

$archivosRequeridos = @(
    @{
        Local = "web\pages\facturas_sucursal.html"
        Servidor = "/public_html/pages/facturas_sucursal.html"
        Tipo = "NUEVO"
    },
    @{
        Local = "web\assets\js\facturas_sucursal.js"
        Servidor = "/public_html/assets/js/facturas_sucursal.js"
        Tipo = "NUEVO"
    },
    @{
        Local = "Server app\app\Http\Controllers\Controllers_local\RequestsController.php"
        Servidor = "/public_html/app/Http/Controllers/Controllers_local/RequestsController.php"
        Tipo = "MODIFICADO"
    },
    @{
        Local = "Server app\routes\api.php"
        Servidor = "/public_html/routes/api.php"
        Tipo = "MODIFICADO"
    },
    @{
        Local = "web\pages\dashboard.html"
        Servidor = "/public_html/pages/dashboard.html"
        Tipo = "MODIFICADO"
    }
)

foreach ($archivo in $archivosRequeridos) {
    $rutaLocal = Join-Path $localPath $archivo.Local
    if (Test-Path $rutaLocal) {
        $tamaño = (Get-Item $rutaLocal).Length
        Write-Host "✅ $($archivo.Tipo): $($archivo.Local) ($tamaño bytes)" -ForegroundColor Green
        Write-Host "   Destino: $($archivo.Servidor)" -ForegroundColor Gray
    } else {
        Write-Host "❌ FALTA: $($archivo.Local)" -ForegroundColor Red
    }
    Write-Host ""
}

Write-Host ""
Write-Host "📋 INSTRUCCIONES DE DEPLOYMENT:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. ARCHIVOS NUEVOS (subir directamente):" -ForegroundColor Cyan
Write-Host "   • web\pages\facturas_sucursal.html → /public_html/pages/"
Write-Host "   • web\assets\js\facturas_sucursal.js → /public_html/assets/js/"
Write-Host ""

Write-Host "2. ARCHIVOS MODIFICADOS (hacer backup antes):" -ForegroundColor Orange
Write-Host "   • Server app\app\Http\Controllers\Controllers_local\RequestsController.php → /public_html/app/Http/Controllers/Controllers_local/"
Write-Host "   • Server app\routes\api.php → /public_html/routes/"
Write-Host "   • web\pages\dashboard.html → /public_html/pages/"
Write-Host ""

Write-Host "3. VERIFICAR DESPUÉS DEL DEPLOYMENT:" -ForegroundColor Magenta
Write-Host "   • https://fagottoerp.cl/pages/facturas_sucursal.html"
Write-Host "   • Dashboard con nuevo botón 'Ver Facturas'"
Write-Host "   • APIs funcionando en /api/web/sucursal/"
Write-Host ""

Write-Host "4. CREAR CARPETA DE BACKUP (recomendado):" -ForegroundColor Yellow
$backupPath = Join-Path $localPath "backup_$(Get-Date -Format 'yyyyMMdd_HHmm')"
if (!(Test-Path $backupPath)) {
    New-Item -Path $backupPath -ItemType Directory | Out-Null
    Write-Host "   Carpeta de backup creada: $backupPath"
}

Write-Host ""
Write-Host "🚀 OPCIONES DE DEPLOYMENT:" -ForegroundColor Green
Write-Host ""
Write-Host "A) Via cPanel File Manager:"
Write-Host "   1. Acceder a cPanel → File Manager"
Write-Host "   2. Navegar a public_html"
Write-Host "   3. Subir archivos según tabla arriba"
Write-Host ""
Write-Host "B) Via FTP Cliente (FileZilla, WinSCP, etc.):"
Write-Host "   1. Conectar a fagottoerp.cl via FTP"
Write-Host "   2. Navegar a /public_html/"
Write-Host "   3. Subir archivos manteniendo estructura"
Write-Host ""
Write-Host "C) Via SSH (si tienes acceso):"
Write-Host "   scp 'web/pages/facturas_sucursal.html' usuario@fagottoerp.cl:/public_html/pages/"
Write-Host "   scp 'web/assets/js/facturas_sucursal.js' usuario@fagottoerp.cl:/public_html/assets/js/"
Write-Host ""

Write-Host "⚠️  IMPORTANTE:" -ForegroundColor Red
Write-Host "   • Hacer backup de archivos modificados antes de reemplazar"
Write-Host "   • Verificar permisos de archivos (644 para archivos, 755 para carpetas)"
Write-Host "   • Probar funcionalidad después del deployment"
Write-Host ""

# Preguntar si quiere abrir las carpetas
$respuesta = Read-Host "¿Abrir carpetas de archivos? (s/n)"
if ($respuesta -eq 's' -or $respuesta -eq 'S') {
    if (Test-Path (Join-Path $localPath "web\pages")) {
        Invoke-Item (Join-Path $localPath "web\pages")
    }
    if (Test-Path (Join-Path $localPath "web\assets\js")) {
        Invoke-Item (Join-Path $localPath "web\assets\js")
    }
    if (Test-Path (Join-Path $localPath "Server app\app\Http\Controllers\Controllers_local")) {
        Invoke-Item (Join-Path $localPath "Server app\app\Http\Controllers\Controllers_local")
    }
}

Write-Host ""
Write-Host "✨ Script completado. ¡Buen deployment!" -ForegroundColor Green
