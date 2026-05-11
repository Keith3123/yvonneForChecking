<?php

namespace App\Services;

use App\Repositories\PaluwaganRepositoryInterface;
use App\Models\PaluwaganEntry;
use App\Models\PaluwaganPackage;
use App\Models\PaluwaganSchedule;
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
        return PaluwaganEntry::with(['package', 'schedules.payment'])
            ->where('customerID', $customerID)
            ->get();
    }

    public function joinPaluwagan(int $customerID, int $packageID, int $startMonth)
    {
        return DB::transaction(function () use ($customerID, $packageID, $startMonth) {

            // 1️⃣ Already enrolled check
            $exists = PaluwaganEntry::where('customerID', $customerID)
                ->where('packageID', $packageID)
                ->where('status', 'active')
                ->exists();

            if ($exists) {
                throw new \Exception("You are already enrolled in this paluwagan package.");
            }

            // 2️⃣ Create entry
            $entry = PaluwaganEntry::create([
                'customerID' => $customerID,
                'packageID'  => $packageID,
                'joinDate'   => now(),
                'status'     => 'active',
                'startMonth' => $startMonth,
                'startYear'  => now()->year,
            ]);

            // 3️⃣ Load package
            $package = PaluwaganPackage::findOrFail($packageID);

            // 4️⃣ Generate schedules — NO phantom Payment rows
            //    Real payments are created only when customer actually pays via payWithGcash()
            $this->generateSchedules($entry, $package);

            return $entry;
        });
    }

    /**
     * Generate payment schedules for an entry.
     * Called on join AND when a waiting customer is promoted to active.
     */
    public function generateSchedules(PaluwaganEntry $entry, ?PaluwaganPackage $package = null): void
    {
        // Don't double-generate if schedules already exist
        if ($entry->schedules()->count() > 0) {
            return;
        }

        $package ??= $entry->package;

        $startDate = now()
            ->month($entry->startMonth)
            ->day(15)
            ->startOfDay();

        for ($m = 0; $m < $package->durationMonths; $m++) {
            $dueDate = $startDate->copy()->addMonths($m)->day(15);

            PaluwaganSchedule::create([
                'paluwaganEntryID' => $entry->paluwaganEntryID,
                'dueDate'          => $dueDate,
                'amountDue'        => $package->monthlyPayment,
                'amountPaid'       => 0,
                'status'           => 'pending',
            ]);
        }
    }
}