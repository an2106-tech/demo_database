<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class RecruitmentDashboardContext
{
    private ?string $resolvedRole = null;

    public function __construct(private readonly ?User $user) {}

    public static function current(): self
    {
        /** @var User|null $user */
        $user = Auth::user();

        return new self($user);
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function role(): string
    {
        if ($this->resolvedRole !== null) {
            return $this->resolvedRole;
        }

        $rawRole = $this->user?->role;

        if ($rawRole === 'admin') {
            return $this->resolvedRole = 'super_admin';
        }

        if (in_array($rawRole, ['hr', 'director', 'pm'], true)) {
            return $this->resolvedRole = $rawRole;
        }

        if ($this->user?->isSuperAdmin()) {
            return $this->resolvedRole = 'super_admin';
        }

        foreach (['hr', 'director', 'pm'] as $role) {
            if ($this->user?->hasRole($role)) {
                return $this->resolvedRole = $role;
            }
        }

        return $this->resolvedRole = 'unknown';
    }

    public function branchId(): ?int
    {
        if ($this->role() === 'super_admin') {
            return null;
        }

        return $this->is('hr', 'director', 'pm') && $this->user?->branch_id
            ? (int) $this->user->branch_id
            : null;
    }

    public function is(string ...$roles): bool
    {
        return in_array($this->role(), $roles, true);
    }

    public function isPm(): bool
    {
        return $this->is('pm');
    }

    public function scopeLabel(): string
    {
        return match ($this->role()) {
            'super_admin' => 'Dữ liệu tuyển dụng trên toàn hệ thống',
            'director' => 'Các nội dung cần xem xét tại chi nhánh',
            'pm' => 'Các buổi phỏng vấn được phân công cho bạn',
            default => 'Công việc tuyển dụng tại chi nhánh',
        };
    }
}
