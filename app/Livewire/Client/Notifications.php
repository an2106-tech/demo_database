<?php

namespace App\Livewire\Client;

use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Notifications extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['all', 'unread', 'read'], true) ? $filter : 'all';
        $this->resetPage();
    }

    public function markAsRead(int $notificationId): void
    {
        UserNotification::query()
            ->whereKey($notificationId)
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->dispatch('app-notify', message: 'Đã đánh dấu thông báo là đã đọc.');
    }

    public function markAllAsRead(): void
    {
        $count = UserNotification::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($count > 0) {
            $this->dispatch('app-notify', message: "Đã đánh dấu {$count} thông báo là đã đọc.");
        }
    }

    public function deleteNotification(int $notificationId): void
    {
        UserNotification::query()
            ->whereKey($notificationId)
            ->where('user_id', Auth::id())
            ->delete();

        $this->dispatch('app-notify', message: 'Đã xóa thông báo.');
    }

    public function deleteAllRead(): void
    {
        $count = UserNotification::query()
            ->where('user_id', Auth::id())
            ->whereNotNull('read_at')
            ->delete();

        if ($count > 0) {
            $this->dispatch('app-notify', message: "Đã dọn dẹp {$count} thông báo đã đọc.");
        } else {
            $this->dispatch('app-notify', message: 'Không có thông báo đã đọc nào để xóa.');
        }
    }

    #[Computed]
    public function unreadCount(): int
    {
        return UserNotification::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    #[Computed]
    public function totalCount(): int
    {
        return UserNotification::query()
            ->where('user_id', Auth::id())
            ->count();
    }

    public function render()
    {
        $notifications = UserNotification::query()
            ->where('user_id', Auth::id())
            ->when($this->filter === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($this->filter === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()
            ->paginate(10);

        $view = view('livewire.client.notifications', [
            'notifications' => $notifications,
            'isEmployerArea' => request()->routeIs('employers.*'),
        ]);

        return request()->routeIs('employers.*')
            ? $view->layout('layouts.employer')
            : $view->layout('layouts.client');
    }
}
