@component('emails.layouts.base', [
    'title' => $subjectLine,
    'eyebrow' => 'Hồ sơ ứng tuyển',
    'preview' => 'Thông báo từ hệ thống tuyển dụng.',
])
    {!! $htmlBody !!}

    @if (Route::has('candidates.candidate_dashboard'))
        <div class="mail-actions">
            <a href="{{ route('candidates.candidate_dashboard') }}" class="mail-button">
                <span>Truy cập Dashboard</span>
            </a>
        </div>
    @endif
@endcomponent

