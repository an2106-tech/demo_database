@component('emails.layouts.base', [
    'title' => $subjectLine,
    'eyebrow' => 'Cập nhật hồ sơ',
    'preview' => 'Có cập nhật trạng thái hồ sơ ứng tuyển.',
])
    {!! $htmlBody !!}
@endcomponent
