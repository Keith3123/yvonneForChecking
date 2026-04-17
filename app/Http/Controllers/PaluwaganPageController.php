<?php

namespace App\Http\Controllers;

use App\Services\PaluwaganService;
use App\Models\PaluwaganEntry;
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

            $isPaid = (float)$sched->amountPaid >= (float)$sched->amountDue;

            return [
                'scheduleID' => $sched->scheduleID,
                'monthName' => \Carbon\Carbon::parse($sched->dueDate)->format('F'),
                'dueDate' => $sched->dueDate,
                'amountDue' => (float) $sched->amountDue,
                'amountPaid' => (float) $sched->amountPaid,
                'isPaid' => $isPaid,
                'status' => $isPaid ? 'paid' : $sched->status,
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

public function pay(Request $request)
{
    $entryID = $request->entryID;
    $amount = floatval($request->amount);

    $schedules = PaluwaganSchedule::where('paluwaganEntryID', $entryID)
        ->orderBy('dueDate')
        ->get();

    foreach ($schedules as $sched) {

        if ($amount <= 0) break;

        $remaining = $sched->amountDue - $sched->amountPaid;

        if ($remaining <= 0) continue;

        $paying = min($remaining, $amount);

        $sched->amountPaid += $paying;
        $amount -= $paying;

        if ($sched->amountPaid >= $sched->amountDue) {
            $sched->status = 'paid';
        } else {
            $sched->status = 'partial';
        }

        $sched->save();

        Payment::create([
            'paluwaganEntryID' => $entryID,
            'scheduleID' => $sched->scheduleID,
            'contextType' => 'paluwagan',
            'paymentType' => 'partial',
            'amount' => $paying,
            'paymentDate' => now(),
            'method' => 'GCash',
            'proofURL' => ''
        ]);
    }

    return response()->json(['success' => true]);
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

        // safer query instead of relationship dependency
        $schedules = \App\Models\PaluwaganSchedule::where('paluwaganEntryID', $id)->get();

        foreach ($schedules as $schedule) {
            $schedule->status = 'cancelled';
            $schedule->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully'
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