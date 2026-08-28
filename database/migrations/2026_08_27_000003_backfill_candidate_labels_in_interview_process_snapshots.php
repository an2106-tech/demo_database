<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $labels = DB::table('interview_process_template_rounds')
            ->whereNotNull('candidate_label')
            ->get(['interview_process_template_id', 'round_number', 'candidate_label'])
            ->keyBy(fn ($round): string => $round->interview_process_template_id.':'.$round->round_number);

        DB::table('recruitment_jobs')
            ->whereNotNull('interview_process_snapshot')
            ->select(['id', 'interview_process_template_id', 'interview_process_snapshot'])
            ->orderBy('id')
            ->chunkById(100, function ($jobs) use ($labels): void {
                foreach ($jobs as $job) {
                    $snapshot = json_decode((string) $job->interview_process_snapshot, true);

                    if (! is_array($snapshot) || ! is_array($snapshot['rounds'] ?? null)) {
                        continue;
                    }

                    $changed = false;
                    foreach ($snapshot['rounds'] as $index => $round) {
                        if (! is_array($round) || filled($round['candidate_label'] ?? null)) {
                            continue;
                        }

                        $number = (int) ($round['round_number'] ?? $index + 1);
                        $templateLabel = $labels
                            ->get($job->interview_process_template_id.':'.$number)
                            ?->candidate_label;
                        $snapshot['rounds'][$index]['candidate_label'] = $templateLabel
                            ?: ($round['name'] ?? 'Phỏng vấn với đơn vị tuyển dụng');
                        $changed = true;
                    }

                    if ($changed) {
                        DB::table('recruitment_jobs')
                            ->where('id', $job->id)
                            ->update([
                                'interview_process_snapshot' => json_encode(
                                    $snapshot,
                                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                                ),
                            ]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Snapshot enrichment is intentionally retained to avoid losing public labels.
    }
};
