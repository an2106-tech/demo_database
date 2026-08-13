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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/client-topcv.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/ui-enterprise.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/employer-portal.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/auth-unified.css') }}">

    @livewireStyles
</head>

<body class="employer-app client-app" style="overflow-x: hidden;">
    <livewire:header type="employer" />
    {{ $slot }}
    <livewire:footer type="employer" />
    @livewire(\App\Livewire\AiChatbox::class, ['audience' => 'employer'])

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
                    confirmButtonColor: '#ff7800',
                    showClass: {
                        popup: 'animate__animated animate__shakeX'
                    }
                });
            @endif
        });

        // Toast notification system
        (function() {
            const toastStack = document.createElement('div');
            toastStack.className = 'app-toast-stack';
            toastStack.style.cssText = 'display:flex;flex-direction:column;gap:12px;pointer-events:none;position:fixed;right:20px;top:20px;width:min(360px,calc(100vw - 32px));z-index:9999;';
            document.body.appendChild(toastStack);

            const closeToast = (toast) => {
                if (!toast || toast.dataset.closing === 'true') return;
                toast.dataset.closing = 'true';
                toast.classList.add('is-closing');
                toast.style.cssText = 'opacity:0;transform:translateY(-8px);transition:all 0.2s ease;';
                setTimeout(() => toast.remove(), 220);
            };

            const showToast = (message, type = 'success') => {
                if (!message) return;
                const toast = document.createElement('div');
                const colors = type === 'success' ? 'background:linear-gradient(135deg,#d1fae5,#fff);border:1px solid #6ee7b7;color:#065f46;' : 
                             type === 'error' ? 'background:linear-gradient(135deg,#fee2e2,#fff);border:1px solid #fca5a5;color:#991b1b;' :
                             'background:linear-gradient(135deg,#fef3c7,#fff);border:1px solid #fcd34d;color:#92400e;';
                toast.className = 'app-toast';
                toast.style.cssText = colors + 'border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.15);display:flex;gap:12px;padding:14px 16px;position:relative;';
                const icon = type === 'success' ? 'fa-check' : type === 'error' ? 'fa-times' : 'fa-exclamation-circle';
                toast.innerHTML = '<div style="display:flex;align-items:center;"><i class="fa ' + icon + '"></i></div>' +
                    '<div style="flex:1;font-weight:600;">' + message + '</div>' +
                    '<button type="button" style="background:none;border:none;cursor:pointer;font-size:18px;color:inherit;" onclick="this.parentElement.remove()">&times;</button>';
                toast.querySelector('button').addEventListener('click', () => closeToast(toast));
                toastStack.appendChild(toast);
                setTimeout(() => closeToast(toast), 4000);
            };

            window.addEventListener('app-notify', (event) => {
                const detail = event.detail || {};
                showToast(detail.message, detail.type || 'success');
            });
        })();
    </script>
    <livewire:ai-chatbox audience="employer" />
</body>

</html>
