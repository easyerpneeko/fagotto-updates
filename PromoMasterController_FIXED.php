<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PromoMasterController extends Controller
{
    /**
     * Obtener tipos de pasta desde la base de datos maestra
     */
    public function getPastaTypes()
    {
        try {
            // Conectar a la base de datos maestra easyerp
            $pastas = DB::connection('easyerp_master')->table('promo_pasta_types')
                ->where('active', 1)
                ->orderBy('order_display')
                ->get();

            $response = response()->json([
                'success' => true,
                'data' => $pastas
            ]);
            
            $response->header('Access-Control-Allow-Origin', '*');
            return $response;
        } catch (\Exception $e) {
            $response = response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de pasta: ' . $e->getMessage()
            ], 500);
            
            $response->header('Access-Control-Allow-Origin', '*');
            return $response;
        }
    }

    /**
     * Obtener tipos de salsa desde la base de datos maestra
     */
    public function getSalsaTypes()
    {
        try {
            // Conectar a la base de datos maestra easyerp
            $salsas = DB::connection('easyerp_master')->table('promo_salsa_types')
                ->where('active', 1)
                ->orderBy('order_display')
                ->get();

            $response = response()->json([
                'success' => true,
                'data' => $salsas
            ]);
            
            $response->header('Access-Control-Allow-Origin', '*');
            return $response;
        } catch (\Exception $e) {
            $response = response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de salsa: ' . $e->getMessage()
            ], 500);
            
            $response->header('Access-Control-Allow-Origin', '*');
            return $response;
        }
    }

    /**
     * Obtener todos los datos de promo (pastas y salsas)
     */
    public function getPromoData()
    {
        try {
            // Conectar a la base de datos maestra easyerp
            $pastas = DB::connection('easyerp_master')->table('promo_pasta_types')
                ->where('active', 1)
                ->orderBy('order_display')
                ->get();

            $salsas = DB::connection('easyerp_master')->table('promo_salsa_types')
                ->where('active', 1)
                ->orderBy('order_display')
                ->get();

            $response = response()->json([
                'success' => true,
                'data' => [
                    'pastas' => $pastas,
                    'salsas' => $salsas
                ]
            ]);
            
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            
            return $response;
        } catch (\Exception $e) {
            $response = response()->json([
                'success' => false,
                'message' => 'Error al obtener datos de promo: ' . $e->getMessage()
            ], 500);
            
            $response->header('Access-Control-Allow-Origin', '*');
            return $response;
        }
    }
}
