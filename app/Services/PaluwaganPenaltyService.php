<?php

namespace App\Services;

use App\Models\PaluwaganSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaluwaganPenaltyService
{
    const GRACE_DAYS    = 5;
    const PENALTY_DAILY = 30; // ₱30 per day

    public function applyPenaltiesForEntry(int $entryID): void
    {
        $today = Carbon::today();

        $schedules = PaluwaganSchedule::where('paluwaganEntryID', $entryID)
            ->whereIn('status', ['pending', 'late', 'partial'])
            ->where('dueDate', '<', $today)
            ->get();

        foreach ($schedules as $schedule) {
            $this->processSchedule($schedule, $today);
        }
    }

    public function applyPenalties(): void
    {
        $today = Carbon::today();

        $schedules = PaluwaganSchedule::whereIn('status', ['pending', 'late', 'partial'])
            ->where('dueDate', '<', $today)
            ->get();

        foreach ($schedules as $schedule) {
            $this->processSchedule($schedule, $today);
        }
    }

    private function processSchedule(PaluwaganSchedule $schedule, Carbon $today): void
    {
        $dueDate = Carbon::parse($schedule->dueDate);

        // Set grace period end on first encounter
        if (!$schedule->gracePeriodEnd) {
            $schedule->gracePeriodEnd = $dueDate->copy()->addDays(self::GRACE_DAYS);
            $schedule->status         = 'late';
            $schedule->save();
            return;
        }

        $gracePeriodEnd = Carbon::parse($schedule->gracePeriodEnd);

        // Still in grace period
        if ($today->lessThanOrEqualTo($gracePeriodEnd)) {
            if ($schedule->status !== 'late') {
                $schedule->status = 'late';
                $schedule->save();
            }
            return;
        }

        // Past grace — ₱30 per day from grace end
        $daysOverdue = (int) $gracePeriodEnd->diffInDays($today);
        $newPenalty  = $daysOverdue * self::PENALTY_DAILY;

        if ((float) $schedule->penaltyAmount !== (float) $newPenalty) {
            $schedule->penaltyAmount = $newPenalty;
            $schedule->status        = 'late';
            $schedule->save();

            Log::info("⚠️ Penalty updated: Schedule #{$schedule->scheduleID} → ₱{$newPenalty} ({$daysOverdue} days overdue)");
        }
    }

    public function recalculate(PaluwaganSchedule $schedule): float
    {
        $today   = Carbon::today();
        $dueDate = Carbon::parse($schedule->dueDate);

        if ($today->lessThanOrEqualTo($dueDate)) return 0;

        $gracePeriodEnd = $schedule->gracePeriodEnd
            ? Carbon::parse($schedule->gracePeriodEnd)
            : $dueDate->copy()->addDays(self::GRACE_DAYS);

        if ($today->lessThanOrEqualTo($gracePeriodEnd)) return 0;

        return (int) $gracePeriodEnd->diffInDays($today) * self::PENALTY_DAILY;
    }
}