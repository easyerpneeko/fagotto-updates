<?php

/**
 * ===============================================================
 * SISTEMA DE PRECIOS PERSONALIZADOS POR NEGOCIO
 * Para tabla: pedidofinal_precios
 * ===============================================================
 * 
 * CÓMO FUNCIONA:
 * 1. pedidofinal_precios → Precios base globales
 * 2. pedidofinal_precios_negocio → Precios específicos por app_id
 * 3. Query usa LEFT JOIN + COALESCE para obtener precio correcto
 * 
 * Si existe precio custom → usa ese
 * Si NO existe → usa precio_por_unidad de pedidofinal_precios
 * 
 * ===============================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\CurrentApp;

class PedidoFinalPreciosController extends Controller
{
    /**
     * =======================================
     * MÉTODO PRINCIPAL: Obtener productos
     * =======================================
     * 
     * Este método reemplaza tu endpoint actual de pedidofinal_precios
     * Devuelve productos con el precio correcto según el negocio
     */
    public function getProductosPedidoFinal()
    {
        $appId = CurrentApp::getApp();
        
        $productos = DB::table('pedidofinal_precios as p')
            ->leftJoin('pedidofinal_precios_negocio as ppn', function($join) use ($appId) {
                $join->on('ppn.producto_id', '=', 'p.id')
                     ->where('ppn.app_id', '=', $appId)
                     ->where('ppn.activo', '=', 1);
            })
            ->where('p.activo', 1)
            ->select(
                'p.id',
                'p.producto',
                'p.unidad_venta',
                'p.unidad_medida',
                'p.categoria',
                'p.stock',
                'p.min_stock',
                'p.fecha_actualizacion',
                // ⭐ CLAVE: COALESCE sobrescribe el precio
                DB::raw('COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as precio_por_unidad')
            )
            ->orderBy('p.categoria')
            ->orderBy('p.producto')
            ->get();
        
        return response()->json([
            'success' => true,
            'app_id' => $appId,
            'data' => $productos
        ]);
    }

    /**
     * =======================================
     * Ver productos con info de precios
     * =======================================
     * 
     * Útil para admin: ver precio base vs precio custom
     */
    public function getProductosConDetalle()
    {
        $appId = CurrentApp::getApp();
        
        $productos = DB::table('pedidofinal_precios as p')
            ->leftJoin('pedidofinal_precios_negocio as ppn', function($join) use ($appId) {
                $join->on('ppn.producto_id', '=', 'p.id')
                     ->where('ppn.app_id', '=', $appId)
                     ->where('ppn.activo', '=', 1);
            })
            ->where('p.activo', 1)
            ->select(
                'p.id',
                'p.producto',
                'p.categoria',
                'p.precio_por_unidad as precio_base',
                'ppn.precio_por_unidad as precio_custom',
                DB::raw('COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as precio_final'),
                'ppn.nota as razon_precio_custom'
            )
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $productos
        ]);
    }

    /**
     * =======================================
     * Actualizar precio personalizado
     * =======================================
     */
    public function setPrecioPersonalizado(Request $request)
    {
        $appId = CurrentApp::getApp();
        $productoId = $request->input('producto_id');
        $precio = $request->input('precio_por_unidad');
        $nota = $request->input('nota', null); // Opcional
        
        // Validación
        if (!$productoId || !is_numeric($precio) || $precio < 0) {
            return response()->json([
                'success' => false,
                'error' => 'Datos inválidos'
            ], 400);
        }
        
        // Verificar que el producto existe
        $productoExiste = DB::table('pedidofinal_precios')
            ->where('id', $productoId)
            ->exists();
        
        if (!$productoExiste) {
            return response()->json([
                'success' => false,
                'error' => 'Producto no encontrado'
            ], 404);
        }
        
        // Insertar o actualizar precio personalizado
        DB::table('pedidofinal_precios_negocio')->updateOrInsert(
            [
                'app_id' => $appId,
                'producto_id' => $productoId
            ],
            [
                'precio_por_unidad' => $precio,
                'nota' => $nota,
                'activo' => 1,
                'updated_at' => now()
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Precio personalizado actualizado',
            'app_id' => $appId,
            'producto_id' => $productoId,
            'precio' => $precio
        ]);
    }

    /**
     * =======================================
     * Eliminar precio personalizado
     * =======================================
     * 
     * Vuelve a usar el precio base
     */
    public function eliminarPrecioPersonalizado($productoId)
    {
        $appId = CurrentApp::getApp();
        
        $deleted = DB::table('pedidofinal_precios_negocio')
            ->where('app_id', $appId)
            ->where('producto_id', $productoId)
            ->delete();
        
        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Ahora se usará el precio base del producto'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No había precio personalizado para este producto'
            ], 404);
        }
    }

    /**
     * =======================================
     * Ver precios de un producto en todos los negocios
     * =======================================
     * 
     * Admin: comparar precios entre negocios
     */
    public function getPreciosPorNegocio($productoId)
    {
        $preciosCustom = DB::table('pedidofinal_precios_negocio as ppn')
            ->join('pedidofinal_precios as p', 'p.id', '=', 'ppn.producto_id')
            ->where('ppn.producto_id', $productoId)
            ->where('ppn.activo', 1)
            ->select(
                'ppn.app_id',
                'p.producto',
                'p.precio_por_unidad as precio_base',
                'ppn.precio_por_unidad as precio_custom',
                'ppn.nota'
            )
            ->get();
        
        return response()->json([
            'success' => true,
            'producto_id' => $productoId,
            'precios_por_negocio' => $preciosCustom
        ]);
    }
}

