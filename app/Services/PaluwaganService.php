<?php

namespace App\Services;

use App\Repositories\PaluwaganRepositoryInterface;
use App\Models\PaluwaganEntry;
use App\Models\PaluwaganPackage;
use App\Models\PaluwaganSchedule;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaluwaganService
{
    private $repository;

    public function __construct(PaluwaganRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getUserPaluwaganEntries(int $customerID)
    {
        // Eager load relationships, including payment on each schedule
        return PaluwaganEntry::with(['package', 'schedules.payment'])
            ->where('customerID', $customerID)
            ->get();
    }

    public function joinPaluwagan(int $customerID, int $packageID)
    {
        return DB::transaction(function () use ($customerID, $packageID) {

            // 1️⃣ Check if already enrolled
            $exists = PaluwaganEntry::where('customerID', $customerID)
                                    ->where('packageID', $packageID)
                                    ->exists();
            if ($exists) {
                throw new \Exception("You are already enrolled in this paluwagan package.");
            }

            // 2️⃣ Create the entry
            $entry = PaluwaganEntry::create([
                'customerID' => $customerID,
                'packageID' => $packageID,
                'joinDate' => now(),
                'status' => 'active'
            ]);

            // 3️⃣ Get package details
            $package = PaluwaganPackage::findOrFail($packageID);

            // 4️⃣ Generate schedules and payments
            $startDate = now();
            for ($m = 0; $m < $package->durationMonths; $m++) {
                $dueDate = $startDate->copy()->addMonths($m);

                $schedule = PaluwaganSchedule::create([
                    'paluwaganEntryID' => $entry->paluwaganEntryID,
                    'dueDate' => $dueDate->format('Y-m-d'),
                    'amountDue' => $package->monthlyPayment,
                    'amountPaid' => 0,
                    'status' => 'pending',
                    'isPaid' => 0
                ]);

                // 5️⃣ Create Eloquent payment linked to this schedule
                Payment::create([
                    'paluwaganEntryID' => $entry->paluwaganEntryID,
                    'orderID' => null,
                    'contextType' => 'paluwagan',
                    'paymentType' => 'installment',
                    'amount' => 0,
                    'paymentDate' => now(),
                    'method' => 'GCash',
                    'proofURL' => ''
                ]);
            }

            return $entry;
        });
    }
}