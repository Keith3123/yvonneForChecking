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

    // ── Filter out stale waiting entries where customer is already
    // active for the same package+month+day slot ─────────────────
    $activeSlots = $entries
        ->where('status', 'active')
        ->map(fn($e) => $e->packageID . '-' . $e->startMonth . '-' . $e->startDay)
        ->values()
        ->toArray();

    $entries = $entries->filter(function($e) use ($activeSlots) {
        if ($e->status !== 'waiting') return true;
        $slot = $e->packageID . '-' . $e->startMonth . '-' . $e->startDay;
        return !in_array($slot, $activeSlots); // hide if already active on same slot
    })->values();

    return view('user.PaluwaganPage', compact('entries'));
}

public function join(Request $request)
{
    $request->validate([
        'packageID'  => 'required|integer',
        'startMonth' => 'required|integer|min:1|max:12',
        'startDay'   => 'required|integer|min:1|max:31',
    ]);

    $customerID = session('logged_in_user.customerID');
    if (!$customerID) return response()->json(['error' => 'Login required.'], 401);

    // ── Block exact duplicate ────────────────────────────────────
    $existing = PaluwaganEntry::where('customerID', $customerID)
        ->where('packageID',  $request->packageID)
        ->where('startMonth', $request->startMonth)
        ->where('startDay',   $request->startDay)
        ->whereIn('status', ['active', 'waiting'])
        ->first();

    if ($existing) {
        return response()->json([
            'error' => $existing->status === 'waiting'
                ? 'You are already in the waiting list for this slot.'
                : 'You already have an active entry for this slot.',
        ], 409);
    }

    // ── NEW: Day-level check — is this exact day already taken? ──
    $dayTaken = PaluwaganEntry::where('packageID',  $request->packageID)
        ->where('startMonth', $request->startMonth)
        ->where('startDay',   $request->startDay)
        ->where('status', 'active')
        ->exists();

    if ($dayTaken) {
        PaluwaganEntry::create([
            'customerID' => $customerID,
            'packageID'  => $request->packageID,
            'startMonth' => $request->startMonth,
            'startDay'   => $request->startDay,
            'startYear'  => now()->year,
            'status'     => 'waiting',
            'joinDate'   => now(),
        ]);

        $monthName = \Carbon\Carbon::create()->month($request->startMonth)->format('F');

        return response()->json([
            'success' => true,
            'waiting' => true,
            'message' => "{$monthName} {$request->startDay} is already taken. You've been added to the waiting list for that exact slot!",
        ]);
    }

    // ── Month cap check (20 active per month) ───────────────────
    $activeCount = PaluwaganEntry::where('packageID',  $request->packageID)
        ->where('startMonth', $request->startMonth)
        ->where('status', 'active')
        ->count();

    if ($activeCount >= 20) {
        PaluwaganEntry::create([
            'customerID' => $customerID,
            'packageID'  => $request->packageID,
            'startMonth' => $request->startMonth,
            'startDay'   => $request->startDay,
            'startYear'  => now()->year,
            'status'     => 'waiting',
            'joinDate'   => now(),
        ]);

        $monthName = \Carbon\Carbon::create()->month($request->startMonth)->format('F');

        return response()->json([
            'success' => true,
            'waiting' => true,
            'message' => "{$monthName} is full (20/20). You've been added to the waiting list for {$monthName} {$request->startDay}!",
        ]);
    }

    // ── Join normally ────────────────────────────────────────────
    try {
        $this->paluwaganService->joinPaluwagan(
            $customerID,
            $request->packageID,
            $request->startMonth,
            $request->startDay
        );
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 409);
    }

    $monthName = \Carbon\Carbon::create()->month($request->startMonth)->format('F');

    return response()->json([
        'success' => true,
        'waiting' => false,
        'message' => "Successfully joined! Your delivery date: {$monthName} {$request->startDay}.",
    ]);
}

    // ── viewSchedule — show release date ─────────────────────────
