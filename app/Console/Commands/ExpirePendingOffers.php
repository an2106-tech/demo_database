<?php

namespace App\Console\Commands;

use App\Enums\StatusApplicationEnum;
use App\Models\Offer;
use App\Services\RecruitmentInternalNotificationService;
use Illuminate\Console\Command;

class ExpirePendingOffers extends Command
{
    protected $signature = 'offers:expire-pending';

    protected $description = 'Đánh dấu các đề nghị tuyển dụng đã quá hạn phản hồi';

    public function handle(RecruitmentInternalNotificationService $notifications): int
    {
        $expired = 0;

        Offer::query()
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->with(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator'])
            ->orderBy('id')
            ->each(function (Offer $offer) use ($notifications, &$expired): void {
                $application = $offer->application;
                $offer->forceFill(['status' => 'expired'])->save();

                if ($application) {
                    $status = $application->status instanceof StatusApplicationEnum
                        ? $application->status->value
                        : (string) $application->status;

                    $application->recordStatusHistory(
                        $status,
                        $status ?: StatusApplicationEnum::OFFER->value,
                        'Đề nghị tuyển dụng đã hết hạn phản hồi.',
                    );
                }

                $notifications->notifyOfferExpired($offer);
                $expired++;
            });

        $this->info("Đã cập nhật {$expired} đề nghị tuyển dụng quá hạn.");

        return self::SUCCESS;
    }
}
