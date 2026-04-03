<?php

namespace App\Repositories;

use App\Models\PaluwaganEntry;
use App\Models\PaluwaganPackage;

interface PaluwaganRepositoryInterface {
    public function getUserEntries(int $customerID);
    public function joinPackage(int $customerID, int $packageID);
    public function getAllEntriesByStatus(string $status);
}

class PaluwaganRepository implements PaluwaganRepositoryInterface
{
    /**
     * Get all paluwagan entries for a user with package, schedules, and payments
     */
    public function getUserEntries(int $customerID)
    {
        return PaluwaganEntry::with(['package', 'schedules', 'schedules.payment'])
            ->where('customerID', $customerID)
            ->get()
            ->map(function ($entry) {
                return [
                    'entryID'       => $entry->paluwaganEntryID,
                    'id'            => $entry->package->packageID,
                    'name'          => $entry->package->packageName,
                    'desc'          => $entry->package->description,
                    'joinDate'      => $entry->joinDate,
                    'status'        => $entry->status,
                    'package_amount'=> $entry->package->totalAmount,
                    'total_months'  => $entry->package->durationMonths,
                    'monthlyPayment'=> $entry->package->monthlyPayment,
                    'image'         => $entry->package->image,
                    'schedules'     => $entry->schedules->map(function ($schedule) {
                        return [
                            'scheduleID'  => $schedule->scheduleID,
                            'dueDate'     => $schedule->dueDate,
                            'amountDue'   => $schedule->amountDue,
                            'amountPaid'  => $schedule->amountPaid,
                            'status'      => $schedule->status,
                            'isPaid'      => $schedule->isPaid,
                            'payments'    => $schedule->payments ?? []
                        ];
                    })
                ];
            });
    }

    /**
     * Join a paluwagan package
     */
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
            'packageID'  => $packageID,
            'joinDate'   => now(),
            'status'     => 'active'
        ]);
    }

    /**
     * Get all entries by status
     */
    public function getAllEntriesByStatus(string $status)
    {
        return PaluwaganEntry::with('package')
            ->where('status', strtolower($status))
            ->get()
            ->map(function ($entry) {
                return [
                    'entryID'       => $entry->paluwaganEntryID,
                    'package_amount'=> $entry->package->totalAmount,
                    'status'        => $entry->status
                ];
            });
    }
}