public function viewSchedule($entryID)
{
    $entry = PaluwaganEntry::with(['schedules.payment', 'package'])->find($entryID);

    if (!$entry) {
        return response()->json(['status' => 'NOT_FOUND', 'message' => 'Entry not found'], 404);
    }

    $schedules = $entry->schedules ?? collect();
    $releaseDate = $entry->startDay
        ? \Carbon\Carbon::create($entry->startYear ?? now()->year, $entry->startMonth, $entry->startDay)
              ->format('F j, Y')
        : \Carbon\Carbon::create()->month($entry->startMonth)->year($entry->startYear ?? now()->year)
              ->format('F Y');

    if ($schedules->isEmpty()) {
        // Inside viewSchedule(), replace the entry array in the return:
        return response()->json([
            'entry' => [
                'entryID'        => $entry->paluwaganEntryID,
                'name'           => $entry->package->packageName ?? 'N/A',
                'releaseDate'    => $releaseDate,
                'totalPackage'   => (float)($entry->package->totalAmount ?? 0),
                // ← Use actual schedule amount, not package default
                'monthlyPayment' => $mapped->count() > 0
                                    ? (float) $mapped->first()['amountDue']
                                    : (float)($entry->package->monthlyPayment ?? 0),
            ],
            'schedules' => $mapped,
            'status'    => 'OK',
        ]);
    }

    $mapped = $schedules->sortBy('dueDate')->values()->map(function ($sched) {
        $isPaid = (float)$sched->amountPaid >= (float)$sched->amountDue && (float)$sched->amountDue > 0;
        return [
            'scheduleID' => $sched->scheduleID,
            'monthName'  => \Carbon\Carbon::parse($sched->dueDate)->format('F'),
            'dueDate'    => $sched->dueDate,
            'amountDue'  => (float)$sched->amountDue,
            'amountPaid' => (float)$sched->amountPaid,
            'isPaid'     => $isPaid,
            'status'     => $isPaid ? 'paid' : $sched->status,
        ];
    });

    return response()->json([
        'entry' => [
            'entryID'        => $entry->paluwaganEntryID,
            'name'           => $entry->package->packageName ?? 'N/A',
            'releaseDate'    => $releaseDate,
            'totalPackage'   => (float)($entry->package->totalAmount ?? 0),
            'monthlyPayment' => (float)($entry->package->monthlyPayment ?? 0),
        ],
        'schedules' => $mapped,
        'status'    => 'OK',
    ]);
}

