<?php

namespace App\Http\Controllers\Controllers_local;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Helpers\CurrentApp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripePaymentController extends Controller
{
    public function __construct()
    {
        // Configurar Stripe con la secret key
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    }

    /**
     * Crear un Payment Intent para iniciar el pago
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'currency' => 'nullable|string',
            ]);

            $amount = $request->amount;
            $currency = $request->currency ?? 'clp'; // Valor por defecto si no viene

            // Stripe requiere el monto en centavos para la mayoría de monedas
            // Para CLP (pesos chilenos), Stripe no usa decimales
            $amountInCents = ($currency === 'clp') ? round($amount) : round($amount * 100);

            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => $currency,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'app_id' => $request->app_id ?? null,
                    'contact_name' => $request->contact_name ?? null,
                    'contact_phone' => $request->contact_phone ?? null,
                    'whatsapp' => $request->whatsapp ?? null,
                ]
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating payment intent: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear intención de pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirmar el estado del pago
     */
    public function confirmPayment(Request $request)
    {
        try {
            $request->validate([
                'payment_intent_id' => 'required|string',
            ]);

            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            return response()->json([
                'success' => true,
                'status' => $paymentIntent->status,
                'payment_intent' => $paymentIntent,
            ]);

        } catch (\Exception $e) {
            Log::error('Error confirming payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook para recibir eventos de Stripe
     */
    public function webhook(Request $request)
    {
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            if ($endpoint_secret) {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sig_header, $endpoint_secret
                );
            } else {
                $event = json_decode($payload);
            }

            // Manejar el evento
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $this->handlePaymentSucceeded($paymentIntent);
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    $this->handlePaymentFailed($paymentIntent);
                    break;

                default:
                    Log::info('Unhandled event type: ' . $event->type);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Manejar pago exitoso
     */
    private function handlePaymentSucceeded($paymentIntent)
    {
        try {
            $metadata = $paymentIntent->metadata;
            $app_id = $metadata->app_id ?? null;

            if ($app_id) {
                $app = \App\Aplication::find($app_id);
                if ($app) {
                    // Actualizar el estado del pedido en la base de datos del local
                    DB::connection($app->alias)
                        ->table('requests')
                        ->where('payment_id', $paymentIntent->id)
                        ->update([
                            'payment_status' => 'paid',
                            'status' => 'aprobado',
                            'updated_at' => now()
                        ]);

                    // Aquí puedes enviar la notificación de WhatsApp
                    $whatsapp = $metadata->whatsapp ?? null;
                    if ($whatsapp) {
                        $this->sendWhatsAppNotification($whatsapp, $paymentIntent);
                    }

                    Log::info('Payment succeeded for payment_id: ' . $paymentIntent->id);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error handling payment succeeded: ' . $e->getMessage());
        }
    }

    /**
     * Manejar pago fallido
     */
    private function handlePaymentFailed($paymentIntent)
    {
        try {
            $metadata = $paymentIntent->metadata;
            $app_id = $metadata->app_id ?? null;

            if ($app_id) {
                $app = \App\Aplication::find($app_id);
                if ($app) {
                    DB::connection($app->alias)
                        ->table('requests')
                        ->where('payment_id', $paymentIntent->id)
                        ->update([
                            'payment_status' => 'failed',
                            'updated_at' => now()
                        ]);

                    Log::info('Payment failed for payment_id: ' . $paymentIntent->id);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error handling payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Enviar notificación por WhatsApp
     * TODO: Implementar con Twilio o API de WhatsApp Business
     */
    private function sendWhatsAppNotification($whatsapp, $paymentIntent)
    {
        try {
            $amount = $paymentIntent->amount / 100; // Convertir de centavos
            $currency = strtoupper($paymentIntent->currency);

            $message = "✅ *Pago Confirmado - Fagotto*\n\n";
            $message .= "Monto: $currency $" . number_format($amount, 0, ',', '.') . "\n";
            $message .= "ID: " . substr($paymentIntent->id, 0, 16) . "...\n";
            $message .= "Estado: Pagado\n\n";
            $message .= "Tu pedido está siendo preparado. ¡Gracias por tu compra!";

            // Por ahora solo registrar en logs
            // TODO: Integrar con API de WhatsApp o Twilio
            Log::info("WhatsApp notification to $whatsapp: $message");

            // Alternativa temporal: Generar link de WhatsApp
            $encodedMessage = urlencode($message);
            $whatsappLink = "https://wa.me/$whatsapp?text=$encodedMessage";
            Log::info("WhatsApp link: $whatsappLink");

        } catch (\Exception $e) {
            Log::error('Error sending WhatsApp notification: ' . $e->getMessage());
        }
    }
}
