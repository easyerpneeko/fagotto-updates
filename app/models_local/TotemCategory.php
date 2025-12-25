<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class TotemCategory extends Model
{
    protected $connection = 'mysql_local';
    protected $table = 'totem_categorias';

    protected $fillable = [
        'nombre',
        'icono',
        'color',
        'orden',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer'
    ];

    // Relación con productos
    public function productos()
    {
        return $this->hasMany(TotemProduct::class, 'categoria_id');
    }

    // Scope para categorías activas
    public function scopeActivas($query)
    {
        return $query->where('activo', 1);
    }

    // Scope ordenadas
    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden', 'asc')->orderBy('nombre', 'asc');
    }
}
