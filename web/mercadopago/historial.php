<?php
/**
 * Historial de Pagos MercadoPago Point
 * Muestra todos los pagos enviados al terminal con sus estados
 */

// Función para leer y parsear logs
function getLogs($date = null) {
    $logsDir = __DIR__ . '/storage/logs/';
    $logs = [];
    
    if (!is_dir($logsDir)) {
        return $logs;
    }
    
    // Si no se especifica fecha, buscar últimos 7 días
    $daysToSearch = $date ? [$date] : array_map(function($i) {
        return date('Y-m-d', strtotime("-$i days"));
    }, range(0, 6));
    
    foreach ($daysToSearch as $day) {
        $logFile = $logsDir . 'api-' . $day . '.log';
        
        if (!file_exists($logFile)) {
            continue;
        }
        
        $content = file_get_contents($logFile);
        
        // Separar por entradas (doble salto de línea)
        $entries = explode("\n\n", trim($content));
        
        foreach ($entries as $entry) {
            $entry = trim($entry);
            if (empty($entry)) continue;
            
            $data = json_decode($entry, true);
            if (!$data) continue;
            
            // Solo mostrar entradas de "sending_to_mercadopago"
            if (isset($data['action']) && $data['action'] === 'sending_to_mercadopago') {
                $logs[] = $data;
            }
        }
    }
    
    // Ordenar por timestamp descendente (más reciente primero)
    usort($logs, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return $logs;
}

// Obtener fecha del filtro (si existe)
$filterDate = $_GET['date'] ?? null;
$logs = getLogs($filterDate);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial MercadoPago Point - Fagotto Las Condes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .filters {
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .filters label {
            font-weight: 600;
            color: #495057;
        }
        
        .filters input[type="date"],
        .filters button {
            padding: 10px 20px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 1em;
        }
        
        .filters button {
            background: #667eea;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .filters button:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .stats {
            padding: 20px 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            background: #fff;
        }
        
        .stat-card {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            color: white;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        thead {
            background: #f8f9fa;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .badge-credit {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-debit {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .amount {
            font-weight: 700;
            color: #28a745;
            font-size: 1.1em;
        }
        
        .order-id {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            color: #6c757d;
        }
        
        .details-btn {
            padding: 6px 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s;
        }
        
        .details-btn:hover {
            background: #5568d3;
            transform: scale(1.05);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 2em;
            cursor: pointer;
            color: #6c757d;
        }
        
        .modal-close:hover {
            color: #dc3545;
        }
        
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💳 Historial MercadoPago Point</h1>
            <p>Fagotto Las Condes - Registro de transacciones al terminal</p>
        </div>
        
        <div class="filters">
            <label>📅 Filtrar por fecha:</label>
            <input type="date" id="dateFilter" value="<?= htmlspecialchars($filterDate ?? '') ?>">
            <button onclick="filterByDate()">Filtrar</button>
            <button onclick="clearFilter()">Ver todos (7 días)</button>
        </div>
        
        <?php
        // Calcular estadísticas
        $totalPagos = count($logs);
        $totalMonto = 0;
        $totalCredito = 0;
        $totalDebito = 0;
        
        foreach ($logs as $log) {
            if (isset($log['payload']['transactions']['payments'][0]['amount'])) {
                $monto = (int)$log['payload']['transactions']['payments'][0]['amount'];
                $totalMonto += $monto;
                
                $tipo = $log['mercadopago_payment_type'] ?? 'debit_card';
                if ($tipo === 'credit_card') {
                    $totalCredito++;
                } else {
                    $totalDebito++;
                }
            }
        }
        ?>
        
        <div class="stats">
            <div class="stat-card">
                <h3><?= $totalPagos ?></h3>
                <p>Total de Pagos</p>
            </div>
            <div class="stat-card">
                <h3>$<?= number_format($totalMonto, 0, ',', '.') ?></h3>
                <p>Monto Total</p>
            </div>
            <div class="stat-card">
                <h3><?= $totalCredito ?></h3>
                <p>Pagos Crédito</p>
            </div>
            <div class="stat-card">
                <h3><?= $totalDebito ?></h3>
                <p>Pagos Débito</p>
            </div>
        </div>
        
        <div class="content">
            <?php if (empty($logs)): ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h2>No hay pagos registrados</h2>
                    <p>Los pagos enviados al terminal MercadoPago aparecerán aquí</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>📅 Fecha/Hora</th>
                            <th>💰 Monto</th>
                            <th>💳 Tipo</th>
                            <th>🆔 Order ID</th>
                            <th>📝 Descripción</th>
                            <th>⚙️ Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $index => $log): ?>
                            <?php
                            $monto = isset($log['payload']['transactions']['payments'][0]['amount']) 
                                ? (int)$log['payload']['transactions']['payments'][0]['amount'] 
                                : 0;
                            $tipo = $log['mercadopago_payment_type'] ?? 'debit_card';
                            $tipoBadge = $tipo === 'credit_card' ? 'badge-credit' : 'badge-debit';
                            $tipoText = $tipo === 'credit_card' ? 'Crédito' : 'Débito';
                            $orderId = $log['payload']['external_reference'] ?? 'N/A';
                            $descripcion = $log['payload']['description'] ?? 'Sin descripción';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($log['timestamp']) ?></td>
                                <td><span class="amount">$<?= number_format($monto, 0, ',', '.') ?></span></td>
                                <td><span class="badge <?= $tipoBadge ?>"><?= $tipoText ?></span></td>
                                <td><span class="order-id"><?= htmlspecialchars(substr($orderId, 0, 20)) ?>...</span></td>
                                <td><?= htmlspecialchars($descripcion) ?></td>
                                <td>
                                    <button class="details-btn" onclick="showDetails(<?= $index ?>)">
                                        Ver detalles
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal para detalles -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📋 Detalles del Pago</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>
    
    <script>
        const logsData = <?= json_encode($logs) ?>;
        
        function showDetails(index) {
            const log = logsData[index];
            const modal = document.getElementById('detailsModal');
            const modalBody = document.getElementById('modalBody');
            
            modalBody.innerHTML = `
                <h3>Información General</h3>
                <pre>${JSON.stringify(log, null, 2)}</pre>
            `;
            
            modal.classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('detailsModal').classList.remove('show');
        }
        
        function filterByDate() {
            const date = document.getElementById('dateFilter').value;
            if (date) {
                window.location.href = '?date=' + date;
            }
        }
        
        function clearFilter() {
            window.location.href = window.location.pathname;
        }
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('detailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
