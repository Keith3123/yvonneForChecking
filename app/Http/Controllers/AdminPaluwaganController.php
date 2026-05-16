<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaluwaganPackage;
use App\Models\PaluwaganSchedule;
use App\Models\PaluwaganEntry;
use App\Models\Product;
use App\Models\PaluwaganItem;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\PaluwaganMonthAvailability;

class AdminPaluwaganController extends AdminBaseController
{
    public function index()
    {
        parent::__construct();

        $user = session('admin_user');
        if (!$user || ($user['username'] !== 'masteradmin' && $user['roleID'] != 4)) {
            abort(403, 'Unauthorized');
        }
 
        // Fetch packages with schedules
        $packages = PaluwaganPackage::with(['schedules' => function($q) {
            $q->orderBy('dueDate', 'asc');
        }, 'monthAvailability'])->get();

        // Summary
        $activeSubscriptions = PaluwaganEntry::where('status', 'active')->count();
        $collectedRevenue = PaluwaganSchedule::sum('amountPaid');
        $expectedRevenue = PaluwaganSchedule::sum('amountDue');
        $pendingReleases = PaluwaganEntry::where('status', 'release_requested')->count();
 

        $latePayments = PaluwaganSchedule::where('dueDate', '<', Carbon::today())
            ->where('status', '!=', 'paid')
            ->count();

        // Subscriptions
        $subscriptions = PaluwaganEntry::with(['package', 'schedules', 'customer'])->get()->map(function($entry) {
            $package   = $entry->package;
            $schedules = $entry->schedules ?? collect();
 
            $totalPaid   = $schedules->sum('amountPaid');
            $totalMonths = $schedules->count();
 
            $monthsPaid = $schedules->filter(function($s) {
                return (float)$s->amountPaid >= (float)$s->amountDue && (float)$s->amountDue > 0;
            })->count();
 
            $monthsLeft = $totalMonths - $monthsPaid;
 
            $nextSchedule = $schedules
                ->filter(function($s) {
                    return in_array($s->status, ['pending', 'partial', 'late'])
                           && (float)$s->amountPaid < (float)$s->amountDue;
                })
                ->sortBy('dueDate')
                ->first();
 
            return [
                'entryID'              => $entry->paluwaganEntryID,
                'customerID'           => $entry->customerID,
                'packageName'          => $package?->packageName ?? 'N/A',
                'packageID'            => $entry->packageID,       // ← ADD
                'startMonth'           => $entry->startMonth,      // ← ADD
                'totalMonths'          => $totalMonths,
                'monthsPaid'           => $monthsPaid,
                'monthsLeft'           => $monthsLeft,
                'monthlyPayment'       => $package?->monthlyPayment ?? 0,
                'totalPaid'            => $totalPaid,
                'totalAmount'          => $package?->totalAmount ?? 0,
                'nextDueDate'          => $nextSchedule?->dueDate,
                'status'               => $entry->status,
                'startDay'             => $entry->startDay,
                'releaseDate'          => ($entry->startMonth && $entry->startDay)
                                            ? \Carbon\Carbon::create(
                                                $entry->startYear ?? now()->year,
                                                $entry->startMonth,
                                                $entry->startDay
                                            )->format('M d, Y')
                                            : null,
                'customerName'         => trim(
                    ($entry->customer->firstName ?? '') . ' ' . ($entry->customer->lastName ?? '')
                ) ?: 'N/A',
                'releasedAt'           => $entry->releasedAt
                                            ? \Carbon\Carbon::parse($entry->releasedAt)->format('M d, Y')
                                            : null,
                'releaseRequestedAt'   => $entry->releaseRequestedAt
                                            ? \Carbon\Carbon::parse($entry->releaseRequestedAt)->format('M d, Y h:i A')
                                            : null,
                'releaseNote'          => $entry->releaseNote,
            ];
        });

        // After the $subscriptions = PaluwaganEntry::with(...)->get()->map(...) block,
// add this filter before returning the view:

// After the $subscriptions ->map() block, before return view():

$subscriptions = $subscriptions->filter(function($sub) use ($subscriptions) {
    if ($sub['status'] !== 'waiting') return true;

    // Only hide if THIS SAME customer already has an active entry
    // for the exact same slot — means it's a stale orphan
    return !$subscriptions->contains(function($other) use ($sub) {
        return $other['status']     === 'active'
            && $other['customerID'] == $sub['customerID']  // == not === (avoids type mismatch)
            && $other['packageID']  == $sub['packageID']
            && $other['startMonth'] == $sub['startMonth']
            && $other['startDay']   == $sub['startDay'];
    });
})->values();

        $paluwaganItems = PaluwaganItem::where('isActive', 1)
        ->orderBy('category')
        ->orderBy('name')
        ->get();
        
        
        return view('admin.paluwagan', [
            'packages' => $packages,
            'paluwaganItems' => $paluwaganItems,
            'summary' => [
                'activeSubscriptions' => $activeSubscriptions,
                'collectedRevenue' => $collectedRevenue,
                'expectedRevenue' => $expectedRevenue,
                'latePayments' => $latePayments,
                'pendingReleases' => $pendingReleases,
            ],
            'subscriptions' => $subscriptions,
            'months' => $this->getMonthsArray(),
        ]);
    }