/**
 * ===============================================================
 * RUTAS PARA routes/api.php
 * ===============================================================
 */
/*
// Obtener productos con precios (método principal)
Route::get('pedidofinal/productos', [PedidoFinalPreciosController::class, 'getProductosPedidoFinal']);

// Ver detalle de precios (base vs custom)
Route::get('pedidofinal/productos/detalle', [PedidoFinalPreciosController::class, 'getProductosConDetalle']);

// Actualizar precio personalizado
Route::post('pedidofinal/precio-custom', [PedidoFinalPreciosController::class, 'setPrecioPersonalizado']);

// Eliminar precio personalizado (volver a precio base)
Route::delete('pedidofinal/precio-custom/{producto_id}', [PedidoFinalPreciosController::class, 'eliminarPrecioPersonalizado']);

// Admin: Ver precios de un producto en todos los negocios
Route::get('pedidofinal/precio-comparar/{producto_id}', [PedidoFinalPreciosController::class, 'getPreciosPorNegocio']);
*/

/**
 * ===============================================================
 * EJEMPLO DE USO EN EL FRONTEND (Electron/Vue)
 * ===============================================================
 */
/*
import Connection from '@/helpers/Connection';
import BaseUrl from '@/helpers/baseUrl';

// 1. Obtener productos con precios correctos
async obtenerProductosPedidoFinal() {
    const response = await Connection.request('GET', BaseUrl.getUrl('api/pedidofinal/productos'));
    
    if (response.success) {
        console.log('Productos:', response.data);
        // Cada producto tendrá:
        // {
        //   id: 4,
        //   producto: "Salsa Boloñesa",
        //   precio_por_unidad: 8500.00,  // Ya es el precio correcto para tu negocio
        //   categoria: "salsas",
        //   stock: 52
        // }
        
        this.productos = response.data;
    }
}

// 2. Actualizar precio personalizado
async actualizarPrecioCustom(productoId, nuevoPrecio) {
    const response = await Connection.request('POST', BaseUrl.getUrl('api/pedidofinal/precio-custom'), {
        producto_id: productoId,
        precio_por_unidad: nuevoPrecio,
        nota: 'Ajuste manual por encargado'
    });
    
    if (response.success) {
        this.$awn.success('Precio actualizado');
        this.obtenerProductosPedidoFinal(); // Recargar
    }
}

// 3. Eliminar precio personalizado (volver a precio base)
async eliminarPrecioCustom(productoId) {
    const response = await Connection.request('DELETE', 
        BaseUrl.getUrl(`api/pedidofinal/precio-custom/${productoId}`)
    );
    
    if (response.success) {
        this.$awn.info('Ahora se usa el precio base');
        this.obtenerProductosPedidoFinal(); // Recargar
    }
}
*/

/**
 * ===============================================================
 * MIGRACIÓN DESDE TU CÓDIGO ACTUAL
 * ===============================================================
 * 
 * ANTES (query actual):
 * SELECT * FROM pedidofinal_precios WHERE activo = 1;
 * 
 * DESPUÉS (con precios por negocio):
 * SELECT 
 *     p.*,
 *     COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as precio_por_unidad
 * FROM pedidofinal_precios p
 * LEFT JOIN pedidofinal_precios_negocio ppn 
 *     ON ppn.producto_id = p.id 
 *     AND ppn.app_id = ?
 *     AND ppn.activo = 1
 * WHERE p.activo = 1;
 * 
 * ✅ Esa es la ÚNICA diferencia
 * ✅ Todo lo demás sigue funcionando igual
 * ✅ El precio_por_unidad que recibes ya es el correcto
 */

