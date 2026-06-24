{{ config('mail.from.name', config('app.name')) }}
{{ str_repeat('=', mb_strlen(config('mail.from.name', config('app.name')))) }}

{{ $slot }}

@isset($subcopy)
---
{{ $subcopy }}
@endisset

---
© {{ date('Y') }} {{ config('mail.from.name', config('app.name')) }}.
Email này được gửi tự động từ hệ thống tuyển dụng.
