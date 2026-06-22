<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $routeName = \Illuminate\Support\Facades\Route::currentRouteName();
        $baseTitle = config('app.name', 'FPT Careers');
        $fallbackTitle = $routeName
            ? (string) \Illuminate\Support\Str::of($routeName)
                ->replace(['candidates.', 'employers.', 'pages.', 'auth.'], '')
                ->replace(['_', '.'], ' ')
                ->headline()
            : $baseTitle;
        $pageTitle = filled($title ?? null) ? $title : $fallbackTitle;
        $documentTitle = \Illuminate\Support\Str::contains($pageTitle, $baseTitle) ? $pageTitle : "{$pageTitle} | {$baseTitle}";
    @endphp

    <title>{{ $documentTitle }}</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/fe-logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/img/fe-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/fe-logo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/slicknav.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/perfect-scrollbar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/compat.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/auth-unified.css') }}">

    @livewireStyles
</head>

<body class="candidate-app">
    <livewire:header type="candidate" />
    {{ $slot }}
    <livewire:footer type="candidate" />

    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.slicknav.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/jarallax.min.js') }}"></script>
    <script src="{{ asset('assets/js/jarallax-video.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    @stack('scripts')

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('status'))
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: '{!! session('status') !!}',
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    showClass: {
                        popup: 'animate__animated animate__jackInTheBox'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__zoomOut'
                    }
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: '{!! session('error') !!}',
                    showConfirmButton: true,
                    confirmButtonColor: '#0066cc',
                    showClass: {
                        popup: 'animate__animated animate__shakeX'
                    }
                });
            @endif
        });
    </script>
</body>

</html>
