<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GelateriaConfigController extends Controller
{
    /**
     * Mostrar la vista de configuración de Gelateria
     */
    public function index()
    {
        $config = DB::table('gelateria_config')->first();
        
        if (!$config) {
            // Crear registro por defecto si no existe
            DB::table('gelateria_config')->insert([
                'is_active' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $config = DB::table('gelateria_config')->first();
        }
        
        return view('admin.gelateria.config', compact('config'));
    }
    
    /**
     * Actualizar el estado del módulo Gelateria
     */
    public function update(Request $request)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);
        
        DB::table('gelateria_config')
            ->where('id', 1)
            ->update([
                'is_active' => $request->is_active,
                'updated_at' => now()
            ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada correctamente',
            'is_active' => $request->is_active
        ]);
    }
    
    /**
     * Obtener el estado actual del módulo (API para el frontend)
     */
    public function getStatus()
    {
        $config = DB::table('gelateria_config')->first();
        
        return response()->json([
            'success' => true,
            'is_active' => $config ? (bool)$config->is_active : false
        ]);
    }
}
