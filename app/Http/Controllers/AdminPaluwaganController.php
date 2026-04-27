<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaluwaganPackage;
use App\Models\PaluwaganSchedule;
use App\Models\PaluwaganEntry;
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

        $latePayments = PaluwaganSchedule::where('dueDate', '<', Carbon::today())
            ->where('status', '!=', 'paid')
            ->count();

        // Subscriptions
        $subscriptions = PaluwaganEntry::with(['package', 'schedules', 'customer'])->get()->map(function($entry) {
    $package = $entry->package;
    $schedules = $entry->schedules ?? collect();

    $totalPaid = $schedules->sum('amountPaid');
    $totalMonths = $schedules->count();
    
    // ✅ Count months where amountPaid >= amountDue (not just status = 'paid')
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
        'entryID'        => $entry->paluwaganEntryID,
        'packageName'    => $package?->packageName ?? 'N/A',
        'totalMonths'    => $totalMonths,
        'monthsPaid'     => $monthsPaid,
        'monthsLeft'     => $monthsLeft,
        'monthlyPayment' => $package?->monthlyPayment ?? 0,
        'totalPaid'      => $totalPaid,
        'totalAmount'    => $package?->totalAmount ?? 0,
        'nextDueDate'    => $nextSchedule?->dueDate,
        'status'         => $entry->status,
        'customerName'   => trim(
            ($entry->customer->firstName ?? '') . ' ' . ($entry->customer->lastName ?? '')
        ) ?: 'N/A',
    ];
});

        return view('admin.paluwagan', [
            'packages' => $packages,
            'summary' => [
                'activeSubscriptions' => $activeSubscriptions,
                'collectedRevenue' => $collectedRevenue,
                'expectedRevenue' => $expectedRevenue,
                'latePayments' => $latePayments,
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
                'description' => 'required|string|max:500',
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

        // ✅ Check duplicate enrollment
        $alreadyEnrolled = PaluwaganEntry::where('customerID', $customerID)
            ->where('packageID', $entry->packageID)
            ->where('status', 'active')
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json([
                'success' => false,
                'message' => 'This customer already has an active subscription for this package'
            ]);
        }

        // ✅ Change customer and reactivate entry
        $entry->customerID = $customer->customerID;
        $entry->status = 'active';
        $entry->save();

        // ✅ Only reactivate CANCELLED schedules (unpaid ones)
        // Paid schedules remain 'paid' — progress preserved!
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
        $query = $request->input('q', '');

        $customersQuery = \App\Models\Customer::query();

        // Only apply name filter if query is not empty
        if (!empty($query)) {
            $customersQuery->where(function($q) use ($query) {
                $q->where('firstName', 'like', "%{$query}%")
                  ->orWhere('lastName', 'like', "%{$query}%")
                  ->orWhereRaw("CONCAT(firstName, ' ', lastName) LIKE ?", ["%{$query}%"]);
            });
        }

        // ✅ No more exclusions — fetch ALL customers
        $customers = $customersQuery
            ->orderBy('firstName')
            ->limit(50)
            ->get()
            ->map(function($c) {
                return [
                    'customerID' => $c->customerID,
                    'name'       => trim($c->firstName . ' ' . $c->lastName),
                    'email'      => $c->email ?? '',
                ];
            });

        return response()->json([
            'success'   => true,
            'customers' => $customers,
        ]);

    } catch (\Exception $e) {
        \Log::error('searchCustomers error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}