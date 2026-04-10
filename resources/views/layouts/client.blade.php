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
    <link rel="stylesheet" href="{{ asset('assets/css/client-topcv.css') }}">

    @livewireStyles

    <style>
        .app-toast-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
            position: fixed;
            right: 20px;
            top: 20px;
            width: min(360px, calc(100vw - 32px));
            z-index: 1080;
        }

        .app-toast {
            align-items: flex-start;
            animation: appToastEnter .25s ease;
            background: linear-gradient(135deg, #effaf3 0%, #ffffff 100%);
            border: 1px solid #bfe1ca;
            border-radius: 18px;
            box-shadow: 0 16px 40px rgba(28, 57, 39, 0.12);
            color: #205437;
            display: flex;
            gap: 12px;
            opacity: 1;
            overflow: hidden;
            padding: 16px 18px;
            pointer-events: auto;
            position: relative;
            transform: translateY(0);
            transition: opacity .2s ease, transform .2s ease;
        }

        .app-toast.is-closing {
            opacity: 0;
            transform: translateY(-8px);
        }

        .app-toast__icon {
            align-items: center;
            background: #dff3e7;
            border-radius: 999px;
            display: inline-flex;
            flex: 0 0 36px;
            font-size: 16px;
            height: 36px;
            justify-content: center;
            width: 36px;
        }

        .app-toast__body {
            flex: 1 1 auto;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.6;
            min-width: 0;
        }

        .app-toast__close {
            background: transparent;
            border: none;
            color: #4a755b;
            cursor: pointer;
            flex: 0 0 auto;
            font-size: 18px;
            line-height: 1;
            margin: -2px -2px 0 0;
            padding: 0;
        }

        @keyframes appToastEnter {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 767px) {
            .app-toast-stack {
                left: 16px;
                right: 16px;
                top: 16px;
                width: auto;
            }
        }
    </style>
</head>

<body class="client-app">
    <div
        class="app-toast-stack"
        data-toast-stack
        @if (session('status'))
            data-initial-message='@json(session('status'))'
        @endif
    ></div>

    {{-- @include('partials.header') --}}
    <livewire:header type="candidate" />
    {{ $slot }}
    <livewire:footer type="candidate" />
    {{-- @include('partials.footer') --}}

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

    @livewireScripts

    <script>
        (() => {
            const toastStack = document.querySelector('[data-toast-stack]');

            if (!toastStack) {
                return;
            }

            const closeToast = (toast) => {
                if (!toast || toast.dataset.closing === 'true') {
                    return;
                }

                toast.dataset.closing = 'true';
                toast.classList.add('is-closing');

                window.setTimeout(() => {
                    toast.remove();
                }, 220);
            };

            const showToast = (message) => {
                if (!message) {
                    return;
                }

                const toast = document.createElement('div');
                toast.className = 'app-toast';
                toast.innerHTML = `
                    <div class="app-toast__icon"><i class="fa fa-check"></i></div>
                    <div class="app-toast__body"></div>
                    <button type="button" class="app-toast__close" aria-label="Đóng thông báo">&times;</button>
                `;

                toast.querySelector('.app-toast__body').textContent = message;
                toast.querySelector('.app-toast__close').addEventListener('click', () => closeToast(toast));

                toastStack.appendChild(toast);
                window.setTimeout(() => closeToast(toast), 4500);
            };

            const initialMessage = toastStack.dataset.initialMessage;

            if (initialMessage) {
                try {
                    showToast(JSON.parse(initialMessage));
                } catch (error) {
                    showToast(initialMessage);
                }
            }

            window.addEventListener('app-notify', (event) => {
                showToast(event.detail?.message);
            });
        })();
    </script>
</body>

</html>
