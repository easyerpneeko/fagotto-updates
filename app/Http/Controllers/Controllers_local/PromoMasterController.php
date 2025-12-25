<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PromoPasta;
use App\PromoSalsa;

class PromoMasterController extends Controller
{
    /**
     * Obtener tipos de pasta desde la base de datos maestra
     */
    public function getPastaTypes()
    {
        try {
            $pastas = PromoPasta::getActivas();

            return response()->json([
                'success' => true,
                'data' => $pastas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de pasta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipos de salsa desde la base de datos maestra
     */
    public function getSalsaTypes()
    {
        try {
            $salsas = PromoSalsa::getActivas();

            return response()->json([
                'success' => true,
                'data' => $salsas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de salsa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los datos de promo (pastas y salsas)
     */
    public function getPromoData()
    {
        try {
            $pastas = PromoPasta::getActivas();
            $salsas = PromoSalsa::getActivas();

            return response()->json([
                'success' => true,
                'data' => [
                    'pastas' => $pastas,
                    'salsas' => $salsas
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos de promo: ' . $e->getMessage()
            ], 500);
        }
    }
}
