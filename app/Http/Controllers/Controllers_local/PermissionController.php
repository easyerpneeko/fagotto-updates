<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class PermissionController extends Controller
{
    /**
     * Añadir un nuevo privilegio a un módulo específico
     */
    public function addPermissionToModule(Request $request)
    {
        try {
            $request->validate([
                'module_keyname' => 'required|string',
                'permission_key' => 'required|string',
                'permission_description' => 'required|string'
            ]);

            $moduleKeyname = $request->module_keyname;
            $permissionKey = $request->permission_key;
            $permissionDescription = $request->permission_description;

            // Verificar que el módulo existe
            $module = DB::table('modules')->where('keyname', $moduleKeyname)->first();
            
            if (!$module) {
                return response()->json(['error' => 'Módulo no encontrado'], 404);
            }

            // Obtener permisos actuales
            $currentPermissions = json_decode($module->permissions, true);
            
            // Añadir el nuevo permiso
            $currentPermissions[$permissionKey] = $permissionDescription;

            // Actualizar en la base de datos
            DB::table('modules')
                ->where('keyname', $moduleKeyname)
                ->update(['permissions' => json_encode($currentPermissions)]);

            return response()->json([
                'success' => true, 
                'message' => 'Privilegio añadido exitosamente',
                'new_permission' => [
                    'key' => $permissionKey,
                    'description' => $permissionDescription
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al añadir privilegio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todos los permisos de todos los módulos
     */
    public function listAllPermissions()
    {
        try {
            $modules = DB::table('modules')->select('keyname', 'name', 'permissions')->get();
            
            $allPermissions = [];
            
            foreach ($modules as $module) {
                $permissions = json_decode($module->permissions, true);
                $allPermissions[$module->keyname] = [
                    'module_name' => $module->name,
                    'permissions' => $permissions ?: []
                ];
            }

            return response()->json([
                'success' => true,
                'permissions' => $allPermissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener permisos: ' . $e->getMessage()
            ], 500);
        }
    }
}
