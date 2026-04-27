<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Payment;

class PaymongoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('🔥 WEBHOOK HIT RAW');

        try {
            // ====================================
            // ✅ FIX: UNWRAP EVENT STRUCTURE
            // PayMongo sends: data.attributes.data (the actual checkout session)
            // ====================================
            $eventData = $request->input('data');
            $eventType = $eventData['attributes']['type'] ?? null;

            Log::info('📦 Event Type: ' . $eventType);

            // The ACTUAL checkout session is INSIDE data.attributes.data
            $checkoutSession = $eventData['attributes']['data'] ?? null;

            if (!$checkoutSession) {
                Log::error('❌ No checkout session in event');
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $attributes = $checkoutSession['attributes'] ?? [];
            $checkoutSessionId = $checkoutSession['id'] ?? null;

            Log::info('🔑 Checkout Session ID: ' . $checkoutSessionId);

            // =========================
            // GET PAID PAYMENT
            // =========================
            $payments = $attributes['payments'] ?? [];

            Log::info('💳 Payments count: ' . count($payments));

            $payment = collect($payments)->first(function ($p) {
                $status = $p['attributes']['status'] ?? null;
                Log::info('💳 Payment status: ' . $status);
                return $status === 'paid';
            });

            if (!$payment) {
                Log::info('⏳ No paid payment yet');
                return response()->json(['message' => 'not paid'], 200);
            }

            Log::info('✅ Found paid payment: ' . ($payment['id'] ?? 'unknown'));

            // =========================
            // ORDER ID
            // =========================
            $orderId = $attributes['metadata']['order_id']
                ?? $payment['attributes']['metadata']['order_id']
                ?? null;

            if (!$orderId) {
                Log::error('❌ ORDER ID MISSING');
                return response()->json(['error' => 'Missing order_id'], 400);
            }

            Log::info('📋 Order ID: ' . $orderId);

            // =========================
            // EXTRACT PAYMENT DATA
            // =========================
            $paymentId = $payment['id'] ?? null;

            // ✅ FIX: Handle "Over 9 levels deep" issue
            $source = $payment['attributes']['source'] ?? [];
            $sourceId = null;

            if (is_array($source) && isset($source['id']) && $source['id'] !== 'Over 9 levels deep, aborting normalization') {
                $sourceId = $source['id'];
            }

            // If source is truncated, try to get from payment_intent
            if (!$sourceId) {
                // Fetch from PayMongo API directly
                $sourceId = $this->fetchSourceId($paymentId);
            }

            Log::info('💰 Payment ID: ' . $paymentId);
            Log::info('🔗 Source ID: ' . ($sourceId ?? 'null'));

            // =========================
            // FIND DB PAYMENT
            // =========================
            $dbPayment = Payment::where('checkout_session_id', $checkoutSessionId)->first();

            if (!$dbPayment) {
                // Fallback: find by orderID
                $dbPayment = Payment::where('orderID', $orderId)
                    ->where('method', 'GCASH')
                    ->where('status', 'pending')
                    ->first();

                Log::info('🔄 Fallback search by orderID: ' . ($dbPayment ? 'FOUND' : 'NOT FOUND'));
            }

            if (!$dbPayment) {
                Log::error('❌ DB PAYMENT NOT FOUND', [
                    'session' => $checkoutSessionId,
                    'orderID' => $orderId,
                ]);
                return response()->json(['error' => 'Not found'], 404);
            }

            Log::info('✅ DB Payment found: paymentID ' . $dbPayment->paymentID);

            // =========================
            // IDEMPOTENT CHECK
            // =========================
            if ($dbPayment->status === 'approved') {
                Log::info('⚠️ Already approved, skipping');
                return response()->json(['message' => 'already processed'], 200);
            }

            // =========================
            // GENERATE REF NUMBER
            // =========================
            $referenceNumber = $dbPayment->reference_number
                ?: 'ORD-' . $orderId . '-' . strtoupper(Str::random(6));

            // =========================
            // UPDATE PAYMENT
            // =========================
            $dbPayment->update([
                'status'              => 'approved',
                'paymongo_payment_id' => $paymentId,
                'paymongo_source_id'  => $sourceId,
                'reference_number'    => $referenceNumber,
                'meta'                => json_encode($payment),
            ]);

            Log::info('✅ Payment updated in DB');

            // =========================
            // UPDATE ORDER
            // =========================
            Order::where('orderID', $orderId)->update([
                'paymentStatus' => 'Paid'
            ]);

            Log::info("✅ ORDER #{$orderId} MARKED AS PAID");

            return response()->json(['message' => 'ok'], 200);

        } catch (\Throwable $e) {
            Log::error('💥 WEBHOOK CRASH', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);
            return response()->json(['error' => 'fail'], 500);
        }
    }

    /**
     * Fetch source ID from PayMongo API (fallback for truncated data)
     */
    private function fetchSourceId(?string $paymentId): ?string
    {
        if (!$paymentId) return null;

        try {
            $response = \Illuminate\Support\Facades\Http::withBasicAuth(
                config('services.paymongo.secret'), ''
            )->get("https://api.paymongo.com/v1/payments/{$paymentId}");

            if ($response->successful()) {
                $data = $response->json();
                return $data['data']['attributes']['source']['id'] ?? null;
            }
        } catch (\Throwable $e) {
            Log::warning('⚠️ Could not fetch source ID: ' . $e->getMessage());
        }

        return null;
    }
}