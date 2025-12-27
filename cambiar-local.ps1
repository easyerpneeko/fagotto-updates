# Script para cambiar la configuración del local
# Uso: .\cambiar-local.ps1 MAN001

param(
    [Parameter(Mandatory=$true)]
    [string]$LocalCode
)

$ConfigDir = "src\renderer\config"
$SourceFile = "$ConfigDir\app-config-$LocalCode.json"
$TargetFile = "$ConfigDir\app-config.json"

# Verificar que el archivo de configuración existe
if (-Not (Test-Path $SourceFile)) {
    Write-Host "Error: No existe configuración para el local $LocalCode" -ForegroundColor Red
    Write-Host ""
    Write-Host "Locales disponibles:" -ForegroundColor Yellow
    Get-ChildItem "$ConfigDir\app-config-*.json" | ForEach-Object {
        $name = $_.Name -replace 'app-config-', '' -replace '.json', ''
        Write-Host "  - $name" -ForegroundColor Cyan
    }
    exit 1
}

# Leer el contenido del archivo para mostrar información
$ConfigContent = Get-Content $SourceFile | ConvertFrom-Json

Write-Host ""
Write-Host "Cambiando configuracion a:" -ForegroundColor Green
Write-Host "   Local: $($ConfigContent.localNombre)" -ForegroundColor White
Write-Host "   APPID: $($ConfigContent.appId)" -ForegroundColor White
Write-Host "   GPS: $($ConfigContent.gps.latitud), $($ConfigContent.gps.longitud)" -ForegroundColor White
Write-Host ""

# Copiar el archivo
Copy-Item -Path $SourceFile -Destination $TargetFile -Force

Write-Host "Configuracion actualizada correctamente!" -ForegroundColor Green
Write-Host ""
Write-Host "Recuerda reiniciar la aplicacion para aplicar los cambios" -ForegroundColor Yellow
Write-Host ""
Write-Host "Comandos:" -ForegroundColor Cyan
Write-Host "  npm run dev    # Modo desarrollo" -ForegroundColor Gray
Write-Host "  npm run build  # Compilar para produccion" -ForegroundColor Gray
Write-Host ""
