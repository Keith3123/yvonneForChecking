<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaluwaganSchedule;

class PaymongoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('🔥 WEBHOOK HIT RAW');

        try {
            // Unwrap event structure
            $eventData       = $request->input('data');
            $eventType       = $eventData['attributes']['type'] ?? null;
            $checkoutSession = $eventData['attributes']['data'] ?? null;

            Log::info('📦 Event Type: ' . $eventType);

            if (!$checkoutSession) {
                Log::error('❌ No checkout session in event');
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $attributes       = $checkoutSession['attributes'] ?? [];
            $checkoutSessionId = $checkoutSession['id'] ?? null;
            $metadata         = $attributes['metadata'] ?? [];

            Log::info('🔑 Session ID: ' . $checkoutSessionId);
            Log::info('📋 Metadata: ', $metadata);

            // =========================
            // GET PAID PAYMENT
            // =========================
            $payments = $attributes['payments'] ?? [];
            $payment  = collect($payments)->first(fn($p) => ($p['attributes']['status'] ?? null) === 'paid');

            if (!$payment) {
                Log::info('⏳ No paid payment yet');
                return response()->json(['message' => 'not paid'], 200);
            }

            $paymentId = $payment['id'] ?? null;
            $source    = $payment['attributes']['source'] ?? [];
            $sourceId  = (is_array($source) && isset($source['id']) && !str_contains($source['id'] ?? '', 'Over 9'))
                ? $source['id']
                : $this->fetchSourceId($paymentId);

            // =========================
            // FIND DB PAYMENT
            // =========================
            $dbPayment = Payment::where('checkout_session_id', $checkoutSessionId)->first();

            if (!$dbPayment) {
                Log::error('❌ DB PAYMENT NOT FOUND', ['session' => $checkoutSessionId]);
                return response()->json(['error' => 'Not found'], 404);
            }

            // Idempotent check
            if ($dbPayment->status === 'approved') {
                Log::info('⚠️ Already approved, skipping');
                return response()->json(['message' => 'already processed'], 200);
            }

            // =========================
            // DETERMINE CONTEXT
            // =========================
            $context = $metadata['context'] ?? 'order';

            Log::info('🎯 Context: ' . $context);

            if ($context === 'paluwagan') {
                return $this->handlePaluwaganPayment(
                    $dbPayment,
                    $metadata,
                    $paymentId,
                    $sourceId,
                    $payment
                );
            }

            // =========================
            // DEFAULT: ORDER PAYMENT
            // =========================
            return $this->handleOrderPayment(
                $dbPayment,
                $metadata,
                $paymentId,
                $sourceId,
                $payment
            );

        } catch (\Throwable $e) {
            Log::error('💥 WEBHOOK CRASH', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);
            return response()->json(['error' => 'fail'], 500);
        }
    }

    // ==============================
    // HANDLE ORDER PAYMENT
    // ==============================
    private function handleOrderPayment($dbPayment, $metadata, $paymentId, $sourceId, $payment)
    {
        $orderId = $metadata['order_id'] ?? null;

        if (!$orderId) {
            Log::error('❌ ORDER ID MISSING');
            return response()->json(['error' => 'Missing order_id'], 400);
        }

        $referenceNumber = $dbPayment->reference_number
            ?: 'ORD-' . $orderId . '-' . strtoupper(Str::random(6));

        $dbPayment->update([
            'status'              => 'approved',
            'paymongo_payment_id' => $paymentId,
            'paymongo_source_id'  => $sourceId,
            'reference_number'    => $referenceNumber,
            'meta'                => json_encode($payment),
        ]);

        Order::where('orderID', $orderId)->update([
            'paymentStatus' => 'Paid'
        ]);

        Log::info("✅ ORDER #{$orderId} PAID");

        return response()->json(['message' => 'ok'], 200);
    }

    // ==============================
    // HANDLE PALUWAGAN PAYMENT
    // ==============================
    private function handlePaluwaganPayment($dbPayment, $metadata, $paymentId, $sourceId, $payment)
    {
        $entryID = $metadata['entry_id'] ?? null;
        $amount  = floatval($metadata['amount'] ?? $dbPayment->amount);

        if (!$entryID) {
            Log::error('❌ ENTRY ID MISSING');
            return response()->json(['error' => 'Missing entry_id'], 400);
        }

        Log::info("💰 PALUWAGAN PAYMENT - Entry #{$entryID}, Amount: {$amount}");

        $referenceNumber = $dbPayment->reference_number
            ?: 'PAL-' . $entryID . '-' . strtoupper(Str::random(6));

        // Update the payment record
        $dbPayment->update([
            'status'              => 'approved',
            'paymongo_payment_id' => $paymentId,
            'paymongo_source_id'  => $sourceId,
            'reference_number'    => $referenceNumber,
            'meta'                => json_encode($payment),
        ]);

        // =========================
        // DISTRIBUTE AMOUNT TO SCHEDULES
        // =========================
        $schedules = PaluwaganSchedule::where('paluwaganEntryID', $entryID)
            ->whereIn('status', ['pending', 'late'])
            ->orderBy('dueDate')
            ->get();

        $remaining = $amount;

        foreach ($schedules as $sched) {
            if ($remaining <= 0) break;

            $due     = floatval($sched->amountDue) - floatval($sched->amountPaid);
            if ($due <= 0) continue;

            $paying  = min($due, $remaining);
            $remaining -= $paying;

            $sched->amountPaid = floatval($sched->amountPaid) + $paying;

            if ($sched->amountPaid >= $sched->amountDue) {
                $sched->status = 'paid';
            }

            $sched->save();

            Log::info("✅ Schedule #{$sched->scheduleID} updated: paid ₱{$paying}");
        }

        Log::info("✅ PALUWAGAN ENTRY #{$entryID} PAYMENT PROCESSED");

        return response()->json(['message' => 'ok'], 200);
    }

    // ==============================
    // FETCH SOURCE ID (FALLBACK)
    // ==============================
    private function fetchSourceId(?string $paymentId): ?string
    {
        if (!$paymentId) return null;

        try {
            $response = \Illuminate\Support\Facades\Http::withBasicAuth(
                config('services.paymongo.secret'), ''
            )->get("https://api.paymongo.com/v1/payments/{$paymentId}");

            if ($response->successful()) {
                return $response->json()['data']['attributes']['source']['id'] ?? null;
            }
        } catch (\Throwable $e) {
            Log::warning('⚠️ Could not fetch source ID: ' . $e->getMessage());
        }

        return null;
    }
}