@component('emails.layouts.base', [
    'title' => $subjectLine,
    'eyebrow' => 'Kết quả ứng tuyển',
    'preview' => 'Thông báo kết quả ứng tuyển từ hệ thống tuyển dụng.',
])
    {!! $htmlBody !!}
@endcomponent
