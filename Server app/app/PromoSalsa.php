<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromoSalsa extends Model
{
    protected $connection = 'mysql';
    protected $table = 'promo_salsa_types';

    protected $fillable = [
        'name',
        'description',
        'active',
        'order_display'
    ];

    protected $casts = [
        'active' => 'boolean',
        'order_display' => 'integer',
    ];

    /**
     * Obtener solo salsas activas sin duplicados
     */
    public static function getActivas()
    {
        return self::where('active', 1)
            ->orderBy('order_display')
            ->get()
            ->unique('name')
            ->values();
    }
}