    private function getMonthsArray()
    {
        return collect(range(1,12))->mapWithKeys(fn($m)=>[
            $m => [
                'label'=>Carbon::create()->month($m)->format('F'),
                'status'=>'active',
            ]
        ])->toArray();
    }

    // =========================
    // CREATE PACKAGE
    // =========================
    public function createPackage(Request $request)
    {
        try {
            $request->validate([
                'packageName' => 'required|string|max:255',
                'description' => 'required|string|max:5000',
                'totalAmount' => 'required|numeric|min:1',
                'durationMonths' => 'required|integer|min:1',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Upload image (single clean upload)
            $file = $request->file('image');
            $filename = preg_replace('/[^A-Za-z0-9\.\-_]/','_', $file->getClientOriginalName());
            $file->storeAs('public/products', $filename);

            $monthlyPayment = $request->totalAmount / $request->durationMonths;

            $package = PaluwaganPackage::create([
                'packageName' => $request->packageName,
                'description' => $request->description,
                'totalAmount' => $request->totalAmount,
                'durationMonths' => $request->durationMonths,
                'image' => $filename,
            ]);

            return response()->json([
                'success' => true,
                'package' => $package
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =========================
    // UPDATE PACKAGE
    // =========================
    public function updatePackage(Request $request, $id)
    {
        try {
            $request->validate([
                'packageName' => 'required|string|max:255',
                'description' => 'required|string',
                'totalAmount' => 'required|numeric|min:1',
                'durationMonths' => 'required|integer|min:1',
            ]);

            $package = PaluwaganPackage::findOrFail($id);

            $package->packageName = $request->packageName;
            $package->description = $request->description;
            $package->totalAmount = $request->totalAmount;
            $package->durationMonths = $request->durationMonths;

            if ($request->hasFile('image')) {
                if ($package->image) {
                    Storage::delete('public/products/' . $package->image);
                }

                $file = $request->file('image');
                $filename = preg_replace('/[^A-Za-z0-9\.\-_]/','_', $file->getClientOriginalName());
                $file->storeAs('public/products', $filename);

                $package->image = $filename;
            }

            $package->save();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =========================
    // DELETE PACKAGE
    // =========================
    public function destroy($id)
    {
        try {
            $package = PaluwaganPackage::findOrFail($id);
            
            PaluwaganMonthAvailability::where('packageID', $id)->delete();

            if ($package->image) {
                Storage::delete('public/products/'.$package->image);
            }

            $package->delete();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =========================
    // TOGGLE MONTH
    // =========================
    public function toggleMonth(Request $request)
    {
        try {
            $request->validate([
                'packageID' => 'required|integer|exists:paluwaganpackage,packageID',
                'month' => 'required|integer|min:1|max:12',
                'status' => 'required|in:active,inactive',
            ]);

            $packageID = $request->packageID; // 🔥 FIX: define this

            $year = PaluwaganMonthAvailability::where('packageID', $request->packageID)
                ->max('year') ?? now()->year;

            $record = PaluwaganMonthAvailability::updateOrCreate(
                [
                    'packageID' => $packageID,
                    'month' => $request->month,
                    'year' => $year
                ],
                [
                    'status' => $request->status
                ]
            );

            return response()->json([
                'success' => true,
                'month' => $record->month,
                'status' => $record->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

public function complete($id)
{
    try {
        $entry = PaluwaganEntry::with(['schedules', 'package'])->find($id);
 
        if (!$entry) {
            return response()->json(['success' => false, 'message' => 'Entry not found'], 404);
        }
 
        if (!in_array($entry->status, ['active', 'release_requested'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only active subscriptions can be completed.'
            ], 400);
        }
 
        // ── Block if balance not fully paid ──────────────────────
        $totalPaid   = (float) $entry->schedules->sum('amountPaid');
        $totalAmount = (float) ($entry->package->totalAmount ?? 0);
        $remaining   = $totalAmount - $totalPaid;
 
        if ($remaining > 0.01) {
            return response()->json([
                'success' => false,
                'message' => "Cannot complete. Customer still has ₱" .
                             number_format($remaining, 2) .
                             " remaining balance. All payments must be settled first."
            ], 400);
        }
 
        // ── Fully paid → complete ─────────────────────────────────
        $entry->status     = 'completed';
        $entry->releasedAt = $entry->releasedAt ?? now();
        $entry->save();
 
        foreach ($entry->schedules as $schedule) {
            if ($schedule->status !== 'paid') {
                $schedule->status     = 'paid';
                $schedule->amountPaid = $schedule->amountDue;
                $schedule->save();
            }
        }
 
        return response()->json([
            'success' => true,
            'message' => 'Subscription completed successfully.'
        ]);
 
    } catch (\Throwable $e) {
        \Log::error('Complete error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Server error'], 500);
    }
}

public function reassign(Request $request, $entryID)
{
    try {
        $customerID = $request->input('customerID');
        $customer   = \App\Models\Customer::find($customerID);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found']);
        }

        $entry = PaluwaganEntry::find($entryID);
        if (!$entry) {
            return response()->json(['success' => false, 'message' => 'Entry not found']);
        }

        // Block if customer already has active entry for this package
        $alreadyActive = PaluwaganEntry::where('customerID', $customerID)
            ->where('packageID', $entry->packageID)
            ->where('status', 'active')
            ->exists();

        if ($alreadyActive) {
            return response()->json([
                'success' => false,
                'message' => 'This customer already has an active subscription for this package',
            ]);
        }

        // Delete the customer's waiting entry for this same slot (if any)
        // so we don't create a duplicate
        PaluwaganEntry::where('customerID', $customerID)
            ->where('packageID',  $entry->packageID)
            ->where('startMonth', $entry->startMonth)
            ->where('startDay',   $entry->startDay)
            ->where('status', 'waiting')
            ->delete();

        // Reassign this cancelled entry to the new customer
        // New customer inherits all paid schedule history (free benefit)
        $entry->customerID = $customer->customerID;
        $entry->status     = 'active';
        $entry->save();

        // Reactivate cancelled (unpaid) schedules only
        \App\Models\PaluwaganSchedule::where('paluwaganEntryID', $entryID)
            ->where('status', 'cancelled')
            ->update(['status' => 'pending']);

        return response()->json([
            'success' => true,
            'message' => 'Customer replaced successfully. Previous payments are now free credit for the new customer.',
        ]);

    } catch (\Exception $e) {
        \Log::error('Reassign error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Add this helper method
private function promoteNextWaiting(int $packageID, int $startMonth): void
{
    $next = PaluwaganEntry::with('customer')
        ->where('packageID', $packageID)
        ->where('startMonth', $startMonth)
        ->where('status', 'waiting')
        ->orderBy('paluwaganEntryID')
        ->first();

    if (!$next) return;

    // Activate them and generate schedules
    $next->status = 'active';
    $next->save();

    // Generate schedules using your service
    app(\App\Services\PaluwaganService::class)
        ->generateSchedules($next);

    // Optional: notify via email/SMS
}


/**
 * Get payment history for an entry
 */
public function getPayments($entryID)
{
    try {
        $entry = PaluwaganEntry::with('package')
            ->where('paluwaganEntryID', $entryID)
            ->firstOrFail();

        // ── Include both approved AND pending payments ──────────────
        $payments = \App\Models\Payment::where('paluwaganEntryID', $entryID)
            ->where('contextType', 'paluwagan')
            ->whereIn('status', ['approved', 'pending', 'paid'])  // ← was only 'approved'
            ->orderBy('paymentDate', 'asc')
            ->get()
            ->map(function ($payment) {
                $schedule   = \App\Models\PaluwaganSchedule::find($payment->scheduleID);
                $monthLabel = $schedule
                    ? \Carbon\Carbon::parse($schedule->dueDate)->format('F Y')
                    : \Carbon\Carbon::parse($payment->paymentDate)->format('F Y');

                return [
                    'monthLabel' => $monthLabel,
                    'amountPaid' => (float) $payment->amount,
                    'paidAt'     => \Carbon\Carbon::parse($payment->paymentDate)->format('M d, Y h:i A'),
                    'method'     => $payment->method ?? 'GCash',
                    'status'     => $payment->status,  // show status in UI
                ];
            });

        $totalPaid   = (float) \App\Models\PaluwaganSchedule::where('paluwaganEntryID', $entryID)
                        ->sum('amountPaid');
        $totalAmount = (float) ($entry->package->totalAmount ?? 0);

        return response()->json([
            'success'     => true,
            'payments'    => $payments,
            'totalPaid'   => $totalPaid,
            'totalAmount' => $totalAmount,
        ]);

    } catch (\Exception $e) {
        \Log::error('getPayments error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}


public function searchCustomers(Request $request)
{
    try {
        $query      = $request->input('q', '');
        $packageID  = $request->input('packageID');
        $startMonth = $request->input('startMonth');
        $startDay   = $request->input('startDay'); 
 
        // ── CASE 1: Slot is known → show ONLY waiting customers for that slot ──
        if ($packageID && $startMonth) {
            $waitingEntries = PaluwaganEntry::with('customer')
                ->where('packageID',  $packageID)
                ->where('startMonth', $startMonth)
                ->where('startDay',   $startDay)
                ->where('status',     'waiting')
                ->orderBy('paluwaganEntryID') // FIFO
                ->get();
 
            // Filter by search query if provided
            $customers = $waitingEntries
                ->map(fn($e) => $e->customer)
                ->filter() // remove nulls (deleted customers)
                ->when(!empty($query), function ($col) use ($query) {
                    $q = strtolower($query);
                    return $col->filter(fn($c) =>
                        str_contains(strtolower($c->firstName), $q) ||
                        str_contains(strtolower($c->lastName),  $q) ||
                        str_contains(strtolower($c->firstName . ' ' . $c->lastName), $q)
                    );
                })
                ->map(fn($c) => [
                    'customerID' => $c->customerID,
                    'name'       => trim($c->firstName . ' ' . $c->lastName),
                    'email'      => $c->email ?? '',
                    'isWaiting'  => true,
                ])
                ->values();
 
            return response()->json([
                'success'      => true,
                'customers'    => $customers,
                'waitingOnly'  => true,   // tells the UI this is waiting-list mode
                'waitingCount' => $customers->count(),
            ]);
        }
 
        // ── CASE 2: No slot → search all customers (fallback / manual override) ──
        $customersQuery = \App\Models\Customer::query();
 
        if (!empty($query)) {
            $customersQuery->where(function ($q) use ($query) {
                $q->where('firstName', 'like', "%{$query}%")
                  ->orWhere('lastName',  'like', "%{$query}%")
                  ->orWhereRaw("CONCAT(firstName,' ',lastName) LIKE ?", ["%{$query}%"]);
            });
        }
 
        $customers = $customersQuery
            ->orderBy('firstName')
            ->limit(50)
            ->get()
            ->map(fn($c) => [
                'customerID' => $c->customerID,
                'name'       => trim($c->firstName . ' ' . $c->lastName),
                'email'      => $c->email ?? '',
                'isWaiting'  => false,
            ])
            ->values();
 
        return response()->json([
            'success'     => true,
            'customers'   => $customers,
            'waitingOnly' => false,
        ]);
 
    } catch (\Exception $e) {
        \Log::error('searchCustomers error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

 public function approveRelease($entryID)
{
    try {
        $entry = PaluwaganEntry::with(['schedules', 'package'])->find($entryID);

        if (!$entry) {
            return response()->json(['success' => false, 'message' => 'Entry not found'], 404);
        }

        if ($entry->status !== 'release_requested') {
            return response()->json(['success' => false, 'message' => 'No pending release request.'], 400);
        }

        // ── Must be fully paid before releasing ───────────────────
        $totalPaid   = (float) $entry->schedules->sum('amountPaid');
        $totalAmount = (float) ($entry->package->totalAmount ?? 0);
        $remaining   = $totalAmount - $totalPaid;

        if ($remaining > 0.01) {
            return response()->json([
                'success' => false,
                'message' => "Cannot release. Customer still has ₱" .
                             number_format($remaining, 2) .
                             " remaining balance. Full payment required before early release."
            ], 400);
        }

        // Fully paid → approve, release, complete
        $entry->releasedAt = now();
        $entry->status     = 'completed';
        $entry->save();

        foreach ($entry->schedules as $sched) {
            if ($sched->status !== 'paid') {
                $sched->status     = 'paid';
                $sched->amountPaid = $sched->amountDue;
                $sched->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Early release approved! Subscription completed.'
        ]);

    } catch (\Throwable $e) {
        \Log::error('approveRelease error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
    }
}
 
    /**
     * Admin rejects early release request.
     * Entry goes back to 'active', request data is cleared.
     */
    public function rejectRelease(Request $request, $entryID)
    {
        try {
            $entry = PaluwaganEntry::find($entryID);
 
            if (!$entry || $entry->status !== 'release_requested') {
                return response()->json(['success' => false, 'message' => 'No pending release request'], 404);
            }
 
            $entry->status             = 'active';
            $entry->releaseRequestedAt = null;
            $entry->releaseNote        = null;
            $entry->save();
 
            return response()->json([
                'success' => true,
                'message' => 'Release request rejected. Entry is active again.'
            ]);
 
        } catch (\Throwable $e) {
            \Log::error('rejectRelease error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
}