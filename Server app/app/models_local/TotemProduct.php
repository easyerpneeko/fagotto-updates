<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class TotemProduct extends Model
{
    protected $connection = 'mysql';
    protected $table = 'totem_productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'imagen',
        'categoria_id',
        'activo',
        'orden'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
        'orden' => 'integer'
    ];

    // Relación con categoría
    public function categoria()
    {
        return $this->belongsTo(TotemCategory::class, 'categoria_id');
    }

    // Scope para productos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }

    // Scope ordenados
    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden', 'asc')->orderBy('nombre', 'asc');
    }
}
