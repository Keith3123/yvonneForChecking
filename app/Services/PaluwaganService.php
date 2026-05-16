<?php

namespace App\Services;

use App\Repositories\PaluwaganRepositoryInterface;
use App\Models\PaluwaganEntry;
use App\Models\PaluwaganPackage;
use App\Models\PaluwaganSchedule;
use App\Models\PaluwaganMonthAvailability;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    public function joinPaluwagan(int $customerID, int $packageID, int $startMonth, int $startDay = 15)
    {
        return DB::transaction(function () use ($customerID, $packageID, $startMonth, $startDay) {

            // Block only the exact same slot, not the whole package
            $exists = PaluwaganEntry::where('customerID', $customerID)
                ->where('packageID',  $packageID)
                ->where('startMonth', $startMonth)
                ->where('startDay',   $startDay)
                ->where('status', 'active')
                ->exists();

            if ($exists) {
                throw new \Exception("You already have an active entry for this slot.");
            }

            $entry = PaluwaganEntry::create([
                'customerID' => $customerID,
                'packageID'  => $packageID,
                'joinDate'   => now(),
                'status'     => 'active',
                'startMonth' => $startMonth,
                'startDay'   => $startDay,
                'startYear'  => now()->year,
            ]);

            $package = PaluwaganPackage::findOrFail($packageID);
            $this->generateSchedules($entry, $package);

            return $entry;
        });
    }

    /**
     * Generate monthly payment schedules for a paluwagan entry.
     *
     * Rule:
     *  - Schedules start from the EARLIEST admin-opened month >= today.
     *  - Schedules end on or before the customer's chosen release month/day.
     *  - Number of schedules = how many months fit from start → release date.
     *  - Each schedule due date = 15th of each month (or configurable).
     *  - Year wraps correctly (e.g. Nov → Dec → Jan next year).
     */
    public function generateSchedules(PaluwaganEntry $entry, ?PaluwaganPackage $package = null): void
{
    if ($entry->schedules()->count() > 0) return;

    $package ??= $entry->package;

    $today       = Carbon::now();
    $releaseYear = $entry->startYear ?? $today->year;

    // ── Customer's chosen delivery deadline ─────────────────────
    $releaseDate = Carbon::create($releaseYear, $entry->startMonth, $entry->startDay ?? 28)
        ->startOfDay();

    // Guard: if release date is somehow in the past, push to next year
    if ($releaseDate->lessThanOrEqualTo($today)) {
        $releaseDate->addYear();
    }

    // ── Find the schedule start month ────────────────────────────
    $activeMonths = PaluwaganMonthAvailability::where('packageID', $entry->packageID)
        ->where('status', 'active')
        ->orderBy('month')
        ->pluck('month')
        ->toArray();

    $scheduleStart = $this->findScheduleStartDate($activeMonths, $today, $releaseDate);

    // ── Collect all valid due dates (15th of each month up to release) ─
    $current  = $scheduleStart->copy();
    $dueDates = [];
    $maxCap   = $package->durationMonths; // safety cap

    while (count($dueDates) < $maxCap) {
        $dueDate = Carbon::create($current->year, $current->month, 15)->startOfDay();

        // Stop if this due date would fall after the release date
        if ($dueDate->greaterThan($releaseDate)) break;

        $dueDates[] = $dueDate->copy();
        $current->addMonth();
    }

    if (empty($dueDates)) {
        // Edge case: release date is before even the first due date
        // Create one schedule due on the 15th of start month
        $dueDate = Carbon::create($scheduleStart->year, $scheduleStart->month, 15)->startOfDay();
        $dueDates[] = $dueDate;
    }

    // ── Recalculate monthly payment based on actual number of months ──
    $totalAmount   = round((float) $package->totalAmount, 2);
    $numMonths     = count($dueDates);
    $monthlyAmount = round($totalAmount / $numMonths, 2);

    // Rounding correction on last payment
    $sumOfRest     = round($monthlyAmount * ($numMonths - 1), 2);
    $lastAmount    = round($totalAmount - $sumOfRest, 2);

    foreach ($dueDates as $i => $dueDate) {
        $amountDue = ($i === $numMonths - 1) ? $lastAmount : $monthlyAmount;

        PaluwaganSchedule::create([
            'paluwaganEntryID' => $entry->paluwaganEntryID,
            'dueDate'          => $dueDate,
            'amountDue'        => $amountDue,
            'amountPaid'       => 0,
            'status'           => 'pending',
        ]);
    }
}

private function findScheduleStartDate(array $activeMonths, Carbon $today, Carbon $releaseDate): Carbon
{
    if (empty($activeMonths)) {
        return Carbon::create($today->year, $today->month, 1)->startOfMonth();
    }

    $currentMonth = $today->month;
    $currentYear  = $today->year;

    // Months this year that are >= today's month
    $sameYearMonths = array_values(array_filter($activeMonths, fn($m) => $m >= $currentMonth));

    if (!empty($sameYearMonths)) {
        $start = Carbon::create($currentYear, $sameYearMonths[0], 1)->startOfMonth();
        // Don't start after release date
        if ($start->lessThanOrEqualTo($releaseDate)) return $start;
    }

    // All enabled months are earlier in calendar → next year
    sort($activeMonths);
    return Carbon::create($currentYear + 1, $activeMonths[0], 1)->startOfMonth();
}
}