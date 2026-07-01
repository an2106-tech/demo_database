@component('emails.layouts.base', [
    'title' => $subjectLine,
    'eyebrow' => 'Đề nghị tuyển dụng',
    'preview' => 'Vui lòng phản hồi đề nghị tuyển dụng từ FPT Career.',
])
    {!! $htmlBody !!}
@endcomponent
