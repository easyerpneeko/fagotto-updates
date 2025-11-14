<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class TurnoCaja extends Model
{
    protected $connection = 'mysql_local';
    protected $table = 'TurnosCaja';
    
    protected $fillable = [
        'app_id',
        'app_nombre',
        'usuario_id', 
        'usuario_nombre',
        'fecha_inicio',
        'fecha_termino',
        'monto_inicial',
        'monto_final',
        'total_sistema',
        'total_contado',
        'total_otros_medios',
        'diferencia',
        'diferencia_general',
        'estado',
        'observaciones',
        'detalle_efectivo',
        'detalle_medios_pago',
        'numero_transacciones',
        'turno_abierto',
        'turno_abierto_en',
        'turno_cerrado_en',
        'puede_hacer_arqueo'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_termino' => 'datetime',
        'turno_abierto_en' => 'datetime',
        'turno_cerrado_en' => 'datetime',
        'monto_inicial' => 'decimal:2',
        'monto_final' => 'decimal:2',
        'total_contado' => 'decimal:2',
        'total_sistema' => 'decimal:2',
        'total_otros_medios' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'diferencia_general' => 'decimal:2',
        'turno_abierto' => 'boolean',
        'puede_hacer_arqueo' => 'boolean'
    ];

    public static function turnoAbiertoParaUsuario($usuarioId, $appId) {
        return self::where('usuario_id', $usuarioId)
                   ->where('app_id', $appId)
                   ->where(function($query) {
                       $query->where('turno_abierto', true)
                             ->orWhere('estado', 'abierto');
                   })
                   ->where(function($query) {
                       $query->whereNull('fecha_termino')
                             ->orWhereNull('turno_cerrado_en');
                   })
                   ->first();
    }

    public static function iniciarTurno($usuarioId, $usuarioNombre, $appId, $appNombre, $montoInicial = 0) {
        return self::create([
            'app_id' => $appId,
            'usuario_id' => $usuarioId,
            'usuario_nombre' => $usuarioNombre,
            'app_nombre' => $appNombre,
            'fecha_inicio' => now(),
            'estado' => 'abierto',
            'turno_abierto' => true,
            'turno_abierto_en' => now(),
            'puede_hacer_arqueo' => true,
            'monto_inicial' => $montoInicial,
            'monto_final' => 0,
            'total_sistema' => 0,
            'total_contado' => 0,
            'total_otros_medios' => 0,
            'diferencia' => 0,
            'diferencia_general' => 0,
            'observaciones' => null,
            'detalle_efectivo' => '{}',
            'detalle_medios_pago' => '{}',
            'numero_transacciones' => 0
        ]);
    }

    public function cerrarTurno($datosArqueo) {
        $this->update([
            'fecha_termino' => now(),
            'turno_cerrado_en' => now(),
            'turno_abierto' => false,
            'puede_hacer_arqueo' => false,
            'estado' => 'cerrado',
            
            'monto_inicial' => $datosArqueo['monto_inicial'] ?? $this->monto_inicial ?? 0,
            'monto_final' => $datosArqueo['monto_final'] ?? 0,
            'total_sistema' => $datosArqueo['total_sistema'] ?? 0,
            'total_contado' => $datosArqueo['total_contado'] ?? 0,
            'total_otros_medios' => $datosArqueo['total_otros_medios'] ?? 0,
            'diferencia' => $datosArqueo['diferencia'] ?? 0,
            'diferencia_general' => $datosArqueo['diferencia_general'] ?? 0,
            
            'observaciones' => $datosArqueo['observaciones'] ?? '',
            'detalle_efectivo' => is_array($datosArqueo['detalle_efectivo']) ? 
                                   json_encode($datosArqueo['detalle_efectivo']) : 
                                   ($datosArqueo['detalle_efectivo'] ?? '{}'),
            'detalle_medios_pago' => is_array($datosArqueo['detalle_medios_pago']) ? 
                                      json_encode($datosArqueo['detalle_medios_pago']) : 
                                      ($datosArqueo['detalle_medios_pago'] ?? '{}'),
            'numero_transacciones' => $datosArqueo['numero_transacciones'] ?? 0
        ]);
        
        return $this;
    }
}
