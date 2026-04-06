<?php

use App\Console\Commands\ExpireRecruitmentJobs;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Chạy lúc 00:05 mỗi ngày để đổi trạng thái tin hết hạn
Schedule::command(ExpireRecruitmentJobs::class)->dailyAt('00:05');
