<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CuponUso extends Model
{
    protected $connection = 'mysql';
    protected $table = 'cupones_usos';
    
    public $timestamps = false;

    protected $fillable = [
        'cupon_id',
        'orden_id',
        'usuario_id',
        'descuento_aplicado',
        'usado_en'
    ];

    protected $casts = [
        'descuento_aplicado' => 'decimal:2',
        'usado_en' => 'datetime',
    ];

    /**
     * Relación con el cupón
     */
    public function cupon()
    {
        return $this->belongsTo(Cupon::class, 'cupon_id');
    }
}
