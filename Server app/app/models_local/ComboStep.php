<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class ComboStep extends Model
{
    protected $connection = 'mysql_local';
    protected $table = 'combo_steps';

    protected $fillable = [
        'product_id',
        'step_order',
        'step_label',
        'category_id'
    ];

    /**
     * Relación con el producto
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Relación con la categoría
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Obtener pasos de un combo ordenados
     */
    public static function getStepsByProduct($productId)
    {
        \Log::info("🔍 ComboStep::getStepsByProduct called for product_id: $productId");
        
        $steps = self::where('product_id', $productId)
            ->orderBy('step_order', 'asc')
            ->get();
        
        \Log::info("📦 Found " . $steps->count() . " steps for product $productId");
        \Log::info("📋 Steps data: " . json_encode($steps->toArray()));
        
        return $steps;
    }

    /**
     * Guardar/actualizar pasos de un combo
     */
    public static function saveSteps($productId, $steps)
    {
        // Eliminar pasos anteriores
        self::where('product_id', $productId)->delete();

        // Crear nuevos pasos
        if (is_array($steps) && count($steps) > 0) {
            foreach ($steps as $index => $step) {
                self::create([
                    'product_id' => $productId,
                    'step_order' => $index + 1,
                    'step_label' => $step['label'] ?? 'Elige un producto',
                    'category_id' => $step['category_id']
                ]);
            }
        }

        return true;
    }
}
