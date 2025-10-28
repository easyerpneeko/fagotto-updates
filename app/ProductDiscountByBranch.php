<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductDiscountByBranch extends Model
{
    protected $table = 'product_discounts_by_branch';
    
    protected $fillable = [
        'application_id',
        'product_id',
        'product_category',
        'product_name_pattern',
        'discount_percentage',
        'discount_amount',
        'active',
        'start_date',
        'end_date',
        'description'
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    /**
     * Relación con la aplicación/sucursal
     */
    public function application()
    {
        return $this->belongsTo(Aplication::class, 'application_id');
    }

    /**
     * Relación con el producto
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Verificar si el descuento está vigente
     */
    public function isValid()
    {
        if (!$this->active) {
            return false;
        }

        $now = now();

        // Verificar fechas si existen
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Obtener descuentos activos para una sucursal
     */
    public static function getActiveDiscountsForBranch($applicationId)
    {
        return self::where('application_id', $applicationId)
            ->where('active', 1)
            ->where(function ($query) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            })
            ->get();
    }

    /**
     * Calcular descuento para un producto
     */
    public function calculateDiscount($productPrice, $productName = null)
    {
        if (!$this->isValid()) {
            return 0;
        }

        // Si es descuento por porcentaje
        if ($this->discount_percentage > 0) {
            return $productPrice * ($this->discount_percentage / 100);
        }

        // Si es descuento por monto fijo
        if ($this->discount_amount > 0) {
            return min($this->discount_amount, $productPrice); // No puede ser mayor al precio
        }

        return 0;
    }

    /**
     * Verificar si aplica a un producto
     */
    public function appliesToProduct($productId, $productName = null, $productCategory = null)
    {
        // Si está especificado el ID del producto
        if ($this->product_id && $this->product_id == $productId) {
            return true;
        }

        // Si es por patrón de nombre
        if ($this->product_name_pattern && $productName) {
            $pattern = str_replace('%', '.*', $this->product_name_pattern);
            if (preg_match("/$pattern/i", $productName)) {
                return true;
            }
        }

        // Si es por categoría
        if ($this->product_category && $productCategory) {
            if (stripos($productCategory, $this->product_category) !== false) {
                return true;
            }
        }

        return false;
    }
}
