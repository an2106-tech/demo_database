<?php

namespace App\Console\Commands;

use App\Services\ApplicationPreScreeningService;
use Illuminate\Console\Command;

class RemindPreScreeningFollowUps extends Command
{
    protected $signature = 'applications:remind-pre-screening-follow-ups';

    protected $description = 'Nhắc HR xử lý các hồ sơ sơ tuyển đến hạn liên hệ lại';

    public function handle(ApplicationPreScreeningService $preScreeningService): int
    {
        $count = $preScreeningService->remindDueFollowUps();

        $this->info("Đã tạo {$count} nhắc hẹn sơ tuyển.");

        return self::SUCCESS;
    }
}
