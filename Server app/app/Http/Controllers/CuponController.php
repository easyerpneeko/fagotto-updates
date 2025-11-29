<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Cupon;
use Carbon\Carbon;

class CuponController extends Controller
{
    /**
     * Listar todos los cupones (solo admin)
     */
    public function index(Request $request)
    {
        $cupones = Cupon::orderBy('created_at', 'desc')->paginate(50);
        return response()->json($cupones, 200);
    }

    /**
     * Crear nuevos cupones en lote
     */
    public function crearLote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:1|max:500',
            'tipo_descuento' => 'required|in:porcentaje,monto_fijo',
            'valor_descuento' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:255',
            'usos_maximos' => 'nullable|integer|min:1',
            'fecha_inicio' => 'nullable|date',
            'fecha_expiracion' => 'nullable|date|after_or_equal:fecha_inicio',
            'monto_minimo' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $cantidad = $request->input('cantidad');
        $cuponesCreados = [];
        $usuario = Auth::user();

        for ($i = 0; $i < $cantidad; $i++) {
            $cupon = Cupon::create([
                'codigo' => Cupon::generarCodigoUnico(),
                'descripcion' => $request->input('descripcion'),
                'tipo_descuento' => $request->input('tipo_descuento'),
                'valor_descuento' => $request->input('valor_descuento'),
                'usos_maximos' => $request->input('usos_maximos', 1),
                'fecha_inicio' => $request->input('fecha_inicio'),
                'fecha_expiracion' => $request->input('fecha_expiracion'),
                'monto_minimo' => $request->input('monto_minimo'),
                'activo' => true,
                'creado_por' => $usuario ? $usuario->id : null
            ]);

            $cuponesCreados[] = $cupon->codigo;
        }

        return response()->json([
            'mensaje' => "Se crearon {$cantidad} cupones exitosamente",
            'cupones' => $cuponesCreados
        ], 201);
    }

    /**
     * Validar un cupón por código
     */
    public function validar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string',
            'monto_compra' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $codigo = strtoupper(trim($request->input('codigo')));
        $montoCompra = $request->input('monto_compra', 0);

        $cupon = Cupon::where('codigo', $codigo)->first();

        if (!$cupon) {
            return response()->json([
                'valido' => false,
                'mensaje' => 'Cupón no encontrado'
            ], 404);
        }

        // ✅ Verificar si ya fue usado
        if ($cupon->usado == 1) {
            $fechaUso = $cupon->usado_en ? Carbon::parse($cupon->usado_en)->format('d/m/Y H:i') : 'Fecha desconocida';
            $sucursal = $cupon->sucursal_nombre ?: 'Sucursal no registrada';
            $usuario = $cupon->usuario_nombre ?: 'Usuario no registrado';
            $producto = $cupon->producto_nombre ?: 'Producto no especificado';
            
            return response()->json([
                'valido' => false,
                'mensaje' => "Este cupón ya fue utilizado el {$fechaUso} en {$sucursal} por {$usuario}. Producto: {$producto}"
            ], 400);
        }

        $validacion = $cupon->esValido($montoCompra);

        if (!$validacion['valido']) {
            return response()->json($validacion, 400);
        }

        $descuento = $cupon->calcularDescuento($montoCompra);

        return response()->json([
            'valido' => true,
            'mensaje' => 'Cupón válido',
            'cupon' => [
                'id' => $cupon->id,
                'codigo' => $cupon->codigo,
                'descripcion' => $cupon->descripcion,
                'tipo_descuento' => $cupon->tipo_descuento,
                'valor_descuento' => $cupon->valor_descuento,
                'descuento_calculado' => $descuento,
                'usos_disponibles' => $cupon->usos_maximos - $cupon->usos_actuales
            ]
        ], 200);
    }

    /**
     * Aplicar (usar) un cupón - Registrar uso con detalles del producto
     */
    public function aplicar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string',
            'cupon_id' => 'nullable|integer',
            'orden_id' => 'nullable|integer',
            'sucursal_id' => 'nullable|integer',
            'sucursal_nombre' => 'nullable|string',
            'usuario_id' => 'nullable|integer',
            'usuario_nombre' => 'nullable|string',
            'categoria_id' => 'nullable|integer',
            'categoria_nombre' => 'nullable|string',
            'producto_id' => 'nullable|integer',
            'producto_nombre' => 'nullable|string',
            'precio_original' => 'nullable|numeric',
            'precio_con_cupon' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        $codigo = strtoupper(trim($request->input('codigo')));

        $cupon = Cupon::where('codigo', $codigo)->first();

        if (!$cupon) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Cupón no encontrado'
            ], 404);
        }

        // Si ya fue usado, retornar error
        if ($cupon->usado == 1) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Este cupón ya fue utilizado'
            ], 400);
        }

        // Si está inactivo, retornar error
        if ($cupon->activo == 0) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Este cupón está inactivo'
            ], 400);
        }

        // Actualizar cupón con los datos de uso
        $cupon->usado = 1;
        $cupon->usado_en = now();
        $cupon->orden_id = $request->input('orden_id');
        $cupon->sucursal_id = $request->input('sucursal_id');
        $cupon->sucursal_nombre = $request->input('sucursal_nombre');
        $cupon->usuario_id = $request->input('usuario_id');
        $cupon->usuario_nombre = $request->input('usuario_nombre');
        $cupon->categoria_id = $request->input('categoria_id');
        $cupon->categoria_nombre = $request->input('categoria_nombre');
        $cupon->producto_id = $request->input('producto_id');
        $cupon->producto_nombre = $request->input('producto_nombre');
        $cupon->precio_original = $request->input('precio_original');
        $cupon->precio_con_cupon = $request->input('precio_con_cupon');
        $cupon->save();

        return response()->json([
            'success' => true,
            'mensaje' => 'Cupón aplicado y registrado exitosamente',
            'cupon' => [
                'codigo' => $cupon->codigo,
                'usado_en' => $cupon->usado_en,
                'orden_id' => $cupon->orden_id,
                'producto' => $cupon->producto_nombre,
                'sucursal_id' => $cupon->sucursal_id
            ]
        ], 200);
    }

    /**
     * Desactivar un cupón
     */
    public function desactivar($id)
    {
        $cupon = Cupon::find($id);

        if (!$cupon) {
            return response()->json(['mensaje' => 'Cupón no encontrado'], 404);
        }

        $cupon->activo = false;
        $cupon->save();

        return response()->json(['mensaje' => 'Cupón desactivado'], 200);
    }

    /**
     * Obtener estadísticas de cupones
     */
    public function estadisticas()
    {
        $total = Cupon::count();
        $activos = Cupon::where('activo', true)->count();
        $usados = Cupon::where('usos_actuales', '>=', Cupon::raw('usos_maximos'))->count();
        $disponibles = Cupon::where('activo', true)
            ->where('usos_actuales', '<', Cupon::raw('usos_maximos'))
            ->whereNull('fecha_expiracion')
            ->orWhere('fecha_expiracion', '>=', Carbon::now())
            ->count();

        return response()->json([
            'total' => $total,
            'activos' => $activos,
            'usados_completamente' => $usados,
            'disponibles' => $disponibles
        ], 200);
    }
}
