<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class TurnoCaja extends Model
{
    protected $connection = 'mysql_local';
    protected $table = 'TurnosCaja';  // ✅ CORREGIR: Usar el nombre correcto de la tabla
    
    protected $fillable = [
        'app_id',
        'usuario_id', 
        'usuario_nombre',
        'app_nombre',
        'fecha_inicio',
        'fecha_termino',
        'monto_inicial',          // ✅ AGREGAR: Monto inicial del turno
        'monto_final',            // ✅ AGREGAR: Monto final contado
        'total_sistema',
        'total_contado',          // ✅ CORREGIR: Este es el total general
        'total_otros_medios',     // ✅ AGREGAR: Total otros medios de pago
        'diferencia',
        'diferencia_general',     // ✅ AGREGAR: Diferencia total general
        'estado',
        'observaciones',
        'detalle_efectivo',
        'detalle_medios_pago',
        'numero_transacciones',
        'turno_abierto',
        'turno_cerrado_en',
        'puede_hacer_arqueo'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_termino' => 'datetime',
        'turno_cerrado_en' => 'datetime',
        'monto_inicial' => 'decimal:2',       // ✅ AGREGAR: Cast para monto inicial
        'monto_final' => 'decimal:2',         // ✅ AGREGAR: Cast para monto final  
        'total_contado' => 'decimal:2',
        'total_sistema' => 'decimal:2',
        'total_otros_medios' => 'decimal:2',  // ✅ AGREGAR: Cast para otros medios
        'diferencia' => 'decimal:2',
        'diferencia_general' => 'decimal:2',  // ✅ AGREGAR: Cast para diferencia general
        'turno_abierto' => 'boolean',
        'puede_hacer_arqueo' => 'boolean'
    ];

    // Método para verificar si hay un turno abierto
    public static function turnoAbiertoParaUsuario($usuarioId, $appId) {
        return self::where('usuario_id', $usuarioId)
                   ->where('app_id', $appId)
                   ->where('estado', 'abierto') // 🔧 ARREGLO: Usar 'estado' en lugar de 'turno_abierto'
                   ->whereNull('fecha_termino') // 🔧 ARREGLO: Usar 'fecha_termino' en lugar de 'turno_cerrado_en'
                   ->first();
    }

    // Método para iniciar nuevo turno
    public static function iniciarTurno($usuarioId, $usuarioNombre, $appId, $appNombre, $montoInicial = 0) {
        return self::create([
            'app_id' => $appId,
            'usuario_id' => $usuarioId,
            'usuario_nombre' => $usuarioNombre,
            'app_nombre' => $appNombre,
            'fecha_inicio' => now(),
            'estado' => 'abierto',
            'turno_abierto' => true,
            'puede_hacer_arqueo' => true,
            'monto_inicial' => $montoInicial,  // ✅ AGREGAR: Guardar monto inicial
            'total_sistema' => 0,
            'total_contado' => 0,
            'diferencia' => 0
        ]);
    }

    // Método para cerrar turno
    public function cerrarTurno($datosArqueo) {
        $this->update([
            'fecha_termino' => now(),
            'turno_cerrado_en' => now(),
            'turno_abierto' => false,
            'puede_hacer_arqueo' => false,
            'estado' => 'cerrado',
            
            // ✅ CAMPOS MONETARIOS CORREGIDOS
            'monto_inicial' => $datosArqueo['monto_inicial'] ?? $this->monto_inicial ?? 0,
            'monto_final' => $datosArqueo['monto_final'] ?? 0,
            'total_sistema' => $datosArqueo['total_sistema'] ?? 0,
            'total_contado' => $datosArqueo['total_contado'] ?? 0,          // Total general
            'total_otros_medios' => $datosArqueo['total_otros_medios'] ?? 0,
            'diferencia' => $datosArqueo['diferencia'] ?? 0,               // Diferencia efectivo
            'diferencia_general' => $datosArqueo['diferencia_general'] ?? 0, // Diferencia total
            
            // ✅ CAMPOS ADICIONALES
            'observaciones' => $datosArqueo['observaciones'] ?? '',
            'detalle_efectivo' => $datosArqueo['detalle_efectivo'] ?? '{}',
            'detalle_medios_pago' => $datosArqueo['detalle_medios_pago'] ?? '{}',
            'numero_transacciones' => $datosArqueo['numero_transacciones'] ?? 0
        ]);
    }
}
