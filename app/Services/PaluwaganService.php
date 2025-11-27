<?php

namespace App\Services;

use App\Repositories\PaluwaganRepositoryInterface;
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
        return $this->repository->getUserEntries($customerID);
    }

   public function joinPaluwagan($customerID, $packageID)
{
    $entry = $this->repository->joinPackage($customerID, $packageID);

    if (!$entry) {
        throw new \Exception("Already enrolled in this paluwagan.");
    }

    $package = DB::table('paluwaganpackage')
        ->where('packageID', $packageID)
        ->first();

    // Generate schedules
    for ($m = 1; $m <= $package->durationMonths; $m++) {
        PaluwaganSchedule::create([
            'paluwaganEntryID' => $entry->paluwaganEntryID,
            'dueDate' => now()->addMonths($m)->format('Y-m-d'),
            'amountDue' => $package->monthlyPayment,
            'amountPaid' => 0,
            'status' => 'PENDING',
            'isPaid' => 0
        ]);
    }

    return $entry;
}


}
