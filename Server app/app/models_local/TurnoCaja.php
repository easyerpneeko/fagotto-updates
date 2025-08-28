<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class TurnoCaja extends Model
{
    protected $connection = 'mysql_local';
    protected $table = 'turnos_caja';
    
    protected $fillable = [
        'app_id',
        'usuario_id', 
        'usuario_nombre',
        'app_nombre',
        'fecha_inicio',
        'fecha_termino',
        'total_sistema',
        'total_contado',
        'diferencia',
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
        'total_contado' => 'decimal:2',
        'total_sistema' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'turno_abierto' => 'boolean',
        'puede_hacer_arqueo' => 'boolean'
    ];

    // Método para verificar si hay un turno abierto
    public static function turnoAbiertoParaUsuario($usuarioId, $appId) {
        return self::where('usuario_id', $usuarioId)
                   ->where('app_id', $appId)
                   ->where('turno_abierto', true)
                   ->whereNull('turno_cerrado_en')
                   ->first();
    }

    // Método para iniciar nuevo turno
    public static function iniciarTurno($usuarioId, $usuarioNombre, $appId, $appNombre) {
        return self::create([
            'app_id' => $appId,
            'usuario_id' => $usuarioId,
            'usuario_nombre' => $usuarioNombre,
            'app_nombre' => $appNombre,
            'fecha_inicio' => now(),
            'estado' => 'abierto',
            'turno_abierto' => true,
            'puede_hacer_arqueo' => true,
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
            'total_sistema' => $datosArqueo['total_sistema'],
            'total_contado' => $datosArqueo['total_contado'],
            'diferencia' => $datosArqueo['diferencia'],
            'observaciones' => $datosArqueo['observaciones'] ?? '',
            'detalle_efectivo' => $datosArqueo['detalle_efectivo'] ?? '{}',
            'detalle_medios_pago' => $datosArqueo['detalle_medios_pago'] ?? '{}',
            'numero_transacciones' => $datosArqueo['numero_transacciones'] ?? 0
        ]);
    }
}