// ──────────────────────────────────────────────────────────────
//  availableMonths()  — returns activeCount / slotsLeft per month
// ──────────────────────────────────────────────────────────────
public function availableMonths($packageID)
{
    try {
        $currentCustomerID = session('logged_in_user.customerID');
 
        $year = \App\Models\PaluwaganMonthAvailability::where('packageID', $packageID)
            ->max('year') ?? now()->year;
 
        $activeMonthNums = \App\Models\PaluwaganMonthAvailability::where('packageID', $packageID)
            ->where('year',   $year)
            ->where('status', 'active')
            ->pluck('month');
 
        // Load all entries for these months in one query
        $entries = PaluwaganEntry::where('packageID', $packageID)
            ->whereIn('startMonth', $activeMonthNums)
            ->whereIn('status', ['active', 'waiting'])
            ->get();
 
        $result = $activeMonthNums->map(function ($month) use ($entries, $currentCustomerID) {
            $monthEntries = $entries->where('startMonth', $month);
 
            $activeCount  = $monthEntries->where('status', 'active')->count();
            $waitingCount = $monthEntries->where('status', 'waiting')->count();
            $isFull       = $activeCount >= 20;
 
            // Check if current user already has an entry for this month
            $userEntry = $monthEntries->firstWhere('customerID', $currentCustomerID);
 
            return [
                'month'        => $month,
                'label'        => \Carbon\Carbon::create()->month($month)->format('F'),
                'activeCount'  => $activeCount,          // how many joined
                'waitingCount' => $waitingCount,          // how many on waitlist
                'slotsLeft'    => max(0, 20 - $activeCount),
                'isFull'       => $isFull,
                'userDay'      => $userEntry ? (int) $userEntry->startDay   : null,
                'userStatus'   => $userEntry ? $userEntry->status           : null,
            ];
        })->values();
 
        return response()->json($result);
 
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
 
// ──────────────────────────────────────────────────────────────
//  availableDays()  — just returns days 1..N, no per-day slot cap
// ──────────────────────────────────────────────────────────────
public function availableDays($packageID, $month)
{
    try {
        $currentCustomerID = session('logged_in_user.customerID');
        $year              = now()->year;
        $daysInMonth       = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;

        // ── Load ALL entries for this package+month ─────────────
        $entries = PaluwaganEntry::with('customer')
            ->where('packageID',  $packageID)
            ->where('startMonth', $month)
            ->whereIn('status', ['active', 'waiting'])
            ->get();

        $byDay = $entries->groupBy('startDay');

        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dayEntries    = $byDay->get($d, collect());
            $activeForDay  = $dayEntries->where('status', 'active');
            $waitingForDay = $dayEntries->where('status', 'waiting')->values();

            // Is this day taken by another active customer?
            $isTaken = $activeForDay->count() > 0;

            // Does the current user already have an entry for this day?
            $userEntry = $dayEntries->firstWhere('customerID', $currentCustomerID);
            $currentUserStatus = $userEntry ? $userEntry->status : null;

            $userWaitPos = null;
            if ($userEntry && $userEntry->status === 'waiting') {
                $userWaitPos = $waitingForDay->search(
                    fn($e) => $e->customerID === $currentCustomerID
                ) + 1;
            }

            $days[] = [
                'day'                => $d,
                'isTaken'            => $isTaken,
                'waitingCount'       => $waitingForDay->count(),
                'currentUserStatus'  => $currentUserStatus,
                'currentUserWaitPos' => $userWaitPos,
            ];
        }

        return response()->json($days);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
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
            return response()->json(['success' => false, 'message' => 'Entry not found'], 404);
        }
        if ($entry->status === 'cancelled') {
            return response()->json(['success' => true, 'message' => 'Already cancelled']);
        }

        $packageID  = $entry->packageID;
        $startMonth = $entry->startMonth;
        $startDay   = $entry->startDay;

        // Find next waiting customer BEFORE changing anything
        $next = PaluwaganEntry::with('customer')
            ->where('packageID',  $packageID)
            ->where('startMonth', $startMonth)
            ->where('startDay',   $startDay)
            ->where('status',     'waiting')
            ->orderBy('paluwaganEntryID') // FIFO
            ->first();

        if ($next) {
            $promotedCustomerID = $next->customerID;
            $promotedName = trim(
                ($next->customer->firstName ?? '') . ' ' . ($next->customer->lastName ?? '')
            ) ?: 'Next customer';

            // ── Delete the waiting placeholder FIRST by primary key ──
            // Do this before reassigning so there's zero ambiguity
            PaluwaganEntry::destroy($next->paluwaganEntryID);

            // Reassign this entry to the promoted customer
            $entry->customerID = $promotedCustomerID;
            $entry->status     = 'active';
            $entry->save();

            // Reactivate unpaid/cancelled schedules
            \App\Models\PaluwaganSchedule::where('paluwaganEntryID', $id)
                ->where('status', 'cancelled')
                ->update(['status' => 'pending']);

            return response()->json([
                'success' => true,
                'message' => "Slot passed to {$promotedName}. They inherited your payment progress.",
            ]);
        }

        // No one waiting — cancel normally
        $entry->status = 'cancelled';
        $entry->save();

        \App\Models\PaluwaganSchedule::where('paluwaganEntryID', $id)
            ->where('status', '!=', 'paid')
            ->whereColumn('amountPaid', '<', 'amountDue')
            ->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled.',
        ]);

    } catch (\Throwable $e) {
        \Log::error('Cancel error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

public function requestRelease(Request $request, $entryID)
    {
        try {
            $customerID = session('logged_in_user.customerID');
 
            if (!$customerID) {
                return response()->json(['error' => 'Login required'], 401);
            }
 
            $entry = PaluwaganEntry::where('paluwaganEntryID', $entryID)
                ->where('customerID', $customerID)
                ->first();
 
            if (!$entry) {
                return response()->json(['success' => false, 'message' => 'Entry not found'], 404);
            }
 
            // Only active entries can request early release
            if ($entry->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active subscriptions can request early release.'
                ], 400);
            }
 
            // Already requested
            if ($entry->hasPendingReleaseRequest()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending release request.'
                ], 400);
            }
 
            // Already released (but still paying)
            if ($entry->isReleased()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your product has already been released.'
                ], 400);
            }
 
            $note = $request->input('note', '');
 
            $entry->status               = 'release_requested';
            $entry->releaseRequestedAt   = now();
            $entry->releaseNote          = $note;
            $entry->save();
 
            return response()->json([
                'success' => true,
                'message' => 'Early release request submitted. Admin will review and process it shortly.'
            ]);
 
        } catch (\Throwable $e) {
            \Log::error('requestRelease error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
 
    /**
     * Customer cancels their pending release request (changed their mind).
     */
    public function cancelReleaseRequest($entryID)
    {
        try {
            $customerID = session('logged_in_user.customerID');
 
            $entry = PaluwaganEntry::where('paluwaganEntryID', $entryID)
                ->where('customerID', $customerID)
                ->where('status', 'release_requested')
                ->first();
 
            if (!$entry) {
                return response()->json(['success' => false, 'message' => 'No pending release request found'], 404);
            }
 
            $entry->status             = 'active';
            $entry->releaseRequestedAt = null;
            $entry->releaseNote        = null;
            $entry->save();
 
            return response()->json([
                'success' => true,
                'message' => 'Release request cancelled.'
            ]);
 
        } catch (\Throwable $e) {
            \Log::error('cancelReleaseRequest error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    /**
 * Called after any payment is recorded — auto-completes if fully paid
 * and release date has arrived.
 */
private function checkAndAutoComplete(int $entryID): void
{
    try {
        $entry = PaluwaganEntry::with(['schedules', 'package'])->find($entryID);
        if (!$entry || !in_array($entry->status, ['active', 'release_requested'])) return;

        $totalPaid   = (float) $entry->schedules->sum('amountPaid');
        $totalAmount = (float) ($entry->package->totalAmount ?? 0);

        // Not fully paid yet — do nothing
        if ($totalAmount <= 0 || $totalPaid < $totalAmount - 0.01) return;

        // ── Only auto-complete if release date has arrived ────────
        $releaseDate = \Carbon\Carbon::create(
            $entry->startYear  ?? now()->year,
            $entry->startMonth,
            $entry->startDay   ?? 28
        )->startOfDay();

        if (now()->lessThan($releaseDate)) {
            // Fully paid but release date not yet here —
            // just let them request early release manually
            \Log::info("Entry #{$entry->paluwaganEntryID} fully paid but release date not yet reached.");
            return;
        }

        // Fully paid AND release date reached → auto-complete
        foreach ($entry->schedules as $sched) {
            if ($sched->status !== 'paid') {
                $sched->status     = 'paid';
                $sched->amountPaid = $sched->amountDue;
                $sched->save();
            }
        }

        $entry->status     = 'completed';
        $entry->releasedAt = now();
        $entry->save();

        \Log::info("Auto-completed paluwagan entry #{$entry->paluwaganEntryID}");

    } catch (\Throwable $e) {
        \Log::error('checkAndAutoComplete error: ' . $e->getMessage());
    }
}
}