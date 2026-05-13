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
        $entry = PaluwaganEntry::with('schedules')->find($id);

        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => 'Entry not found'
            ], 404);
        }

        // 🚨 Only ACTIVE can be completed
        if ($entry->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Only active entries can be completed'
            ], 400);
        }

        // If already completed (extra safety)
        if ($entry->status === 'completed') {
            return response()->json([
                'success' => true,
                'message' => 'Already completed'
            ]);
        }

        $entry->status = 'completed';
        $entry->save();

        // Mark all schedules as fully paid
        if ($entry->schedules) {
            foreach ($entry->schedules as $schedule) {
                $schedule->status = 'paid';
                $schedule->amountPaid = $schedule->amountDue;
                $schedule->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription completed'
        ]);

    } catch (\Throwable $e) {
        \Log::error('Complete error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Server error'
        ], 500);
    }
}

public function reassign(Request $request, $entryID)
{
    try {
        $customerID = $request->input('customerID');
        $customer = \App\Models\Customer::find($customerID);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found']);
        }

        $entry = PaluwaganEntry::where('paluwaganEntryID', $entryID)->first();
        if (!$entry) {
            return response()->json(['success' => false, 'message' => 'Entry not found']);
        }

        // Check if customer already has an ACTIVE subscription for this package
        $alreadyActive = PaluwaganEntry::where('customerID', $customerID)
            ->where('packageID', $entry->packageID)
            ->where('status', 'active')
            ->exists();

        if ($alreadyActive) {
            return response()->json([
                'success' => false,
                'message' => 'This customer already has an active subscription for this package'
            ]);
        }

        // ✅ DELETE the customer's WAITING entry for this same package+month
        // (remove duplicate, don't cancel it)
        PaluwaganEntry::where('customerID', $customerID)
            ->where('packageID', $entry->packageID)
            ->where('startMonth', $entry->startMonth)
            ->where('status', 'waiting')
            ->where('paluwaganEntryID', '!=', $entryID)
            ->delete();

        // Reassign the cancelled entry to this customer
        $entry->customerID = $customer->customerID;
        $entry->status     = 'active';
        $entry->save();

        // Reactivate any cancelled schedules for this entry
        \App\Models\PaluwaganSchedule::where('paluwaganEntryID', $entryID)
            ->where('status', 'cancelled')
            ->update(['status' => 'pending']);

        return response()->json([
            'success' => true,
            'message' => 'Customer replaced successfully. Previous payments retained.'
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
        // ✅ Use where() instead of findOrFail() since custom primary key
        $entry = PaluwaganEntry::with(['schedules', 'package'])
            ->where('paluwaganEntryID', $entryID)
            ->firstOrFail();

        $payments = $entry->schedules->sortBy('dueDate')->values()->map(function($schedule) {
            return [
                'monthLabel'  => Carbon::parse($schedule->dueDate)->format('F Y'),
                'dueDate'     => Carbon::parse($schedule->dueDate)->format('M d, Y'),
                'amountDue'   => (float) $schedule->amountDue,
                'amountPaid'  => (float) $schedule->amountPaid,
                'status'      => $schedule->amountPaid >= $schedule->amountDue ? 'paid' : $schedule->status,
                'paidAt'      => $schedule->status === 'paid' && $schedule->updated_at
                                    ? Carbon::parse($schedule->updated_at)->format('M d, Y') 
                                    : null,
            ];
        });

        return response()->json([
            'success'     => true,
            'payments'    => $payments,
            'totalPaid'   => (float) $entry->schedules->sum('amountPaid'),
            'totalAmount' => (float) ($entry->package->totalAmount ?? 0),
        ]);

    } catch (\Exception $e) {
        \Log::error('getPayments error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * Search ALL customers for replacement
 */
public function searchCustomers(Request $request)
{
    try {
        $query      = $request->input('q', '');
        $packageID  = $request->input('packageID');
        $startMonth = $request->input('startMonth');
 
        // ── CASE 1: Slot is known → show ONLY waiting customers for that slot ──
        if ($packageID && $startMonth) {
            $waitingEntries = PaluwaganEntry::with('customer')
                ->where('packageID',  $packageID)
                ->where('startMonth', $startMonth)
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
            $entry = PaluwaganEntry::find($entryID);
 
            if (!$entry) {
                return response()->json(['success' => false, 'message' => 'Entry not found'], 404);
            }
 
            if ($entry->status !== 'release_requested') {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending release request for this entry.'
                ], 400);
            }
 
            // ✅ Mark as physically released, but keep collecting payments
            $entry->releasedAt = now();
            $entry->status     = 'active';  // stays active — payments continue!
            $entry->save();
 
            return response()->json([
                'success' => true,
                'message' => 'Product released. Payment schedule continues as normal.'
            ]);
 
        } catch (\Throwable $e) {
            \Log::error('approveRelease error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
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