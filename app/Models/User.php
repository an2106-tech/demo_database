<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    use HasRoles;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saved(function (self $user): void {
            $user->syncAssignedRole();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    use HasRoles;
    use SoftDeletes;

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->is_active && $this->roles()->exists();
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

    public function branchScopeId(): ?int
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        if ($this->hasAnyRole(['director', 'pm', 'hr']) && $this->branch_id) {
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
}
