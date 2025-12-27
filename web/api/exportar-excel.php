<?php
/**
 * API: Exportar registros a Excel
 */
require_once '../config.php';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="asistencias_' . date('Y-m-d_His') . '.xls"');

$appId = $_GET['app_id'] ?? '';
$employeeId = $_GET['employee_id'] ?? '';
$fechaDesde = $_GET['fecha_desde'] ?? '';
$fechaHasta = $_GET['fecha_hasta'] ?? '';

try {
    $pdo = getDB();
    
    // Construir query con filtros
    $sql = "
        SELECT 
            r.fecha_hora,
            r.tipo_marcacion,
            l.nombre as local,
            e.nombre as empleado,
            e.rut,
            e.cargo,
            r.coincidencia_facial,
            r.gps_lat,
            r.gps_lng
        FROM asistencias_records r
        LEFT JOIN asistencias_employees e ON r.employee_id = e.id
        LEFT JOIN asistencias_locales l ON r.app_id = l.app_id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($appId) {
        $sql .= " AND r.app_id = ?";
        $params[] = $appId;
    }
    
    if ($employeeId) {
        $sql .= " AND r.employee_id = ?";
        $params[] = $employeeId;
    }
    
    if ($fechaDesde) {
        $sql .= " AND DATE(r.fecha_hora) >= ?";
        $params[] = $fechaDesde;
    }
    
    if ($fechaHasta) {
        $sql .= " AND DATE(r.fecha_hora) <= ?";
        $params[] = $fechaHasta;
    }
    
    $sql .= " ORDER BY r.fecha_hora DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular horas trabajadas
    $empleados = [];
    foreach ($registros as $reg) {
        $emp = $reg['empleado'];
        $fecha = date('Y-m-d', strtotime($reg['fecha_hora']));
        $key = $emp . '_' . $fecha;
        
        if (!isset($empleados[$key])) {
            $empleados[$key] = [
                'empleado' => $emp,
                'rut' => $reg['rut'],
                'cargo' => $reg['cargo'],
                'local' => $reg['local'],
                'fecha' => $fecha,
                'entrada' => null,
                'salida_colacion' => null,
                'regreso_colacion' => null,
                'salida' => null,
                'horas_trabajadas' => 0
            ];
        }
        
        $empleados[$key][$reg['tipo_marcacion']] = $reg['fecha_hora'];
    }
    
    // Calcular horas
    foreach ($empleados as &$emp) {
        $entrada = strtotime($emp['entrada']);
        $salida = strtotime($emp['salida']);
        $salida_col = strtotime($emp['salida_colacion']);
        $regreso_col = strtotime($emp['regreso_colacion']);
        
        if ($entrada && $salida) {
            $horas_totales = ($salida - $entrada) / 3600;
            
            // Descontar tiempo de colación
            if ($salida_col && $regreso_col) {
                $horas_colacion = ($regreso_col - $salida_col) / 3600;
                $horas_totales -= $horas_colacion;
            }
            
            $emp['horas_trabajadas'] = round($horas_totales, 2);
        }
    }
    
    // Generar Excel
    echo '<table border="1">';
    echo '<tr style="background-color: #667eea; color: white; font-weight: bold;">';
    echo '<th>Fecha</th>';
    echo '<th>Empleado</th>';
    echo '<th>RUT</th>';
    echo '<th>Cargo</th>';
    echo '<th>Local</th>';
    echo '<th>Entrada</th>';
    echo '<th>Salida Colación</th>';
    echo '<th>Regreso Colación</th>';
    echo '<th>Salida</th>';
    echo '<th>Horas Trabajadas</th>';
    echo '</tr>';
    
    foreach ($empleados as $emp) {
        echo '<tr>';
        echo '<td>' . date('d/m/Y', strtotime($emp['fecha'])) . '</td>';
        echo '<td>' . htmlspecialchars($emp['empleado']) . '</td>';
        echo '<td>' . htmlspecialchars($emp['rut']) . '</td>';
        echo '<td>' . htmlspecialchars($emp['cargo']) . '</td>';
        echo '<td>' . htmlspecialchars($emp['local']) . '</td>';
        echo '<td>' . ($emp['entrada'] ? date('H:i', strtotime($emp['entrada'])) : '-') . '</td>';
        echo '<td>' . ($emp['salida_colacion'] ? date('H:i', strtotime($emp['salida_colacion'])) : '-') . '</td>';
        echo '<td>' . ($emp['regreso_colacion'] ? date('H:i', strtotime($emp['regreso_colacion'])) : '-') . '</td>';
        echo '<td>' . ($emp['salida'] ? date('H:i', strtotime($emp['salida'])) : '-') . '</td>';
        echo '<td>' . number_format($emp['horas_trabajadas'], 2) . ' hrs</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
} catch (Exception $e) {
    echo '<h3>Error: ' . $e->getMessage() . '</h3>';
}
