<?php

namespace App\Services;

use App\Models\PaluwaganSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaluwaganPenaltyService
{
    const GRACE_DAYS    = 5;
    const PENALTY_DAILY = 10; // ₱10 per day

    /**
     * Run this daily via scheduler.
     * Checks all late schedules and applies penalties.
     */
    public function applyPenaltiesForEntry(int $entryID): void
{
    $today = Carbon::today();

    $schedules = PaluwaganSchedule::where('paluwaganEntryID', $entryID)
        ->whereIn('status', ['pending', 'late', 'partial'])
        ->where('dueDate', '<', $today)
        ->whereRaw('amountPaid < (amountDue + penaltyAmount)')
        ->get();

    foreach ($schedules as $schedule) {
        $this->processSchedule($schedule, $today);
    }
}

/**
 * Apply penalties for ALL entries (call manually via tinker or a route).
 */
public function applyPenalties(): void
{
    $today = Carbon::today();

    $schedules = PaluwaganSchedule::whereIn('status', ['pending', 'late', 'partial'])
        ->where('dueDate', '<', $today)
        ->whereRaw('amountPaid < (amountDue + penaltyAmount)')
        ->get();

    foreach ($schedules as $schedule) {
        $this->processSchedule($schedule, $today);
    }
}

/**
 * Core penalty logic — shared by both methods above.
 */
private function processSchedule(PaluwaganSchedule $schedule, Carbon $today): void
{
    $dueDate = Carbon::parse($schedule->dueDate);

    // Set grace period end on first encounter
    if (!$schedule->gracePeriodEnd) {
        $schedule->gracePeriodEnd = $dueDate->copy()->addDays(self::GRACE_DAYS);
        $schedule->status         = 'late';
        $schedule->save();
        return; // grace just started, no penalty yet
    }

    $gracePeriodEnd = Carbon::parse($schedule->gracePeriodEnd);

    // Still in grace period — just ensure status is 'late'
    if ($today->lessThanOrEqualTo($gracePeriodEnd)) {
        if ($schedule->status !== 'late') {
            $schedule->status = 'late';
            $schedule->save();
        }
        return;
    }

    // Past grace — calculate days overdue from grace end
    $daysOverdue = $gracePeriodEnd->diffInDays($today);
    $newPenalty  = $daysOverdue * self::PENALTY_DAILY;

    if ((float)$schedule->penaltyAmount !== (float)$newPenalty) {
        $schedule->penaltyAmount = $newPenalty;
        $schedule->status        = 'late';
        $schedule->save();
    }
}

    /**
     * Recalculate penalty for a single schedule (call on-demand).
     */
    public function recalculate(PaluwaganSchedule $schedule): float
    {
        $today = Carbon::today();
        $dueDate = Carbon::parse($schedule->dueDate);

        if ($today->lessThanOrEqualTo($dueDate)) return 0;

        $gracePeriodEnd = $schedule->gracePeriodEnd
            ? Carbon::parse($schedule->gracePeriodEnd)
            : $dueDate->copy()->addDays(self::GRACE_DAYS);

        if ($today->lessThanOrEqualTo($gracePeriodEnd)) return 0;

        return $gracePeriodEnd->diffInDays($today) * self::PENALTY_DAILY;
    }

    
}