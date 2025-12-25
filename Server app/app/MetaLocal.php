<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetaLocal extends Model
{
    protected $connection = 'mysql';
    protected $table = 'metas_locales';

    protected $fillable = [
        'sucursal_id',
        'sucursal_nombre',
        'meta_lunes',
        'meta_martes',
        'meta_miercoles',
        'meta_jueves',
        'meta_viernes',
        'meta_sabado',
        'meta_domingo',
        'fecha_inicio',
        'activo'
    ];

    protected $casts = [
        'meta_lunes' => 'float',
        'meta_martes' => 'float',
        'meta_miercoles' => 'float',
        'meta_jueves' => 'float',
        'meta_viernes' => 'float',
        'meta_sabado' => 'float',
        'meta_domingo' => 'float',
        'activo' => 'boolean',
        'fecha_inicio' => 'date'
    ];

    /**
     * Obtener la meta para un día específico (0=Domingo, 6=Sábado)
     */
    public function getMetaParaDia($numeroDia)
    {
        $dias = [
            0 => 'meta_domingo',
            1 => 'meta_lunes',
            2 => 'meta_martes',
            3 => 'meta_miercoles',
            4 => 'meta_jueves',
            5 => 'meta_viernes',
            6 => 'meta_sabado'
        ];

        return $this->{$dias[$numeroDia]} ?? 0;
    }

    /**
     * Obtener todas las metas como array
     */
    public function getMetasArray()
    {
        return [
            'lunes' => $this->meta_lunes,
            'martes' => $this->meta_martes,
            'miercoles' => $this->meta_miercoles,
            'jueves' => $this->meta_jueves,
            'viernes' => $this->meta_viernes,
            'sabado' => $this->meta_sabado,
            'domingo' => $this->meta_domingo
        ];
    }

    /**
     * Relación con la aplicación/sucursal
     */
    public function sucursal()
    {
        return $this->belongsTo('App\Aplication', 'sucursal_id', 'id');
    }
}
