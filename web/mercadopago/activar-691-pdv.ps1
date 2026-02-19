# ACTIVAR TERMINAL 691 (AHUMADA) A PDV - CUENTA 3
# Serial: N950NCC804178691

$accessToken = "APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034"
$deviceId = "NEWLAND_N950__N950NCC804178691"

Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "  ACTIVAR TERMINAL AHUMADA 691 A MODO PDV" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Terminal: $deviceId" -ForegroundColor Yellow
Write-Host ""

$body = @{
    terminals = @(
        @{
            id = $deviceId
            operating_mode = "PDV"
        }
    )
} | ConvertTo-Json -Depth 10

$headers = @{
    "Authorization" = "Bearer $accessToken"
    "Content-Type" = "application/json"
}

Write-Host "Enviando request a MercadoPago..." -ForegroundColor White
Write-Host ""

try {
    $response = Invoke-RestMethod -Uri "https://api.mercadopago.com/terminals/v1/setup" -Method Patch -Headers $headers -Body $body -ErrorAction Stop
    
    Write-Host "✅ ¡ÉXITO! Terminal activado en modo PDV" -ForegroundColor Green
    Write-Host ""
    
    if ($response.data.terminals) {
        $terminal = $response.data.terminals[0]
        Write-Host "🖥️  Terminal ID: $($terminal.id)" -ForegroundColor White
        Write-Host "🔧 Modo: $($terminal.operating_mode)" -ForegroundColor Green
        Write-Host "🏪 Store ID: $($terminal.store_id)" -ForegroundColor White
        Write-Host "📍 POS ID: $($terminal.pos_id)" -ForegroundColor White
    } else {
        Write-Host "Respuesta completa:" -ForegroundColor DarkGray
        $response | ConvertTo-Json -Depth 10
    }
    
    Write-Host ""
    Write-Host "💡 El terminal debe sincronizar en 2-3 minutos" -ForegroundColor Cyan
    Write-Host ""
    
} catch {
    $statusCode = $_.Exception.Response.StatusCode.value__
    Write-Host "❌ ERROR $statusCode" -ForegroundColor Red
    Write-Host ""
    Write-Host "Mensaje: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    
    if ($_.ErrorDetails.Message) {
        Write-Host "Detalles del error:" -ForegroundColor Yellow
        $errorJson = $_.ErrorDetails.Message | ConvertFrom-Json
        $errorJson | ConvertTo-Json -Depth 10
    }
}

Write-Host ""
Write-Host "===============================================" -ForegroundColor Cyan
