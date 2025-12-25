<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\models_local\TotemProduct;
use App\models_local\TotemCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TotemController extends Controller
{
    /**
     * Obtener todas las categorías activas
     */
    public function getCategorias()
    {
        try {
            $categorias = TotemCategory::activas()->ordenadas()->get();

            return response()->json([
                'success' => true,
                'data' => $categorias
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categorías: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los productos activos con sus categorías
     */
    public function getProductos()
    {
        try {
            $productos = TotemProduct::with('categoria')
                ->activos()
                ->ordenados()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $productos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los datos del totem (categorías y productos)
     */
    public function getTotemData()
    {
        try {
            $categorias = TotemCategory::activas()->ordenadas()->get();
            $productos = TotemProduct::with('categoria')->activos()->ordenados()->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'categorias' => $categorias,
                    'productos' => $productos
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del totem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo producto
     */
    public function crearProducto(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'precio' => 'required|numeric|min:0',
                'categoria_id' => 'required|exists:totem_categorias,id',
                'descripcion' => 'nullable|string',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'orden' => 'nullable|integer',
                'activo' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['nombre', 'precio', 'categoria_id', 'descripcion', 'orden', 'activo']);

            // Subir imagen si existe
            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
                $path = $imagen->storeAs('public/totem', $nombreImagen);
                $data['imagen'] = Storage::url($path);
            }

            $producto = TotemProduct::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'data' => $producto->load('categoria')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar producto
     */
    public function actualizarProducto(Request $request, $id)
    {
        try {
            $producto = TotemProduct::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|required|string|max:255',
                'precio' => 'sometimes|required|numeric|min:0',
                'categoria_id' => 'sometimes|required|exists:totem_categorias,id',
                'descripcion' => 'nullable|string',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'orden' => 'nullable|integer',
                'activo' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['nombre', 'precio', 'categoria_id', 'descripcion', 'orden', 'activo']);

            // Subir nueva imagen si existe
            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior si existe
                if ($producto->imagen) {
                    $oldPath = str_replace('/storage', 'public', $producto->imagen);
                    Storage::delete($oldPath);
                }

                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
                $path = $imagen->storeAs('public/totem', $nombreImagen);
                $data['imagen'] = Storage::url($path);
            }

            $producto->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'data' => $producto->load('categoria')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar producto
     */
    public function eliminarProducto($id)
    {
        try {
            $producto = TotemProduct::findOrFail($id);

            // Eliminar imagen si existe
            if ($producto->imagen) {
                $oldPath = str_replace('/storage', 'public', $producto->imagen);
                Storage::delete($oldPath);
            }

            $producto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
            ], 500);
        }
    }
}
