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
            return redirect()->route('login')->with('error', 'You must be logged in to access Paluwagan.');
        }

        // ✅ Return Eloquent models with relationships
        $entries = $this->paluwaganService->getUserPaluwaganEntries($customerID);

        return view('user.PaluwaganPage', compact('entries'));
    }

    public function join(Request $request)
    {
        $request->validate([
            'packageID' => 'required|integer',
        ]);

        $customerID = session('logged_in_user.customerID');

        if (!$customerID) {
            return response()->json(['error' => 'You must be logged in to join paluwagan.'], 401);
        }

        try {
            $this->paluwaganService->joinPaluwagan($customerID, $request->packageID);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }

        return response()->json(['success' => true, 'message' => 'Successfully joined the paluwagan!']);
    }

    public function viewSchedule($entryID)
    {
        $entry = PaluwaganEntry::with(['schedules.payment', 'package'])->findOrFail($entryID);

        $schedules = $entry->schedules->map(function ($sched) {
            return [
                'monthName' => \Carbon\Carbon::parse($sched->dueDate)->format('F'),
                'dueDate' => $sched->dueDate,
                'amountDue' => $sched->amountDue,
                'amountPaid' => $sched->payment->amount ?? 0,
                'isPaid' => $sched->isPaid,
            ];
        });

        return response()->json([
            'entry' => [
                'entryID' => $entry->paluwaganEntryID,
                'name' => $entry->package->packageName,
                'startDate' => $entry->joinDate,
                'totalPackage' => $entry->package->totalAmount,
                'monthlyPayment' => $entry->package->monthlyPayment
            ],
            'schedules' => $schedules
        ]);
    }
}