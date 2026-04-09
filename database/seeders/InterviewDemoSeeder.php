<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Interview;
use App\Models\User;
use App\Models\Workplace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class InterviewDemoSeeder extends Seeder
{
    public function run(): void
    {
        $applications = Application::query()
            ->with(['job.branch', 'candidate'])
            ->orderByDesc('id')
            ->take(5)
            ->get();

        if ($applications->isEmpty()) {
            $this->call(ApplicationDemoSeeder::class);

            $applications = Application::query()
                ->with(['job.branch', 'candidate'])
                ->orderByDesc('id')
                ->take(5)
                ->get();
        }

        if ($applications->isEmpty()) {
            return;
        }

        $interviewers = User::query()
            ->where('is_active', true)
            ->whereHas('roles', function ($query): void {
                $query->whereIn('name', ['director', 'pm', 'hr']);
            })
            ->orderBy('id')
            ->get();

        if ($interviewers->isEmpty()) {
            return;
        }

        foreach ($applications as $index => $application) {
            $branchId = $application->job?->branch_id;

            $workplace = Workplace::query()
                ->where('is_active', true)
                ->when(
                    filled($branchId),
                    fn ($query) => $query->where('branch_id', $branchId)
                )
                ->where('is_interview_room', true)
                ->orderBy('id')
                ->first()
                ?? Workplace::query()
                    ->where('is_active', true)
                    ->when(
                        filled($branchId),
                        fn ($query) => $query->where('branch_id', $branchId)
                    )
                    ->orderBy('id')
                    ->first();

            $interviewer = $interviewers[$index % $interviewers->count()];
            $scheduledAt = Carbon::now()
                ->startOfHour()
                ->addDays($index)
                ->addHours(2 + ($index % 3));

            Interview::query()->updateOrCreate(
                [
                    'application_id' => $application->id,
                    'round_number' => 1,
                ],
                [
                    'interviewer_id' => $interviewer->id,
                    'round_name' => 'Phong van vong 1',
                    'scheduled_at' => $scheduledAt,
                    'duration_minutes' => 60,
                    'type' => $index % 2 === 0 ? 'online' : 'offline',
                    'meeting_link' => $index % 2 === 0 ? 'https://meet.google.com/demo-' . ($index + 1) : null,
                    'workplace_id' => $index % 2 === 0 ? null : $workplace?->id,
                    'invite_sent_at' => now()->subDay(),
                    'invite_confirmed_at' => null,
                    'notes' => 'Du lieu demo cho lich phong van.',
                    'result' => match ($index % 3) {
                        0 => 'pending',
                        1 => 'pass',
                        default => 'fail',
                    },
                ]
            );
        }
    }
}
