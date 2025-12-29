<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Validar GPS API</title>
    <style>
        body { font-family: system-ui; max-width: 600px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .card { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h1 { color: #667eea; margin: 0 0 20px 0; }
        button { width: 100%; padding: 15px; background: #667eea; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; margin-bottom: 20px; }
        button:hover { background: #5568d3; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; }
        .result { background: #000; color: #0f0; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 13px; white-space: pre-wrap; word-break: break-all; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🧪 Test API validar-gps.php</h1>
        
        <label>Session ID:</label>
        <input type="text" id="sessionId" value="SES1766976232198" placeholder="SES1735433700" />
        
        <label>Latitud:</label>
        <input type="text" id="lat" value="-33.43769560" />
        
        <label>Longitud:</label>
        <input type="text" id="lng" value="-70.64392252" />
        
        <button onclick="testAPI()">🚀 Probar API</button>
        
        <div id="resultado" class="result" style="display:none;"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        async function testAPI() {
            const resultado = document.getElementById('resultado');
            resultado.style.display = 'block';
            resultado.textContent = '⏳ Enviando petición...\n';
            
            const sessionId = document.getElementById('sessionId').value;
            const lat = parseFloat(document.getElementById('lat').value);
            const lng = parseFloat(document.getElementById('lng').value);
            
            resultado.textContent += `\nDatos:\n`;
            resultado.textContent += `  SessionId: ${sessionId}\n`;
            resultado.textContent += `  Lat: ${lat}\n`;
            resultado.textContent += `  Lng: ${lng}\n\n`;
            
            try {
                const response = await axios.post('https://asistencia.fagottoerp.cl/api/validar-gps.php', {
                    sessionId: sessionId,
                    lat: lat,
                    lng: lng
                }, {
                    timeout: 15000,
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                resultado.textContent += '✅ RESPUESTA EXITOSA:\n\n';
                resultado.textContent += `Status: ${response.status}\n`;
                resultado.textContent += `\nDatos:\n`;
                resultado.textContent += JSON.stringify(response.data, null, 2);
                
            } catch (error) {
                resultado.textContent += '❌ ERROR:\n\n';
                
                if (error.response) {
                    resultado.textContent += `Status: ${error.response.status}\n`;
                    resultado.textContent += `\nRespuesta del servidor:\n`;
                    resultado.textContent += JSON.stringify(error.response.data, null, 2);
                } else if (error.request) {
                    resultado.textContent += 'Sin respuesta del servidor (timeout o conexión)\n';
                    resultado.textContent += `\nError: ${error.message}`;
                } else {
                    resultado.textContent += `Error: ${error.message}`;
                }
                
                console.error('Error completo:', error);
            }
        }
    </script>
</body>
</html>
