<?php

namespace App\Console\Commands;

use App\Models\RecruitmentJob;
use Illuminate\Console\Command;

class ExpireRecruitmentJobs extends Command
{
    protected $signature   = 'recruitment-jobs:expire';
    protected $description = 'Tự động đổi trạng thái tin tuyển dụng sang "hết hạn" khi quá deadline';

    public function handle(): int
    {
        $count = RecruitmentJob::query()
            ->whereIn('status', ['published'])
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        $this->info("Đã cập nhật {$count} tin tuyển dụng sang trạng thái hết hạn.");

        return self::SUCCESS;
    }
}
