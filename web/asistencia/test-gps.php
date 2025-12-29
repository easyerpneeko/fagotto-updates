<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test GPS</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 { color: #667eea; margin: 0 0 20px 0; }
        button {
            width: 100%;
            padding: 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
        }
        button:hover { background: #5568d3; }
        .result {
            background: #000;
            color: #0f0;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 13px;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 400px;
            overflow-y: auto;
        }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .info { color: #3b82f6; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🧪 Test GPS - Diagnóstico</h1>
        <p>Prueba si tu GPS funciona correctamente</p>
        
        <button onclick="testGPS()">🎯 Obtener mi ubicación GPS</button>
        
        <div id="resultado" class="result" style="display:none;"></div>
    </div>

    <script>
        let logs = [];
        
        function addLog(msg, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            logs.push(`[${timestamp}] ${msg}`);
            updateDisplay();
        }
        
        function updateDisplay() {
            const div = document.getElementById('resultado');
            div.style.display = 'block';
            div.innerHTML = logs.join('\n');
            div.scrollTop = div.scrollHeight;
        }
        
        async function testGPS() {
            logs = [];
            addLog('=== INICIANDO TEST GPS ===', 'info');
            
            // 1. Verificar soporte de Geolocation API
            if (!navigator.geolocation) {
                addLog('❌ ERROR: Tu navegador no soporta Geolocation API', 'error');
                return;
            }
            addLog('✅ Geolocation API disponible', 'success');
            
            // 2. Verificar permisos
            if (navigator.permissions) {
                try {
                    const result = await navigator.permissions.query({ name: 'geolocation' });
                    addLog(`📋 Permiso de ubicación: ${result.state}`, 'info');
                } catch (e) {
                    addLog('⚠️ No se pudo verificar permisos (navegador antiguo)', 'info');
                }
            }
            
            // 3. Obtener ubicación
            addLog('📡 Solicitando ubicación GPS...', 'info');
            addLog('   (Puede tomar hasta 30 segundos)', 'info');
            
            const opciones = {
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            };
            
            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, opciones);
                });
                
                addLog('✅ GPS OBTENIDO EXITOSAMENTE!', 'success');
                addLog('', 'info');
                addLog('📍 COORDENADAS:', 'info');
                addLog(`   Latitud: ${position.coords.latitude}`, 'info');
                addLog(`   Longitud: ${position.coords.longitude}`, 'info');
                addLog('', 'info');
                addLog('📏 PRECISIÓN:', 'info');
                addLog(`   Accuracy: ${position.coords.accuracy.toFixed(0)} metros`, 'info');
                
                if (position.coords.altitude !== null) {
                    addLog(`   Altitud: ${position.coords.altitude.toFixed(0)}m`, 'info');
                }
                
                addLog('', 'info');
                addLog('⏰ TIMESTAMP:', 'info');
                addLog(`   ${new Date(position.timestamp).toLocaleString()}`, 'info');
                
                addLog('', 'info');
                addLog('🎉 TODO FUNCIONA CORRECTAMENTE!', 'success');
                
            } catch (error) {
                addLog('❌ ERROR AL OBTENER GPS', 'error');
                addLog('', 'info');
                
                if (error.code === 1) {
                    addLog('Código 1: PERMISO DENEGADO', 'error');
                    addLog('Solución: Ve a configuración del navegador y permite ubicación', 'info');
                } else if (error.code === 2) {
                    addLog('Código 2: UBICACIÓN NO DISPONIBLE', 'error');
                    addLog('Solución: Verifica que el GPS esté activado en tu celular', 'info');
                } else if (error.code === 3) {
                    addLog('Código 3: TIMEOUT', 'error');
                    addLog('Solución: Sal afuera o busca mejor señal GPS', 'info');
                } else {
                    addLog(`Error desconocido: ${error.message}`, 'error');
                }
                
                addLog('', 'info');
                addLog('📱 INFO DEL DISPOSITIVO:', 'info');
                addLog(`   User Agent: ${navigator.userAgent}`, 'info');
                addLog(`   Platform: ${navigator.platform}`, 'info');
            }
        }
    </script>
</body>
</html>
