@component('emails.layouts.base', [
    'title' => $subjectLine,
    'eyebrow' => 'Thư mời nhận việc',
    'preview' => 'Vui lòng phản hồi thư mời nhận việc từ hệ thống tuyển dụng.',
])
    {!! $htmlBody !!}
@endcomponent
