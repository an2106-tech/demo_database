<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTitleAttribute(): string
    {
        return (string) data_get($this->data, 'title', $this->defaultTitle());
    }

    public function getMessageAttribute(): string
    {
        return (string) data_get($this->data, 'message', '');
    }

    public function getActionUrlAttribute(): ?string
    {
        $url = data_get($this->data, 'url')
            ?? data_get($this->data, 'action_url')
            ?? data_get($this->data, 'filament_url');

        return is_string($url) && $url !== '' ? $url : null;
    }

    private function defaultTitle(): string
    {
        return match ($this->type) {
            'job_pending_approval' => 'Tin tuyển dụng chờ duyệt',
            'application_status_changed' => 'Cập nhật trạng thái ứng tuyển',
            default => 'Thông báo',
        };
    }
}
