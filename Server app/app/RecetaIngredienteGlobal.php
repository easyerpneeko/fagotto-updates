<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecetaIngredienteGlobal extends Model
{
    protected $connection = 'mysql';
    protected $table = 'receta_ingredientes_global';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'receta_global_id',
        'ingrediente_nombre',
        'cantidad',
        'unidad',
        'orden',
        'opcional',
        'notas',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'opcional' => 'boolean',
        'orden' => 'integer',
    ];

    public function receta()
    {
        return $this->belongsTo(RecetaGlobal::class, 'receta_global_id');
    }
}
