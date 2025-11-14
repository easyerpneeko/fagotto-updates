<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GlobalIngredient
 *
 * Este modelo representa la tabla `global_ingredients` en la base de datos principal.
 * Su propósito es almacenar el gramaje (`quantity_grams`) global
 * asociado a un `ingredient_id` específico.
 *
 * Se asume que el `ingredient_id` en esta tabla corresponde al `id`
 * de los ingredientes en las tablas `ingredients` locales de cada sucursal.
 *
 */
class GlobalIngredient extends Model
{

    // Define el nombre de la tabla en la base de datos
    protected $table = 'global_ingredients'; 

    // Clave primaria de la tabla
    protected $primaryKey = 'id';

    // Indica si la clave primaria es auto-incremental
    public $incrementing = true;

    // Tipo de la clave primaria
    protected $keyType = 'int';

    // Indica si el modelo debe manejar automáticamente las columnas created_at y updated_at
    public $timestamps = true;

    /**
     * Los atributos que son asignables en masa.
     * `ingredient_id`: El ID del ingrediente local al que se aplica este gramaje.
     * `quantity_grams`: El gramaje global asociado a este `ingredient_id`.
     */
    protected $fillable = [
        'ingredient_id',
        'quantity_grams',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     * Asegura que `ingredient_id` se maneje como un entero
     * y `quantity_grams` como un decimal con 2 puntos de precisión.
     */
    protected $casts = [
        'ingredient_id' => 'integer',
        'quantity_grams' => 'decimal:2',
    ];

    /**
     * Relaciones:
     * En este esquema, no hay una relación directa de Eloquent `belongsTo`
     * a un modelo `Ingredient` global o local aquí, porque la tabla `global_ingredients`
     * que proporcionaste actúa como una tabla de lookup para el gramaje.
     *
     * La asociación lógica es la siguiente:
     * `local_ingredients.id` (en la BD de sucursal) <--- mapea a ---> `global_ingredients.ingredient_id` (en la BD principal)
     *
     * Si existiera una tabla maestra `global_master_ingredients` con los nombres y
     * otras propiedades globales del ingrediente, y si `ingredient_id` en esta
     * tabla `global_ingredients` fuera una clave foránea a esa tabla maestra,
     * entonces podrías definir una relación como:
     *
     * public function masterIngredient()
     * {
     * return $this->belongsTo(GlobalMasterIngredient::class, 'ingredient_id', 'id');
     * }
     * Pero esto depende de si tienes esa tabla `GlobalMasterIngredient` y si `ingredient_id` se refiere a ella.
     */
}