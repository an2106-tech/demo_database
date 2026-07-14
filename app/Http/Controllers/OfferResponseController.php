<?php

namespace App\Http\Controllers;

use App\Enums\StatusApplicationEnum;
use App\Models\Offer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class OfferResponseController extends Controller
{
    public function accept(Request $request, Offer $offer): View
    {
        $context = $this->resolveActionableOffer($request, $offer);

        if ($context instanceof View) {
            return $context;
        }

        $application = $context['application'];
        $candidateName = $application->candidate?->name ?? 'ứng viên';
        $jobTitle = $application->job?->title ?? 'vị trí ứng tuyển';
        $fromStatus = $this->statusValue($application->status);

        $offer->forceFill([
            'status' => 'accepted',
            'response_at' => now(),
            'accepted_at' => now(),
        ])->save();

        $application->forceFill([
            'status' => StatusApplicationEnum::HIRED,
            'rejected_reason' => null,
        ])->save();

        $application->recordStatusHistory(
            $fromStatus,
            StatusApplicationEnum::HIRED->value,
            'Ứng viên đã đồng ý đề nghị tuyển dụng qua email.',
        );

        return $this->resultView(
            title: 'Đã xác nhận đồng ý đề nghị tuyển dụng',
            message: "Cảm ơn {$candidateName}. Bạn đã đồng ý đề nghị tuyển dụng cho vị trí {$jobTitle}. Bộ phận tuyển dụng sẽ liên hệ để hướng dẫn thủ tục nhận việc tiếp theo.",
            status: 'success',
            offer: $offer,
            application: $application->fresh(['candidate', 'job.branch']) ?? $application,
        );
    }

    public function decline(Request $request, Offer $offer): View
    {
        $context = $this->resolveActionableOffer($request, $offer);

        if ($context instanceof View) {
            return $context;
        }

        return view('offers.decline-confirm', [
            'offer' => $offer,
            'application' => $context['application'],
            'declineReasons' => $this->declineReasons(),
        ]);
    }

    public function submitDecline(Request $request, Offer $offer): View
    {
        $context = $this->resolveActionableOffer($request, $offer);

        if ($context instanceof View) {
            return $context;
        }

        $validated = $request->validate([
            'decline_reason' => ['required', Rule::in(array_keys($this->declineReasons()))],
            'expected_compensation' => [
                Rule::requiredIf(fn (): bool => $request->input('decline_reason') === 'compensation'),
                'nullable',
                'string',
                'max:120',
            ],
            'preferred_start_date' => [
                Rule::requiredIf(fn (): bool => $request->input('decline_reason') === 'start_date'),
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'decline_note' => [
                Rule::requiredIf(fn (): bool => $request->input('decline_reason') === 'other'),
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'decline_reason.required' => 'Vui lòng chọn lý do từ chối.',
            'decline_reason.in' => 'Lý do từ chối không hợp lệ.',
            'expected_compensation.required' => 'Vui lòng nhập mức đãi ngộ mong muốn.',
            'expected_compensation.max' => 'Mức đãi ngộ mong muốn không được vượt quá 120 ký tự.',
            'preferred_start_date.required' => 'Vui lòng chọn thời gian bắt đầu phù hợp hơn.',
            'preferred_start_date.date' => 'Thời gian bắt đầu không hợp lệ.',
            'preferred_start_date.after_or_equal' => 'Thời gian bắt đầu phù hợp hơn không được ở quá khứ.',
            'decline_note.required' => 'Vui lòng nhập ghi chú khi chọn lý do khác.',
            'decline_note.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
        ]);

        $application = $context['application'];
        $jobTitle = $application->job?->title ?? 'vị trí ứng tuyển';
        $fromStatus = $this->statusValue($application->status);
        $reasonText = $this->formatDeclineReason(
            $validated['decline_reason'],
            $validated['expected_compensation'] ?? null,
            $validated['preferred_start_date'] ?? null,
            $validated['decline_note'] ?? null,
        );

        $offer->forceFill([
            'status' => 'declined',
            'response_at' => now(),
            'accepted_at' => null,
            'declined_reason' => $reasonText,
        ])->save();

        $application->forceFill([
            'status' => StatusApplicationEnum::OFFER,
        ])->save();

        $application->recordStatusHistory(
            $fromStatus,
            StatusApplicationEnum::OFFER->value,
            'Ứng viên đã từ chối đề nghị tuyển dụng. Lý do: '.$reasonText,
        );

        return $this->resultView(
            title: 'Đã ghi nhận phản hồi từ chối',
            message: "Cảm ơn bạn đã phản hồi đề nghị tuyển dụng cho vị trí {$jobTitle}. Bộ phận tuyển dụng sẽ xem xét thông tin và liên hệ lại nếu cần.",
            status: 'warning',
            offer: $offer,
            application: $application->fresh(['candidate', 'job.branch']) ?? $application,
        );
    }

    protected function resolveActionableOffer(Request $request, Offer $offer): array|View
    {
        abort_unless(URL::hasCorrectSignature($request, false), 403);

        $application = $offer->application()->with(['candidate', 'job.branch'])->firstOrFail();

        if (! $this->isCurrentOfferResponseLink($request->query('sent'), $offer->sent_at)) {
            return $this->resultView(
                title: 'Thư mời không còn hiệu lực',
                message: 'Liên kết phản hồi này thuộc thư mời cũ hoặc đã được thay thế. Vui lòng kiểm tra email mới nhất hoặc liên hệ bộ phận tuyển dụng.',
                status: 'expired',
                offer: $offer,
                application: $application,
            );
        }

        if ($offer->status !== 'pending') {
            return $this->resultView(
                title: 'Thư mời đã được phản hồi',
                message: 'Thư mời này đã được phản hồi trước đó. Nếu cần điều chỉnh, vui lòng liên hệ bộ phận tuyển dụng.',
                status: 'info',
                offer: $offer,
                application: $application,
            );
        }

        if ($offer->expires_at && now()->greaterThan($offer->expires_at)) {
            $offer->forceFill([
                'status' => 'expired',
            ])->save();

            $application->recordStatusHistory(
                $this->statusValue($application->status),
                $this->statusValue($application->status) ?? StatusApplicationEnum::OFFER->value,
                'Thư mời nhận việc đã hết hạn phản hồi.',
            );

            return $this->resultView(
                title: 'Thư mời đã hết hạn',
                message: 'Liên kết phản hồi đã hết hạn. Vui lòng liên hệ bộ phận tuyển dụng để được hỗ trợ.',
                status: 'expired',
                offer: $offer,
                application: $application,
            );
        }

        return [
            'application' => $application,
        ];
    }

    protected function resultView(string $title, string $message, string $status, Offer $offer, mixed $application): View
    {
        return view('offers.response-result', [
            'title' => $title,
            'message' => $message,
            'status' => $status,
            'offer' => $offer,
            'application' => $application,
        ]);
    }

    protected function isCurrentOfferResponseLink(mixed $linkSentAt, mixed $currentSentAt): bool
    {
        if (! $linkSentAt || ! $currentSentAt) {
            return false;
        }

        return (int) $linkSentAt === $currentSentAt->getTimestamp();
    }

    protected function declineReasons(): array
    {
        return [
            'compensation' => 'Mong muốn trao đổi thêm về mức đãi ngộ',
            'start_date' => 'Chưa phù hợp về thời gian bắt đầu',
            'career_plan' => 'Định hướng hiện tại chưa phù hợp để tiếp tục',
            'not_ready' => 'Chưa sẵn sàng tiếp nhận vị trí này',
            'other' => 'Lý do khác',
        ];
    }

    protected function formatDeclineReason(
        string $reason,
        ?string $expectedCompensation = null,
        ?string $preferredStartDate = null,
        ?string $note = null,
    ): string
    {
        $reasonText = $this->declineReasons()[$reason] ?? 'Lý do khác';
        $details = [];

        if ($reason === 'compensation' && filled($expectedCompensation)) {
            $details[] = 'Mức đãi ngộ mong muốn: '.trim((string) $expectedCompensation);
        }

        if ($reason === 'start_date' && filled($preferredStartDate)) {
            $details[] = 'Thời gian bắt đầu phù hợp hơn: '.\Carbon\Carbon::parse((string) $preferredStartDate)->format('d/m/Y');
        }

        $noteText = trim((string) $note);

        if ($noteText !== '') {
            $details[] = 'Ghi chú: '.$noteText;
        }

        return $details === []
            ? $reasonText.'.'
            : $reasonText.'. '.implode('. ', $details).'.';
    }

    protected function statusValue(mixed $status): ?string
    {
        return $status instanceof StatusApplicationEnum ? $status->value : ($status ? (string) $status : null);
    }
}
