<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cupon extends Model
{
    protected $connection = 'mysql';
    protected $table = 'cupones';

    protected $fillable = [
        'codigo',
        'descripcion',
        'tipo_descuento',
        'valor_descuento',
        'activo',
        'usado',
        'usado_en',
        'fecha_expiracion',
        'orden_id',
        'sucursal_id',
        'sucursal_nombre',
        'usuario_id',
        'usuario_nombre',
        'categoria_id',
        'categoria_nombre',
        'producto_id',
        'producto_nombre',
        'precio_original',
        'precio_con_cupon'
    ];

    protected $casts = [
        'valor_descuento' => 'decimal:2',
        'monto_minimo' => 'decimal:2',
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_expiracion' => 'date',
    ];

    /**
     * Relación con los usos del cupón
     */
    public function usos()
    {
        return $this->hasMany(CuponUso::class, 'cupon_id');
    }

    /**
     * Validar si el cupón es válido para usar
     */
    public function esValido($montoCompra = null)
    {
        $ahora = Carbon::now();

        // Verificar si está activo
        if (!$this->activo) {
            return ['valido' => false, 'mensaje' => 'El cupón está inactivo'];
        }

        // Verificar fecha de inicio
        if ($this->fecha_inicio && $ahora->lt($this->fecha_inicio)) {
            return ['valido' => false, 'mensaje' => 'El cupón aún no está disponible'];
        }

        // Verificar fecha de expiración
        if ($this->fecha_expiracion && $ahora->gt($this->fecha_expiracion)) {
            return ['valido' => false, 'mensaje' => 'El cupón ha expirado'];
        }

        // Verificar usos disponibles
        if ($this->usos_actuales >= $this->usos_maximos) {
            return ['valido' => false, 'mensaje' => 'El cupón ya fue utilizado'];
        }

        // Verificar monto mínimo
        if ($this->monto_minimo && $montoCompra && $montoCompra < $this->monto_minimo) {
            return ['valido' => false, 'mensaje' => "El monto mínimo de compra es $" . number_format($this->monto_minimo, 0, ',', '.')];
        }

        return ['valido' => true, 'mensaje' => 'Cupón válido'];
    }

    /**
     * Calcular el descuento a aplicar
     */
    public function calcularDescuento($montoCompra)
    {
        if ($this->tipo_descuento === 'porcentaje') {
            return ($montoCompra * $this->valor_descuento) / 100;
        } else {
            return min($this->valor_descuento, $montoCompra); // No puede ser mayor al monto
        }
    }

    /**
     * Marcar el cupón como usado
     */
    public function marcarComoUsado($ordenId = null, $usuarioId = null, $descuentoAplicado = 0)
    {
        // Incrementar contador de usos
        $this->increment('usos_actuales');

        // Registrar el uso
        CuponUso::create([
            'cupon_id' => $this->id,
            'orden_id' => $ordenId,
            'usuario_id' => $usuarioId,
            'descuento_aplicado' => $descuentoAplicado,
            'usado_en' => Carbon::now()
        ]);

        return true;
    }

    /**
     * Generar código aleatorio único
     */
    public static function generarCodigoUnico($longitud = 8)
    {
        do {
            $codigo = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $longitud));
        } while (self::where('codigo', $codigo)->exists());

        return $codigo;
    }
}
