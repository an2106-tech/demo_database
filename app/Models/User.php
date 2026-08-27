<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'branch_id',
    'avatar',
    'is_active',
    'metadata',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;
    use SoftDeletes;

    protected ?int $cachedUnreadNotificationCount = null;

    protected static function booted(): void
    {
        static::saved(function (self $user): void {
            $user->syncAssignedRole();
        });
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        if ($panel->getId() !== 'admin' || ! $this->is_active) {
            return false;
        }

        return in_array($this->role, ['admin', 'director', 'pm', 'hr'], true)
            || $this->hasAnyRole(['super_admin', 'director', 'pm', 'hr']);
    }

    public function syncAssignedRole(): void
    {
        $role = $this->role === 'admin' ? 'super_admin' : $this->role;

        if (! is_string($role) || $role === '') {
            $this->syncRoles([]);

            return;
        }

        $availableRoleNames = \Spatie\Permission\Models\Role::query()
            ->pluck('name')
            ->all();

        if (! in_array($role, $availableRoleNames, true)) {
            $this->syncRoles([]);

            return;
        }

        $this->syncRoles([$role]);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function branchScopeId(): ?int
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        $hasBranchScopedRole = in_array($this->role, ['director', 'pm', 'hr'], true)
            || $this->hasAnyRole(['director', 'pm', 'hr']);

        if ($hasBranchScopedRole && $this->branch_id) {
            return (int) $this->branch_id;
        }

        return null;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function candidate(): HasOne
    {
        return $this->hasOne(Candidate::class, 'user_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function getAvatarUrlAttribute(): string
    {
        if (! $this->avatar) {
            return asset('assets/img/avatar_detail.jpg');
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        return asset('storage/' . ltrim($this->avatar, '/'));
    }

    public function getHasCustomAvatarAttribute(): bool
    {
        return ! empty($this->avatar);
    }

    public function interviewAssignments(): HasMany
    {
        return $this->hasMany(InterviewEvaluator::class);
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function unreadNotificationCount(): int
    {
        if (! $this->exists) {
            return 0;
        }

        return $this->cachedUnreadNotificationCount ??= $this->userNotifications()
            ->whereNull('read_at')
            ->count();
    }
}
