@component('emails.layouts.base', [
    'title' => $subjectLine,
    'eyebrow' => 'Lịch phỏng vấn',
    'preview' => 'Thông tin lịch phỏng vấn từ hệ thống tuyển dụng.',
])
    {!! $htmlBody !!}
@endcomponent
