<?php

namespace App\Livewire\Admin;

use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationsBell extends Component
{
    public string $tab = 'pending';

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['pending', 'done'], true) ? $tab : 'pending';
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

    public function openNotification(int $notificationId): void
    {
        $notification = UserNotification::query()
            ->whereKey($notificationId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $notification) {
            return;
        }

        if (! $notification->read_at) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        if ($notification->action_url) {
            $this->redirect($notification->action_url, navigate: false);
        }
    }

    public function render()
    {
        $userId = Auth::id();

        $notifications = UserNotification::query()
            ->where('user_id', $userId)
            ->when($this->tab === 'pending', fn ($query) => $query->whereNull('read_at'))
            ->when($this->tab === 'done', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()
            ->limit(6)
            ->get();

        $doneCount = UserNotification::query()
            ->where('user_id', $userId)
            ->whereNotNull('read_at')
            ->count();

        $unreadCount = UserNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        return view('livewire.admin.notifications-bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'doneCount' => $doneCount,
        ]);
    }
}
