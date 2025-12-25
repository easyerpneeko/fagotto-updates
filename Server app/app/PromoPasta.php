<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromoPasta extends Model
{
    protected $connection = 'mysql';
    protected $table = 'promo_pasta_types';

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
     * Obtener solo pastas activas sin duplicados
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
