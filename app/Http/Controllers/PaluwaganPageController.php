<?php

namespace App\Http\Controllers;

use App\Services\PaluwaganService;
use Illuminate\Support\Facades\DB;
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

        $orders = $this->paluwaganService->getUserPaluwaganEntries($customerID);

        return view('user.PaluwaganPage', compact('orders'));
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
        }
        catch (\Exception $e) {
            
            return response()->json(['error' => $e->getMessage()], 409);
        }

        return response()->json(['success' => true, 'message' => 'Successfully joined the paluwagan!']);
    }

    public function enroll(Request $request)
    {
        return response()->json(['error' => 'Not implemented'], 501);
    }

    public function viewSchedule($entryID)
{
    $entry = PaluwaganEntry::with('schedules')->findOrFail($entryID);

    $package = DB::table('paluwaganpackage')
        ->where('packageID', $entry->packageID)
        ->first();

    $startMonth = date('n', strtotime($entry->joinDate)); 
    $startYear = date('Y', strtotime($entry->joinDate));
    $totalMonths = $package->durationMonths;
    $schedules = [];

    for ($i = 0; $i < $totalMonths; $i++) {
        $month = ($startMonth + $i - 1) % 12 + 1;
        $year = $startYear + floor(($startMonth + $i - 1)/12);
        $dueDate = \Carbon\Carbon::create($year, $month, 15);

        $existingSchedule = $entry->schedules->firstWhere('dueDate', $dueDate->toDateString());

        $schedules[] = [
            'monthName' => $dueDate->format('F'),
            'dueDate' => $dueDate->toDateString(),
            'amountDue' => $package->monthlyPayment,
            'amountPaid' => $existingSchedule->amountPaid ?? 0,
            'isPaid' => $existingSchedule->isPaid ?? false,
        ];
    }

    return response()->json([
        'entry' => [
            'entryID' => $entry->paluwaganEntryID,
            'name' => $package->packageName,
            'startDate' => $entry->joinDate,
            'totalPackage' => $package->totalAmount,
            'monthlyPayment' => $package->monthlyPayment
        ],
        'schedules' => $schedules
    ]);
}

}


