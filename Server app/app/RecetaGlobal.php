<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RecetaGlobal
 * 
 * Modelo para las recetas globales almacenadas en la BD Maestra
 * Estas son plantillas que pueden ser usadas por todos los negocios
 */
class RecetaGlobal extends Model
{
    protected $connection = 'mysql'; // Conexión a BD Maestra
    protected $table = 'recetas_global';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'observaciones',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    /**
     * Relación con ingredientes de la receta
     */
    public function ingredientes()
    {
        return $this->hasMany(RecetaIngredienteGlobal::class, 'receta_global_id')
                    ->orderBy('orden', 'asc');
    }

    /**
     * Scope para obtener solo recetas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
