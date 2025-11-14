<?php

namespace App\Http\Controllers\Controllers_local;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Models
use App\Client;
use App\Sell;
use App\Folio;

class FranquiciadosController extends Controller
{
    /**
     * Obtener TODAS las facturas del negocio desde tabla requests
     */
    public function obtenerTodasLasFacturas(Request $request)
    {
        try {
            // Traer todas las facturas de la tabla requests
            $facturas = DB::table('requests')
                ->where('trash', 0)
                ->where('folio', '!=', '')
                ->where('folio', '!=', null)
                ->select([
                    'id',
                    'folio',
                    'total',
                    'created_at as fecha',
                    'rut_cliente',
                    'razon_social_cliente',
                    'giro_cliente',
                    'comuna_cliente',
                    'direccion_cliente',
                    'email_cliente',
                    'telefono_cliente',
                    'state as estado'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $facturas,
                'total' => $facturas->count(),
                'message' => 'Facturas obtenidas correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facturas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar facturas por filtros
     */
    public function buscarFacturas(Request $request)
    {
        try {
            $folio = $request->input('folio');
            $rut = $request->input('rut');
            $razonSocial = $request->input('razon_social');
            $fechaDesde = $request->input('fecha_desde');
            $fechaHasta = $request->input('fecha_hasta');

            $query = DB::table('requests')
                ->where('trash', 0)
                ->where('folio', '!=', '')
                ->where('folio', '!=', null);

            if ($folio) {
                $query->where('folio', 'like', '%' . $folio . '%');
            }

            if ($rut) {
                $query->where('rut_cliente', 'like', '%' . $rut . '%');
            }

            if ($razonSocial) {
                $query->where('razon_social_cliente', 'like', '%' . $razonSocial . '%');
            }

            if ($fechaDesde) {
                $query->whereDate('created_at', '>=', $fechaDesde);
            }

            if ($fechaHasta) {
                $query->whereDate('created_at', '<=', $fechaHasta);
            }

            $facturas = $query
                ->select([
                    'id',
                    'folio',
                    'total',
                    'created_at as fecha',
                    'rut_cliente',
                    'razon_social_cliente',
                    'giro_cliente',
                    'comuna_cliente',
                    'direccion_cliente',
                    'email_cliente',
                    'telefono_cliente',
                    'state as estado'
                ])
                ->orderBy('created_at', 'desc')
                ->limit(100) // Limitar a 100 resultados
                ->get();

            return response()->json([
                'success' => true,
                'data' => $facturas,
                'total' => $facturas->count(),
                'message' => 'Búsqueda completada'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar facturas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener PDF de una factura específica
     */
    public function obtenerPDFFactura(Request $request, $facturaId)
    {
        try {
            // Buscar la venta
            $sell = Sell::find($facturaId);
            
            if (!$sell) {
                return response()->json([
                    'success' => false,
                    'message' => 'Factura no encontrada'
                ], 404);
            }

            // Verificar que tenga folio de factura
            $folio = Folio::where('sell_id', $sell->id)
                          ->where('type', 'factura')
                          ->where('trash', 0)
                          ->first();

            if (!$folio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta venta no tiene factura emitida'
                ], 404);
            }

            // Obtener el cliente
            $client = Client::find($sell->client);
            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Preparar datos para generar PDF
            $sell->rut = $client->rut;
            $sell->city = $client->city;
            $sell->comuna = $client->comuna;
            $sell->razon_social = $client->razon_social;
            $sell->direction = $client->direction;
            $sell->giro = $client->giro;

            // Usar el servicio SII para generar el PDF
            $pdfData = \App\Http\Controllers\ServicesSII::imprimirFactura($sell, $folio);

            if ($pdfData && isset($pdfData['pdf_base64'])) {
                return response()->json([
                    'success' => true,
                    'data' => $pdfData['pdf_base64'],
                    'message' => 'PDF generado correctamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al generar el PDF'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
