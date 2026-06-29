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
        abort_unless($request->hasValidSignature(absolute: false), 403);

        $application = $offer->application()->with(['candidate', 'job'])->firstOrFail();
        $candidateName = $application->candidate?->name ?? 'Ung vien';
        $jobTitle = $application->job?->title ?? 'vi tri ung tuyen';
        $expiresAt = $offer->expires_at;

        if ($offer->status !== 'pending') {
            return view('offers.response-result', [
                'title' => 'Thư mời đã được phản hồi',
                'message' => "Thư mời dành cho {$candidateName} đã được phản hồi trước đó.",
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
                'title' => 'Thư mời đã hết hạn',
                'message' => 'Liên kết phản hồi đã hết hạn. Vui lòng liên hệ bộ phận tuyển dụng để nhận thư mời mới.',
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

            $title = 'Đã đồng ý nhận việc';
            $message = "Cảm ơn {$candidateName}. Bạn đã đồng ý thư mời nhận việc cho vị trí {$jobTitle}.";
            $status = 'success';
        } else {
            $payload['status'] = 'declined';
            $payload['declined_reason'] = 'Ung vien tu choi tu link email.';
            $payload['accepted_at'] = null;
            // Ứng viên từ chối thư mời không đồng nghĩa HR từ chối hồ sơ.
            // Giữ application ở trạng thái OFFER để admin quyết định gửi lại thư mời hoặc từ chối chính thức.
            $application->forceFill([
                'status' => StatusApplicationEnum::OFFER,
            ])->save();

            $title = 'Đã từ chối thư mời';
            $message = "Phản hồi từ chối thư mời cho vị trí {$jobTitle} đã được ghi nhận. Bộ phận tuyển dụng sẽ liên hệ lại nếu cần đề xuất mới.";
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
