<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\FranchiseUberCredential;

class FranchiseUberConfigController extends Controller
{
    /**
     * Obtener configuración de franquicia
     */
    public function getConfig($franchiseId)
    {
        $credential = FranchiseUberCredential::where('franchise_id', $franchiseId)->first();
        
        if (!$credential) {
            return response()->json([
                'success' => false,
                'message' => 'Franquicia no encontrada',
                'data' => null
            ], 404);
        }

        // No exponer credenciales sensibles en la respuesta
        return response()->json([
            'success' => true,
            'data' => [
                'franchise_id' => $credential->franchise_id,
                'franchise_name' => $credential->franchise_name,
                'is_configured' => $credential->is_configured,
                'is_active' => $credential->is_active,
                'prep_time_minutes' => $credential->prep_time_minutes,
                'auto_accept_orders' => $credential->auto_accept_orders,
                'last_test_status' => $credential->getLastTestStatus(),
                'last_test_result' => $credential->last_test_result,
                'has_credentials' => $credential->hasCompleteCredentials(),
                'contact_email' => $credential->contact_email,
                'contact_phone' => $credential->contact_phone,
                'last_sync_at' => $credential->last_sync_at
            ]
        ]);
    }

    /**
     * Guardar/actualizar configuración de franquicia
     */
    public function saveConfig(Request $request, $franchiseId)
    {
        $validator = Validator::make($request->all(), [
            'franchise_name' => 'required|string|max:255',
            'uber_client_id' => 'required|string',
            'uber_client_secret' => 'required|string',
            'uber_store_id' => 'required|string',
            'prep_time_minutes' => 'nullable|integer|min:5|max:120',
            'auto_accept_orders' => 'nullable|boolean',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $credential = FranchiseUberCredential::updateOrCreate(
                ['franchise_id' => $franchiseId],
                [
                    'franchise_name' => $request->franchise_name,
                    'uber_client_id' => $request->uber_client_id,
                    'uber_client_secret' => $request->uber_client_secret,
                    'uber_store_id' => $request->uber_store_id,
                    'webhook_url' => env('UBER_EATS_WEBHOOK_URL'),
                    'prep_time_minutes' => $request->prep_time_minutes ?? 15,
                    'auto_accept_orders' => $request->auto_accept_orders ?? false,
                    'contact_email' => $request->contact_email,
                    'contact_phone' => $request->contact_phone,
                    'is_active' => true,
                    'is_configured' => false // Se marcará como configurada después de probar
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Configuración guardada exitosamente',
                'data' => [
                    'franchise_id' => $credential->franchise_id,
                    'is_configured' => $credential->is_configured
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error guardando configuración Uber Eats', [
                'franchise_id' => $franchiseId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error guardando configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Probar conexión con Uber Eats API
     */
    public function testConnection($franchiseId)
    {
        $credential = FranchiseUberCredential::where('franchise_id', $franchiseId)->first();
        
        if (!$credential || !$credential->hasCompleteCredentials()) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incompletas'
            ], 400);
        }

        try {
            // Intentar obtener access token
            $tokenResponse = Http::asForm()->post('https://login.uber.com/oauth/v2/token', [
                'client_id' => $credential->uber_client_id,
                'client_secret' => $credential->uber_client_secret,
                'grant_type' => 'client_credentials',
                'scope' => env('UBER_EATS_SCOPE', 'eats.store eats.store.status.write eats.order eats.store.orders.read'),
            ]);

            if ($tokenResponse->successful()) {
                $tokenData = $tokenResponse->json();
                $accessToken = $tokenData['access_token'];

                // Probar obtener información de la tienda
                $storeResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json'
                ])->get("https://api.uber.com/v1/eats/stores/{$credential->uber_store_id}");

                if ($storeResponse->successful()) {
                    $storeData = $storeResponse->json();
                    
                    // Marcar como configurada exitosamente
                    $credential->markAsConfigured();
                    $credential->saveTestResult(true, 'Conexión exitosa', [
                        'store_name' => $storeData['name'] ?? 'Tienda',
                        'store_status' => $storeData['status'] ?? 'unknown'
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Conexión exitosa con Uber Eats',
                        'data' => [
                            'store_name' => $storeData['name'] ?? null,
                            'store_status' => $storeData['status'] ?? null,
                            'is_configured' => true
                        ]
                    ]);
                } else {
                    throw new \Exception('Error consultando información de la tienda: ' . $storeResponse->body());
                }
            } else {
                throw new \Exception('Error obteniendo access token: ' . $tokenResponse->body());
            }

        } catch (\Exception $e) {
            $errorMessage = 'Error de conexión: ' . $e->getMessage();
            
            $credential->saveTestResult(false, $errorMessage);

            Log::error('Error probando conexión Uber Eats', [
                'franchise_id' => $franchiseId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 400);
        }
    }

    /**
     * Listar todas las franquicias con su estado
     */
    public function listFranchises()
    {
        $franchises = FranchiseUberCredential::select([
            'franchise_id',
            'franchise_name',
            'is_configured',
            'is_active',
            'last_test_result',
            'last_sync_at',
            'contact_email',
            'contact_phone'
        ])->get()->map(function ($franchise) {
            return [
                'franchise_id' => $franchise->franchise_id,
                'franchise_name' => $franchise->franchise_name,
                'is_configured' => $franchise->is_configured,
                'is_active' => $franchise->is_active,
                'status' => $franchise->getLastTestStatus(),
                'has_credentials' => $franchise->hasCompleteCredentials(),
                'last_sync_at' => $franchise->last_sync_at,
                'contact_email' => $franchise->contact_email,
                'contact_phone' => $franchise->contact_phone
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $franchises
        ]);
    }

    /**
     * Activar/desactivar franquicia
     */
    public function toggleActive($franchiseId)
    {
        $credential = FranchiseUberCredential::where('franchise_id', $franchiseId)->first();
        
        if (!$credential) {
            return response()->json([
                'success' => false,
                'message' => 'Franquicia no encontrada'
            ], 404);
        }

        $credential->update(['is_active' => !$credential->is_active]);

        return response()->json([
            'success' => true,
            'message' => $credential->is_active ? 'Franquicia activada' : 'Franquicia desactivada',
            'data' => [
                'franchise_id' => $credential->franchise_id,
                'is_active' => $credential->is_active
            ]
        ]);
    }
}
