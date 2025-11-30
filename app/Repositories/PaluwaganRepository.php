<?php

namespace App\Repositories;

use App\Models\PaluwaganEntry;
use Illuminate\Support\Facades\DB;

interface PaluwaganRepositoryInterface {
    public function getUserEntries(int $customerID);
    public function joinPackage(int $customerID, int $packageID);
}

class PaluwaganRepository implements PaluwaganRepositoryInterface
{
    public function getUserEntries(int $customerID)
    {
        return DB::table('paluwaganentry')
            ->join('paluwaganpackage', 'paluwaganentry.packageID', '=', 'paluwaganpackage.packageID')
            ->where('paluwaganentry.customerID', $customerID)
            ->select(
                'paluwaganentry.paluwaganEntryID as entryID',
                'paluwaganpackage.packageID as id',
                'paluwaganpackage.packageName as name',
                'paluwaganpackage.description as desc',
                'paluwaganentry.joinDate as joinDate',
                'paluwaganentry.status as status',
                'paluwaganpackage.totalAmount as package_amount',
                'paluwaganpackage.durationMonths as total_months',
                'paluwaganpackage.monthlyPayment',
                'paluwaganpackage.image as image'
            )->get();
    }

    public function joinPackage(int $customerID, int $packageID)
    {
        $exists = PaluwaganEntry::where('customerID', $customerID)
                                ->where('packageID', $packageID)
                                ->exists();

        if ($exists) {
            return false;
        }

        return PaluwaganEntry::create([
            'customerID' => $customerID,
            'packageID' => $packageID,
            'joinDate' => now(),
            'status' => 'ACTIVE'
        ]);
    }
    public function getAllEntriesByStatus(string $status)
{
    return DB::table('paluwaganentry')
        ->join('paluwaganpackage', 'paluwaganentry.packageID', '=', 'paluwaganpackage.packageID')
        ->where('paluwaganentry.status', strtoupper($status))
        ->select('paluwaganpackage.totalAmount as package_amount')
        ->get();
}

}

