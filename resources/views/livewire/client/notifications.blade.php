<div>
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li>
                <a href="{{ $isEmployerArea ? route('employers.dashboard') : route('candidates.candidate_dashboard') }}">
                    {{ $isEmployerArea ? 'Nhà tuyển dụng' : 'Ứng viên' }}
                </a>
            </li>
            <li class="active">Thông báo</li>
        </ul>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-lg-3 col-md-4 dashboard-left-border">
                    @if ($isEmployerArea)
                        @include('livewire.client.partials.employer-sidebar')
                    @else
                        @include('livewire.client.partials.candidate-sidebar')
                    @endif
                </div>

                <div class="col-lg-9 col-md-8">
                    <div class="dashboard-right">
                        <div class="premium-panel" style="padding: 28px;">
                            <div class="manage-jobs-heading" style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:22px;">
                                <div>
                                    <span style="display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border-radius:999px;background:rgba(14,116,144,.08);color:#0f766e;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">Thông báo</span>
                                    <h3 style="margin:10px 0 0;color:#0f172a;">Trung tâm thông báo</h3>
                                    <p style="margin:8px 0 0;color:#64748b;">Theo dõi các cập nhật dành riêng cho tài khoản của bạn.</p>
                                </div>

                                @if ($this->unreadCount > 0)
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="markAllAsRead">
                                        Đánh dấu tất cả đã đọc
                                    </button>
                                @endif
                            </div>

                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
                                <button
                                    type="button"
                                    class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}"
                                    wire:click="setFilter('all')"
                                >
                                    Tất cả
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm {{ $filter === 'unread' ? 'btn-primary' : 'btn-outline-primary' }}"
                                    wire:click="setFilter('unread')"
                                >
                                    Chưa đọc
                                    @if ($this->unreadCount > 0)
                                        <span class="badge bg-light text-dark">{{ $this->unreadCount }}</span>
                                    @endif
                                </button>
                            </div>

                            <div style="display:grid;gap:12px;">
                                @forelse ($notifications as $notification)
                                    <article style="border:1px solid {{ $notification->read_at ? '#e2e8f0' : '#fed7aa' }};border-radius:12px;padding:16px;background:{{ $notification->read_at ? '#fff' : '#fff7ed' }};">
                                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;">
                                            <div style="min-width:0;">
                                                <h4 style="margin:0;color:#0f172a;font-size:16px;">{{ $notification->title }}</h4>
                                                @if ($notification->message)
                                                    <p style="margin:8px 0 0;color:#475569;">{{ $notification->message }}</p>
                                                @endif
                                                <p style="margin:8px 0 0;color:#94a3b8;font-size:13px;">
                                                    {{ $notification->created_at?->diffForHumans() }}
                                                    @if ($notification->read_at)
                                                        · Đã đọc
                                                    @else
                                                        · Chưa đọc
                                                    @endif
                                                </p>
                                            </div>

                                            <div style="display:flex;align-items:center;gap:8px;flex:0 0 auto;">
                                                @if ($notification->action_url)
                                                    <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-secondary">Mở</a>
                                                @endif

                                                @unless ($notification->read_at)
                                                    <button type="button" class="btn btn-sm btn-primary" wire:click="markAsRead({{ $notification->id }})">
                                                        Đã đọc
                                                    </button>
                                                @endunless
                                            </div>
                                        </div>
                                    </article>
                                @empty
                                    <div style="border:1px dashed #cbd5e1;border-radius:12px;padding:28px;text-align:center;color:#64748b;">
                                        Chưa có thông báo nào.
                                    </div>
                                @endforelse
                            </div>

                            <div style="margin-top:18px;">
                                {{ $notifications->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
