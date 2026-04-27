<?php

namespace App\Http\Controllers;

use App\Services\PaluwaganService;
use App\Models\PaluwaganEntry;
use App\Models\PaluwaganSchedule;
use App\Models\Payment;  
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; 
use Illuminate\Http\Request;

class PaluwaganPageController extends Controller
{
    private $paluwaganService;

    public function __construct(PaluwaganService $paluwaganService)
    {
        $this->paluwaganService = $paluwaganService;
    }

    public function index()
    {
        $customerID = session('logged_in_user.customerID');

        if (!$customerID) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to access Paluwagan.');
        }

        $entries = PaluwaganEntry::with(['package', 'schedules.payment'])
            ->where('customerID', $customerID)
            ->get();

        return view('user.PaluwaganPage', compact('entries'));
    }

    public function join(Request $request)
    {
        $request->validate([
            'packageID' => 'required|integer',
            'startMonth' => 'required|integer|min:1|max:12'
        ]);

        $customerID = session('logged_in_user.customerID');

        if (!$customerID) {
            return response()->json(['error' => 'You must be logged in to join paluwagan.'], 401);
        }

        try {
            $this->paluwaganService->joinPaluwagan(
            $customerID,
            $request->packageID,
            $request->startMonth
        );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }

        return response()->json(['success' => true, 'message' => 'Successfully joined the paluwagan!']);
    }

    public function viewSchedule($entryID)
{
    $entry = PaluwaganEntry::with(['schedules.payment', 'package'])
        ->find($entryID);

    if (!$entry) {
        return response()->json([
            'status' => 'NOT_FOUND',
            'message' => 'Entry not found'
        ], 404);
    }

    $schedules = $entry->schedules ?? collect();

    // If no schedules, return safe structure (NO JS crash)
    if ($schedules->isEmpty()) {
        return response()->json([
            'entry' => [
                'entryID' => $entry->paluwaganEntryID,
                'name' => $entry->package->packageName ?? 'N/A',
                'startDate' => \Carbon\Carbon::create()
                ->month($entry->startMonth)
                ->year($entry->startYear)
                ->startOfMonth()
                ->setDay(15)
                ->format('F Y'),
                'totalPackage' => (float) ($entry->package->totalAmount ?? 0),
                'monthlyPayment' => (float) ($entry->package->monthlyPayment ?? 0),
            ],
            'schedules' => [],
            'status' => 'NO_SCHEDULES',
            'message' => 'No schedules found for this entry'
        ]);
    }

$mapped = $schedules
    ->sortBy('dueDate')
    ->values()
    ->map(function ($sched) {
        // ✅ Check by actual amount, not just status
        $isPaid = (float)$sched->amountPaid >= (float)$sched->amountDue && (float)$sched->amountDue > 0;

        return [
            'scheduleID' => $sched->scheduleID,
            'monthName'  => \Carbon\Carbon::parse($sched->dueDate)->format('F'),
            'dueDate'    => $sched->dueDate,
            'amountDue'  => (float) $sched->amountDue,
            'amountPaid' => (float) $sched->amountPaid,
            'isPaid'     => $isPaid,
            'status'     => $isPaid ? 'paid' : $sched->status,
        ];
    });

    return response()->json([
        'entry' => [
            'entryID' => $entry->paluwaganEntryID,
            'name' => $entry->package->packageName ?? 'N/A',
            'startDate' => \Carbon\Carbon::create()
                ->month($entry->startMonth)
                ->year($entry->startYear)
                ->startOfMonth()
                ->setDay(15)
                ->format('F Y'),
            'totalPackage' => (float) ($entry->package->totalAmount ?? 0),
            'monthlyPayment' => (float) ($entry->package->monthlyPayment ?? 0),
        ],
        'schedules' => $mapped,
        'status' => 'OK'
    ]);
}

    public function availableMonths($packageID)
{
    try {
        $year = \App\Models\PaluwaganMonthAvailability::where('packageID', $packageID)
            ->max('year') ?? now()->year;

        $months = \App\Models\PaluwaganMonthAvailability::where('packageID', $packageID)
            ->where('year', $year)
            ->where('status', 'active')
            ->orderBy('month')
            ->get()
            ->map(function ($m) {
                return [
                    'month' => $m->month,
                    'label' => \Carbon\Carbon::create()->month($m->month)->format('F')
                ];
            });

        return response()->json($months);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

// ==============================
    // GCASH PAYMENT FOR PALUWAGAN
    // ==============================
    public function payWithGcash(Request $request)
    {
        try {
            $customer = session('logged_in_user');
            if (!$customer) {
                return response()->json(['error' => 'Login required'], 401);
            }

            $validated = $request->validate([
                'entryID'  => 'required|integer',
                'amount'   => 'required|numeric|min:1',
            ]);

            $entryID = $validated['entryID'];
            $amount  = floatval($validated['amount']);

            // =========================
            // VERIFY ENTRY BELONGS TO CUSTOMER
            // =========================
            $entry = PaluwaganEntry::with('package')
                ->where('paluwaganEntryID', $entryID)
                ->where('customerID', $customer['customerID'])
                ->first();

            if (!$entry) {
                return response()->json(['error' => 'Entry not found'], 404);
            }

            // =========================
            // GET PENDING SCHEDULES
            // =========================
            $pendingSchedules = PaluwaganSchedule::where('paluwaganEntryID', $entryID)
                ->whereIn('status', ['pending', 'late'])
                ->orderBy('dueDate')
                ->get();

            if ($pendingSchedules->isEmpty()) {
                return response()->json(['error' => 'No pending payments'], 400);
            }

            // Validate amount doesn't exceed total remaining
            $totalRemaining = $pendingSchedules->sum(fn($s) => $s->amountDue - $s->amountPaid);
            if ($amount > $totalRemaining) {
                return response()->json([
                    'error' => 'Amount exceeds total remaining balance of ₱' . number_format($totalRemaining, 2)
                ], 400);
            }

            // =========================
            // CREATE CHECKOUT SESSION
            // =========================
            $response = Http::withBasicAuth(config('services.paymongo.secret'), '')
                ->post('https://api.paymongo.com/v1/checkout_sessions', [
                    'data' => [
                        'attributes' => [
                            'line_items' => [[
                                'name'     => 'Paluwagan Payment - ' . $entry->package->packageName,
                                'amount'   => intval($amount * 100),
                                'currency' => 'PHP',
                                'quantity' => 1,
                            ]],
                            'payment_method_types' => ['gcash'],
                            'success_url' => route('checkout.payment.success'),
                            'cancel_url'  => route('checkout.payment.failed'),
                            'metadata'    => [
                                'context'  => 'paluwagan',
                                'entry_id' => (string) $entryID,
                                'amount'   => (string) $amount,
                            ],
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('PayMongo Paluwagan error', $response->json());
                return response()->json(['error' => 'PayMongo failed'], 500);
            }

            $data            = $response->json()['data'];
            $checkoutId      = $data['id'];
            $checkoutUrl     = $data['attributes']['checkout_url'] ?? null;

            // =========================
            // CREATE PENDING PAYMENT RECORD
            // =========================
            // Use the first pending schedule
            $firstSchedule = $pendingSchedules->first();

            Payment::create([
                'paluwaganEntryID'   => $entryID,
                'scheduleID'         => $firstSchedule->scheduleID,
                'contextType'        => 'paluwagan',
                'paymentType'        => 'downpayment',
                'amount'             => $amount,
                'paymentDate'        => now(),
                'method'             => 'GCASH',
                'status'             => 'pending',
                'checkout_session_id'=> $checkoutId,
                'checkout_url'       => $checkoutUrl,
                'meta'               => json_encode(['stage' => 'checkout_created']),
            ]);

            Log::info('💰 PALUWAGAN CHECKOUT CREATED', [
                'entryID'    => $entryID,
                'amount'     => $amount,
                'checkoutId' => $checkoutId,
            ]);

            return response()->json(['checkout_url' => $checkoutUrl]);

        } catch (\Throwable $e) {
            Log::error('💥 PALUWAGAN GCASH ERROR', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

public function cancel($id)
{
    try {
        $entry = PaluwaganEntry::find($id);

        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => 'Entry not found'
            ], 404);
        }

        if ($entry->status === 'cancelled') {
            return response()->json([
                'success' => true,
                'message' => 'Already cancelled'
            ]);
        }

        $entry->status = 'cancelled';
        $entry->save();

        $schedules = \App\Models\PaluwaganSchedule::where('paluwaganEntryID', $id)->get();

        foreach ($schedules as $schedule) {
            // ✅ Only cancel UNPAID schedules — preserve paid progress!
            if ($schedule->status !== 'paid' && $schedule->amountPaid < $schedule->amountDue) {
                $schedule->status = 'cancelled';
                $schedule->save();
            }
            // ✅ Paid schedules stay as 'paid' — progress preserved!
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully. Payment progress preserved.'
        ]);

    } catch (\Throwable $e) {
        \Log::error('Cancel error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Server error (check logs)'
        ], 500);
    }
}

}