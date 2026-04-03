<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaluwaganPackage;
use App\Models\PaluwaganSchedule;
use App\Models\PaluwaganEntry;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
        }])->get();

        // Summary
        $activeSubscriptions = PaluwaganEntry::where('status', 'active')->count();
        $collectedRevenue = PaluwaganSchedule::sum('amountPaid');
        $expectedRevenue = PaluwaganSchedule::sum('amountDue');

        $latePayments = PaluwaganSchedule::where('dueDate', '<', Carbon::today())
            ->where('status', '!=', 'paid')
            ->count();

        // Subscriptions
        $subscriptions = PaluwaganEntry::with(['package', 'schedules'])->get()->map(function($entry) {
            $package = $entry->package;
            $schedules = $entry->schedules ?? collect();

            $totalPaid = $schedules->where('status', 'paid')->sum('amountPaid');
            $totalMonths = $schedules->count();
            $monthsPaid = $schedules->where('status', 'paid')->count();
            $monthsLeft = $totalMonths - $monthsPaid;
            $nextSchedule = $schedules->where('status', '!=', 'paid')->sortBy('dueDate')->first();

            return [
                'entryID' => $entry->paluwaganEntryID,
                'packageName' => $package?->packageName ?? 'N/A',
                'totalMonths' => $totalMonths,
                'monthsPaid' => $monthsPaid,
                'monthsLeft' => $monthsLeft,
                'monthlyPayment' => $package?->monthlyPayment ?? 0,
                'totalPaid' => $totalPaid,
                'totalAmount' => $package?->totalAmount ?? 0,
                'nextDueDate' => $nextSchedule?->dueDate,
                'status' => $entry->status,
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
        return response()->json([
            'success' => true,
            'month' => $request->month,
            'status' => $request->status
        ]);
    }
}