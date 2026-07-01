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
        $this->filter = in_array($filter, ['all', 'unread'], true) ? $filter : 'all';
        $this->resetPage();
    }

    public function markAsRead(int $notificationId): void
    {
        UserNotification::query()
            ->whereKey($notificationId)
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllAsRead(): void
    {
        UserNotification::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    #[Computed]
    public function unreadCount(): int
    {
        return UserNotification::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    public function render()
    {
        $notifications = UserNotification::query()
            ->where('user_id', Auth::id())
            ->when($this->filter === 'unread', fn ($query) => $query->whereNull('read_at'))
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
