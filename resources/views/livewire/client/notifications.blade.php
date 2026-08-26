<div class="candidate-notifications-page">
    {{-- Top Unified Breadcrumb --}}
    <div class="fpt-breadcrumb-bar">
        <div class="container-fluid px-lg-5">
            <div class="fpt-breadcrumb-inner">
                <ul class="fpt-breadcrumb-trail">
                    <li><a href="{{ route('home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li>
                        <a href="{{ $isEmployerArea ? route('employers.dashboard') : route('candidates.candidate_dashboard') }}">
                            {{ $isEmployerArea ? 'Nhà tuyển dụng' : 'Ứng viên' }}
                        </a>
                    </li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li class="current">Trung tâm Thông báo</li>
                </ul>

                <a href="{{ $isEmployerArea ? route('employers.dashboard') : route('candidates.candidate_dashboard') }}" class="fpt-back-btn">
                    <i class="fa fa-arrow-left"></i> Bảng điều khiển
                </a>
            </div>
        </div>
    </div>

    {{-- Main Workspace Area --}}
    <section class="candidate-dashboard-area section_70" style="padding-top: 30px; padding-bottom: 70px;">
        <div class="container-fluid px-lg-5">
            <div class="row">
                {{-- Left Navigation Sidebar --}}
                <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                    @if ($isEmployerArea)
                        @include('livewire.client.partials.employer-sidebar')
                    @else
                        @include('livewire.client.partials.candidate-sidebar')
                    @endif
                </div>

                {{-- Right Main Hub --}}
                <div class="col-lg-9 col-md-8 mx-auto">
                    <main class="fpt-notifications-hub">
                        {{-- Hub Page Heading --}}
                        <div class="fpt-hub-header">
                            <div>
                                <span class="fpt-hub-eyebrow">
                                    <i class="fa fa-bell-o"></i> Hộp thông báo hệ thống
                                </span>
                                <h1 class="fpt-hub-title">Trung tâm Thông báo</h1>
                                <p class="fpt-hub-subtitle">Cập nhật tiến độ ứng tuyển, lịch phỏng vấn và các thông báo mới nhất từ FPT Education.</p>
                            </div>

                            <div class="fpt-hub-meta-actions">
                                @if ($this->unreadCount > 0)
                                    <button
                                        type="button"
                                        class="fpt-btn-mark-all"
                                        wire:click="markAllAsRead"
                                        wire:loading.attr="disabled"
                                        wire:target="markAllAsRead"
                                    >
                                        <i class="fa fa-check-circle-o me-1 text-primary"></i>
                                        <span>Đánh dấu tất cả đã đọc</span>
                                    </button>
                                @endif

                                <button
                                    type="button"
                                    class="fpt-btn-clear-read"
                                    wire:click="deleteAllRead"
                                    wire:confirm="Bạn có chắc chắn muốn dọn dẹp tất cả thông báo đã đọc không?"
                                    wire:loading.attr="disabled"
                                    wire:target="deleteAllRead"
                                    title="Xóa tất cả thông báo đã đọc"
                                >
                                    <i class="fa fa-trash-o me-1"></i>
                                    <span>Dọn dẹp đã đọc</span>
                                </button>
                            </div>
                        </div>

                        {{-- Double-Bezel Hardware Container --}}
                        <div class="fpt-notif-shell">
                            <div class="fpt-notif-core">
                                {{-- Filter Tabs Bar --}}
                                <div class="fpt-filter-toolbar">
                                    <div class="fpt-filter-pills">
                                        <button
                                            type="button"
                                            class="fpt-filter-pill {{ $filter === 'all' ? 'is-active' : '' }}"
                                            wire:click="setFilter('all')"
                                        >
                                            <i class="fa fa-inbox me-1"></i> Tất cả
                                            <span class="fpt-pill-count">{{ $this->totalCount }}</span>
                                        </button>

                                        <button
                                            type="button"
                                            class="fpt-filter-pill {{ $filter === 'unread' ? 'is-active' : '' }}"
                                            wire:click="setFilter('unread')"
                                        >
                                            <i class="fa fa-dot-circle-o me-1 text-primary"></i> Chưa đọc
                                            @if ($this->unreadCount > 0)
                                                <span class="fpt-pill-count unread">{{ $this->unreadCount }}</span>
                                            @endif
                                        </button>

                                        <button
                                            type="button"
                                            class="fpt-filter-pill {{ $filter === 'read' ? 'is-active' : '' }}"
                                            wire:click="setFilter('read')"
                                        >
                                            <i class="fa fa-check me-1"></i> Đã đọc
                                        </button>
                                    </div>
                                </div>

                                {{-- Notifications List --}}
                                <div class="fpt-notif-list" wire:loading.class="is-loading" wire:target="setFilter,markAsRead,markAllAsRead,deleteNotification,deleteAllRead">
                                    @forelse ($notifications as $notification)
                                        @php
                                            $isUnread = is_null($notification->read_at);
                                            $type = (string) $notification->type;
                                            
                                            // Icon & Theme Styling based on type
                                            $iconClass = 'fa-bell';
                                            $themeClass = 'default';
                                            
                                            if (str_contains($type, 'application') || str_contains($type, 'status')) {
                                                $iconClass = 'fa-briefcase';
                                                $themeClass = 'application';
                                            } elseif (str_contains($type, 'interview')) {
                                                $iconClass = 'fa-calendar-check-o';
                                                $themeClass = 'interview';
                                            } elseif (str_contains($type, 'ai') || str_contains($type, 'mock')) {
                                                $iconClass = 'fa-magic';
                                                $themeClass = 'ai';
                                            } elseif (str_contains($type, 'security') || str_contains($type, 'password')) {
                                                $iconClass = 'fa-shield';
                                                $themeClass = 'security';
                                            }
                                        @endphp

                                        <article class="fpt-notif-item {{ $isUnread ? 'is-unread' : 'is-read' }}">
                                            {{-- Type Icon Squircle --}}
                                            <div class="fpt-notif-icon-box {{ $themeClass }}">
                                                <i class="fa {{ $iconClass }}"></i>
                                                @if($isUnread)
                                                    <span class="fpt-unread-dot" title="Chưa đọc"></span>
                                                @endif
                                            </div>

                                            {{-- Content Info --}}
                                            <div class="fpt-notif-body">
                                                <div class="fpt-notif-title-row">
                                                    <h3 class="fpt-notif-title">
                                                        {{ $notification->title }}
                                                    </h3>
                                                    <span class="fpt-notif-time" title="{{ $notification->created_at?->format('H:i • d/m/Y') }}">
                                                        <i class="fa fa-clock-o me-1"></i> {{ $notification->created_at?->diffForHumans() }}
                                                    </span>
                                                </div>

                                                @if ($notification->message)
                                                    <p class="fpt-notif-message">
                                                        {{ $notification->message }}
                                                    </p>
                                                @endif

                                                <div class="fpt-notif-footer-actions">
                                                    @if ($notification->action_url)
                                                        <a href="{{ $notification->action_url }}" class="fpt-btn-action" wire:click="markAsRead({{ $notification->id }})">
                                                            <span>Xem chi tiết</span>
                                                            <i class="fa fa-arrow-right" style="font-size: 10px;"></i>
                                                        </a>
                                                    @endif

                                                    @if ($isUnread)
                                                        <button
                                                            type="button"
                                                            class="fpt-btn-mark-single"
                                                            wire:click="markAsRead({{ $notification->id }})"
                                                            title="Đánh dấu đã đọc"
                                                        >
                                                            <i class="fa fa-check me-1 text-primary"></i> Đã đọc
                                                        </button>
                                                    @endif

                                                    <button
                                                        type="button"
                                                        class="fpt-btn-delete-single"
                                                        wire:click="deleteNotification({{ $notification->id }})"
                                                        title="Xóa thông báo này"
                                                    >
                                                        <i class="fa fa-trash-o"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </article>
                                    @empty
                                        <div class="fpt-notif-empty-state">
                                            <div class="fpt-empty-circle">
                                                <i class="fa fa-bell-slash-o"></i>
                                            </div>
                                            <h4 class="fpt-empty-title">Không có thông báo nào</h4>
                                            <p class="fpt-empty-desc">
                                                @if($filter === 'unread')
                                                    Bạn đã đọc tất cả thông báo! Tuyệt vời, hãy tiếp tục khám phá các cơ hội nghề nghiệp tại FPT.
                                                @else
                                                    Hiện tại bạn chưa có thông báo mới. Các cập nhật ứng tuyển sẽ hiển thị tại đây ngay khi có phản hồi.
                                                @endif
                                            </p>
                                            <a href="{{ route('candidates.browse_job') }}" class="fpt-btn-explore">
                                                <i class="fa fa-search me-1"></i> Khám phá việc làm
                                            </a>
                                        </div>
                                    @endforelse
                                </div>

                                {{-- Pagination Container --}}
                                @if($notifications->hasPages())
                                    <div class="fpt-pagination-wrap">
                                        {{ $notifications->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </section>

    {{-- Scoped Luxury CSS for Notifications Hub --}}
    <style>
        .candidate-notifications-page {
            --fpt-bg: #f8fafc;
            --fpt-surface: #ffffff;
            --fpt-ink: #0f172a;
            --fpt-muted: #64748b;
            --fpt-line: #e2e8f0;
            --fpt-line-subtle: #f1f5f9;
            --fpt-primary: #f37021;
            --fpt-primary-hover: #ea580c;
            --fpt-primary-soft: rgba(243, 112, 33, 0.08);
            --fpt-primary-glow: rgba(243, 112, 33, 0.22);
            --fpt-ease: cubic-bezier(0.16, 1, 0.3, 1);

            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--fpt-ink);
        }

        .candidate-notifications-page .fa {
            font-family: 'FontAwesome', FontAwesome !important;
            font-style: normal;
        }

        /* Top Hub Header */
        .fpt-notifications-hub {
            padding: 0;
        }

        .fpt-hub-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 24px;
        }

        .fpt-hub-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 8px;
            border: 1px solid rgba(243, 112, 33, 0.16);
        }

        .fpt-hub-title {
            font-size: 24px;
            font-weight: 850;
            color: var(--fpt-ink);
            letter-spacing: -0.02em;
            margin: 0 0 6px;
        }

        .fpt-hub-subtitle {
            font-size: 13.5px;
            color: var(--fpt-muted);
            margin: 0;
            max-width: 580px;
            line-height: 1.5;
        }

        .fpt-hub-meta-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .fpt-btn-mark-all {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 12px;
            font-size: 12.5px;
            font-weight: 750;
            color: var(--fpt-ink);
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .fpt-btn-mark-all:hover {
            background: var(--fpt-primary-soft);
            border-color: rgba(243, 112, 33, 0.3);
            color: var(--fpt-primary);
        }

        .fpt-btn-clear-read {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            border-radius: 12px;
            font-size: 12.5px;
            font-weight: 650;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fpt-btn-clear-read:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fecaca;
        }

        /* Double-Bezel Hardware Container */
        .fpt-notif-shell {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 24px;
            padding: 6px;
            box-shadow: 0 16px 40px -8px rgba(15, 23, 42, 0.06), 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .fpt-notif-core {
            background: #f8fafc;
            border: 1px solid var(--fpt-line-subtle);
            border-radius: 18px;
            padding: 20px;
        }

        /* Filter Toolbar */
        .fpt-filter-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 16px;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--fpt-line);
        }

        .fpt-filter-pills {
            display: inline-flex;
            background: #ffffff;
            padding: 4px;
            border-radius: 12px;
            border: 1px solid var(--fpt-line);
            gap: 4px;
        }

        .fpt-filter-pill {
            border: none;
            background: transparent;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            color: var(--fpt-muted);
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .fpt-filter-pill.is-active {
            background: #f8fafc;
            color: var(--fpt-ink);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .fpt-pill-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1px 7px;
            border-radius: 999px;
            background: #e2e8f0;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
        }

        .fpt-pill-count.unread {
            background: var(--fpt-primary);
            color: #ffffff;
        }

        /* Notifications List */
        .fpt-notif-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .fpt-notif-list.is-loading {
            opacity: 0.6;
        }

        .fpt-notif-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 18px 20px;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 16px;
            transition: all 0.25s var(--fpt-ease);
            position: relative;
        }

        .fpt-notif-item:hover {
            transform: translateY(-2px);
            border-color: rgba(243, 112, 33, 0.3);
            box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.05);
        }

        .fpt-notif-item.is-unread {
            background: linear-gradient(135deg, #fffaf5 0%, #ffffff 100%);
            border-color: rgba(243, 112, 33, 0.28);
            box-shadow: 0 4px 16px rgba(243, 112, 33, 0.06);
        }

        .fpt-notif-item.is-unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 16px;
            bottom: 16px;
            width: 4px;
            border-radius: 0 4px 4px 0;
            background: var(--fpt-primary);
        }

        /* Type Icon Squircle */
        .fpt-notif-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
            position: relative;
            border: 1px solid var(--fpt-line);
        }

        .fpt-notif-icon-box.application {
            background: #fff7ed;
            color: var(--fpt-primary);
            border-color: #fed7aa;
        }

        .fpt-notif-icon-box.interview {
            background: #f0fdf4;
            color: #16a34a;
            border-color: #bbf7d0;
        }

        .fpt-notif-icon-box.ai {
            background: #faf5ff;
            color: #9333ea;
            border-color: #e9d5ff;
        }

        .fpt-notif-icon-box.security {
            background: #f8fafc;
            color: #475569;
            border-color: #cbd5e1;
        }

        .fpt-unread-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--fpt-primary);
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 2px rgba(243, 112, 33, 0.2);
        }

        /* Body info */
        .fpt-notif-body {
            flex: 1;
            min-width: 0;
        }

        .fpt-notif-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 4px;
        }

        .fpt-notif-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--fpt-ink);
            margin: 0;
            line-height: 1.35;
        }

        .fpt-notif-time {
            font-size: 11.5px;
            color: #94a3b8;
            font-weight: 600;
            white-space: nowrap;
        }

        .fpt-notif-message {
            font-size: 13.5px;
            color: #475569;
            line-height: 1.55;
            margin: 0 0 12px;
        }

        .fpt-notif-footer-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fpt-btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 8px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none !important;
            box-shadow: 0 2px 8px rgba(243, 112, 33, 0.2);
            transition: all 0.2s ease;
        }

        .fpt-btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.3);
        }

        .fpt-btn-mark-single {
            border: 1px solid var(--fpt-line);
            background: #ffffff;
            border-radius: 8px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fpt-btn-mark-single:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .fpt-btn-delete-single {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: transparent;
            border: 1px solid transparent;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: pointer;
            margin-left: auto;
            transition: all 0.2s ease;
        }

        .fpt-btn-delete-single:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fecaca;
        }

        /* Empty State */
        .fpt-notif-empty-state {
            padding: 50px 24px;
            text-align: center;
            background: #ffffff;
            border-radius: 16px;
            border: 1px dashed #cbd5e1;
        }

        .fpt-empty-circle {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 16px;
        }

        .fpt-empty-title {
            font-size: 17px;
            font-weight: 800;
            color: var(--fpt-ink);
            margin: 0 0 6px;
        }

        .fpt-empty-desc {
            font-size: 13.5px;
            color: var(--fpt-muted);
            max-width: 440px;
            margin: 0 auto 20px;
            line-height: 1.6;
        }

        .fpt-btn-explore {
            display: inline-flex;
            align-items: center;
            padding: 9px 22px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none !important;
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.25);
            transition: all 0.2s ease;
        }

        .fpt-btn-explore:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(243, 112, 33, 0.35);
        }

        .fpt-pagination-wrap {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
    </style>
</div>
