<?php

namespace App\Http\Controllers;

use App\Enums\StatusApplicationEnum;
use App\Models\Offer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OfferResponseController extends Controller
{
    public function accept(Request $request, Offer $offer): View
    {
        return $this->handleResponse($request, $offer, 'accept');
    }

    public function decline(Request $request, Offer $offer): View
    {
        return $this->handleResponse($request, $offer, 'decline');
    }

    protected function handleResponse(Request $request, Offer $offer, string $decision): View
    {
        abort_unless($request->hasValidSignature(), 403);

        $application = $offer->application()->with(['candidate', 'job'])->firstOrFail();
        $candidateName = $application->candidate?->name ?? 'Ung vien';
        $jobTitle = $application->job?->title ?? 'vi tri ung tuyen';
        $expiresAt = $offer->expires_at;

        if ($offer->status !== 'pending') {
            return view('offers.response-result', [
                'title' => 'Offer đã được phản hồi',
                'message' => "Offer dành cho {$candidateName} đã được phản hồi trước đó.",
                'status' => 'info',
                'offer' => $offer,
                'application' => $application,
            ]);
        }

        if ($expiresAt && now()->greaterThan($expiresAt)) {
            $offer->forceFill([
                'status' => 'expired',
            ])->save();

            return view('offers.response-result', [
                'title' => 'Offer đã hết hạn',
                'message' => 'Liên kết phản hồi đã hết hạn. Vui lòng liên hệ nhà tuyển dụng để nhận offer mới.',
                'status' => 'expired',
                'offer' => $offer,
                'application' => $application,
            ]);
        }

        $payload = [
            'response_at' => now(),
        ];

        if ($decision === 'accept') {
            $payload['status'] = 'accepted';
            $payload['accepted_at'] = now();

            $application->forceFill([
                'status' => StatusApplicationEnum::HIRED,
                'rejected_reason' => null,
            ])->save();

            $title = 'Đã đồng ý offer';
            $message = "Cảm ơn {$candidateName}. Bạn đã đồng ý thư mời nhận việc cho vị trí {$jobTitle}.";
            $status = 'success';
        } else {
            $payload['status'] = 'declined';
            $payload['declined_reason'] = 'Ung vien tu choi tu link email.';
            $payload['accepted_at'] = null;

            $application->forceFill([
                'status' => StatusApplicationEnum::REJECTED,
                'rejected_reason' => 'Ung vien da tu choi offer.',
            ])->save();

            $title = 'Đã từ chối offer';
            $message = "Phản hồi từ chối offer cho vị trí {$jobTitle} đã được ghi nhận.";
            $status = 'warning';
        }

        $offer->forceFill($payload)->save();

        return view('offers.response-result', [
            'title' => $title,
            'message' => $message,
            'status' => $status,
            'offer' => $offer,
            'application' => $application,
        ]);
    }
}
