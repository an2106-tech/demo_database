<?php

namespace App\Console\Commands;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncCandidatesFromUsers extends Command
{
    protected $signature = 'candidates:sync-from-users {--dry-run : Show changes without writing}';

    protected $description = 'Sync candidates records from users that have candidate accounts';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $query = User::query()
            ->where(function ($q) {
                $q->where('role', 'candidate')
                    ->orWhereJsonContains('metadata->account_types', 'candidate');
            })
            ->orderBy('id');

        $created = 0;
        $updated = 0;
        $linked = 0;

        $query->chunkById(200, function ($users) use ($isDryRun, &$created, &$updated, &$linked) {
            foreach ($users as $user) {
                $existingCandidate = Candidate::query()
                    ->where('user_id', $user->id)
                    ->first();

                if (! $existingCandidate && is_string($user->email) && $user->email !== '') {
                    $existingCandidate = Candidate::query()
                        ->whereNull('user_id')
                        ->where('email', $user->email)
                        ->first();
                }

                $attributes = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];

                if ($existingCandidate) {
                    $wasLinked = is_null($existingCandidate->user_id);

                    $existingCandidate->fill(array_filter($attributes, fn ($v) => ! is_null($v)));

                    if ($existingCandidate->isDirty()) {
                        if ($isDryRun) {
                            $this->line("Would update candidate #{$existingCandidate->id} from user #{$user->id}");
                        } else {
                            $existingCandidate->save();
                        }
                        $updated++;
                    }

                    if ($wasLinked) {
                        $linked++;
                    }

                    continue;
                }

                if ($isDryRun) {
                    $this->line("Would create candidate for user #{$user->id}");
                    $created++;
                    continue;
                }

                DB::transaction(function () use ($attributes, &$created) {
                    Candidate::create($attributes);
                    $created++;
                });
            }
        });

        $this->info("Created: {$created}, Updated: {$updated}, Linked by email: {$linked}");

        return self::SUCCESS;
    }
}
