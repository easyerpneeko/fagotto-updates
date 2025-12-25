<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Pago - Mercado Pago Point</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
        }
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .order-id-input {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        input[type="text"] {
            flex: 1;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
        }
        .btn {
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6c757d;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        .status-created { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-cancelled { background: #e2e3e5; color: #383d41; }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }
        .info-item strong {
            display: block;
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .info-item span {
            font-size: 18px;
            color: #333;
            font-weight: bold;
        }
        .refresh-btn {
            text-align: center;
            margin: 20px 0;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            overflow-x: auto;
            font-size: 12px;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
    <script>
        let autoRefresh = false;
        let refreshInterval;
        
        function toggleAutoRefresh() {
            autoRefresh = !autoRefresh;
            const btn = document.getElementById('autoRefreshBtn');
            
            if (autoRefresh) {
                btn.textContent = '⏸️ Detener Auto-Refresh (5s)';
                btn.classList.add('btn-secondary');
                refreshInterval = setInterval(() => {
                    document.getElementById('checkForm').submit();
                }, 5000);
            } else {
                btn.textContent = '▶️ Activar Auto-Refresh (5s)';
                btn.classList.remove('btn-secondary');
                clearInterval(refreshInterval);
            }
        }
        
        // Al cargar, activar auto-refresh si hay un order_id
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('order_id')) {
                toggleAutoRefresh();
            }
        };
    </script>
</head>
<body>
    <div class="container">
        <h1>🔍 Validar Estado de Pago</h1>
        
        <div class="card">
            <form method="GET" id="checkForm">
                <div class="order-id-input">
                    <input 
                        type="text" 
                        name="order_id" 
                        placeholder="Order ID (ORD01...)" 
                        value="<?php echo htmlspecialchars($_GET['order_id'] ?? ''); ?>"
                        required
                    >
                    <button type="submit" class="btn">🔍 Consultar</button>
                </div>
            </form>
            
            <?php if (isset($_GET['order_id'])): ?>
                <div class="refresh-btn">
                    <button type="button" class="btn" id="autoRefreshBtn" onclick="toggleAutoRefresh()">
                        ▶️ Activar Auto-Refresh (5s)
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <?php
        if (isset($_GET['order_id'])) {
            require_once __DIR__ . '/config.php';
            
            $orderId = $_GET['order_id'];
            $accessToken = MP_ACCESS_TOKEN;
            
            $ch = curl_init("https://api.mercadopago.com/v1/orders/{$orderId}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $order = json_decode($response, true);
                $status = $order['status'];
                $statusClass = 'status-' . $status;
                
                $statusLabels = [
                    'created' => '🟡 CREADO - Esperando pago',
                    'approved' => '✅ APROBADO',
                    'rejected' => '❌ RECHAZADO',
                    'cancelled' => '🚫 CANCELADO'
                ];
                
                echo '<div class="card">';
                echo '<h2 style="margin-bottom: 20px;">Estado del Pago</h2>';
                echo '<div style="margin-bottom: 20px;">';
                echo '<span class="status-badge ' . $statusClass . '">' . ($statusLabels[$status] ?? $status) . '</span>';
                echo '</div>';
                
                echo '<div class="info-grid">';
                
                echo '<div class="info-item">';
                echo '<strong>Order ID</strong>';
                echo '<span>' . htmlspecialchars($order['id']) . '</span>';
                echo '</div>';
                
                echo '<div class="info-item">';
                echo '<strong>Monto</strong>';
                echo '<span>$' . number_format($order['transactions']['payments'][0]['amount'], 0, ',', '.') . ' CLP</span>';
                echo '</div>';
                
                echo '<div class="info-item">';
                echo '<strong>Referencia</strong>';
                echo '<span>' . htmlspecialchars($order['external_reference']) . '</span>';
                echo '</div>';
                
                echo '<div class="info-item">';
                echo '<strong>Creado</strong>';
                echo '<span>' . date('d/m/Y H:i:s', strtotime($order['created_date'])) . '</span>';
                echo '</div>';
                
                if (isset($order['transactions']['payments'][0]['id'])) {
                    $paymentId = $order['transactions']['payments'][0]['id'];
                    echo '<div class="info-item">';
                    echo '<strong>Payment ID</strong>';
                    echo '<span>' . htmlspecialchars($paymentId) . '</span>';
                    echo '</div>';
                }
                
                echo '</div>';
                
                // Mostrar JSON completo
                echo '<details style="margin-top: 20px;">';
                echo '<summary style="cursor: pointer; font-weight: bold; padding: 10px; background: #f8f9fa; border-radius: 5px;">📄 Ver JSON completo</summary>';
                echo '<pre>' . json_encode($order, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                echo '</details>';
                
                echo '</div>';
            } else {
                echo '<div class="card">';
                echo '<h2 style="color: #dc3545;">❌ Error al consultar</h2>';
                echo '<p>HTTP Code: ' . $httpCode . '</p>';
                echo '<pre>' . htmlspecialchars($response) . '</pre>';
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html>
