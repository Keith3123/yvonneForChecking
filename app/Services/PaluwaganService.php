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

    public function joinPaluwagan(int $customerID, int $packageID, int $startMonth)
    {
        return DB::transaction(function () use ($customerID, $packageID, $startMonth) {

            // 1️⃣ Check if already enrolled
           $exists = PaluwaganEntry::where('customerID', $customerID)
                        ->where('packageID', $packageID)
                        ->where('status', 'active')
                        ->exists();
            if ($exists) {
                throw new \Exception("You are already enrolled in this paluwagan package.");
            }

            // 2️⃣ Create the entry
            $entry = PaluwaganEntry::create([
                'customerID' => $customerID,
                'packageID' => $packageID,
                'joinDate' => now(),
                'status' => 'active',
                'startMonth' => $startMonth,
                'startYear' => now()->year
            ]);

            // 3️⃣ Get package details
            $package = PaluwaganPackage::findOrFail($packageID);

            // 4️⃣ Generate schedules and payments
            $startDate = now()
                ->month($startMonth)
                ->day(15)
                ->startOfDay();

            for ($m = 0; $m < $package->durationMonths; $m++) {

                $dueDate = $startDate->copy()
                    ->addMonths($m)
                    ->day(15);

                $schedule = PaluwaganSchedule::create([
                    'paluwaganEntryID' => $entry->paluwaganEntryID,
                    'dueDate' => $dueDate,
                    'amountDue' => $package->monthlyPayment,
                    'amountPaid' => 0,
                    'status' => 'pending'
                ]);

                if (!$schedule) {
                    throw new \Exception("Schedule creation failed");
                }

                Payment::create([
                    'paluwaganEntryID' => $entry->paluwaganEntryID,
                    'scheduleID' => $schedule->scheduleID,
                    'contextType' => 'paluwagan',
                    'paymentType' => 'downpayment',
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
