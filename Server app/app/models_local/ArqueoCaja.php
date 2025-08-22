<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class ArqueoCaja extends Model
{
    protected $table = 'arqueo_caja';

    protected $fillable = [
        'app_id',
        'fecha_arqueo',
        'total_contado',
        'total_ventas_efectivo',
        'diferencia',
        'detalle_conteo',
        'observaciones',
        'usuario_id',
        'usuario_nombre',
        'negocio_id',
        'negocio_nombre',
        'estado'
    ];

    protected $casts = [
        'fecha_arqueo' => 'date',
        'total_contado' => 'decimal:2',
        'total_ventas_efectivo' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con la aplicación
     */
    public function application()
    {
        return $this->belongsTo('App\Aplication', 'app_id');
    }

    /**
     * Obtener el detalle del conteo como array
     */
    public function getDetalleConteoAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Establecer el detalle del conteo como JSON
     */
    public function setDetalleConteoAttribute($value)
    {
        $this->attributes['detalle_conteo'] = is_string($value) ? $value : json_encode($value);
    }

    /**
     * Scope para filtrar por aplicación
     */
    public function scopeByApp($query, $appId)
    {
        return $query->where('app_id', $appId);
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopeByDate($query, $fecha)
    {
        return $query->whereDate('fecha_arqueo', $fecha);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('fecha_arqueo', [$startDate, $endDate]);
    }

    /**
     * Calcular el estado del arqueo basado en la diferencia
     */
    public function getEstadoCalculadoAttribute()
    {
        if ($this->diferencia == 0) {
            return 'exacto';
        } elseif ($this->diferencia > 0) {
            return 'sobrante';
        } else {
            return 'faltante';
        }
    }

    /**
     * Obtener el porcentaje de diferencia respecto a las ventas
     */
    public function getPorcentajeDiferenciaAttribute()
    {
        if ($this->total_ventas_efectivo == 0) {
            return 0;
        }
        
        return round(($this->diferencia / $this->total_ventas_efectivo) * 100, 2);
    }

    /**
     * Validar si el arqueo está dentro del rango aceptable
     * (por ejemplo, diferencia menor al 1% de las ventas)
     */
    public function esRangoAceptable($porcentajeMaximo = 1)
    {
        return abs($this->porcentaje_diferencia) <= $porcentajeMaximo;
    }
}
