<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class AdminUserManagementGuard
{
    public const BRANCH_ROLES = ['hr', 'director', 'pm'];

    /** @return array<string, string> */
    public function roleOptions(User $actor, ?User $target = null): array
    {
        if ($this->isSuperAdmin($actor)) {
            return $this->allRoleOptions();
        }

        if ($target?->is($actor)) {
            return [$target->role => $this->roleLabel($target->role)];
        }

        if ($actor->role === 'director') {
            return [
                'hr' => 'Nhân sự',
                'pm' => 'Quản lý dự án',
            ];
        }

        return $target
            ? [$target->role => $this->roleLabel($target->role)]
            : [];
    }

    /** @return array<int, string> */
    public function branchOptions(User $actor, ?User $target = null): array
    {
        $query = Branch::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($actor->branchScopeId()) {
            $query->whereKey($actor->branchScopeId());
        } elseif ($target?->branch_id) {
            $query->orWhereKey($target->branch_id);
        }

        return $query->pluck('name', 'id')->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalize(User $actor, array $data, ?User $target = null): array
    {
        if ($target?->is($actor)) {
            $data['role'] = $target->role;
            $data['branch_id'] = $target->branch_id;
            $data['is_active'] = $target->is_active;

            return $data;
        }

        $role = (string) ($data['role'] ?? $target?->role ?? '');

        if (! array_key_exists($role, $this->roleOptions($actor, $target))) {
            throw ValidationException::withMessages([
                'role' => 'Bạn không có quyền cấp vai trò này.',
            ]);
        }

        $data['role'] = $role;

        if ($role === 'admin') {
            $data['branch_id'] = null;

            return $data;
        }

        if (! in_array($role, self::BRANCH_ROLES, true)) {
            return $data;
        }

        $branchId = $actor->branchScopeId() ?: ($data['branch_id'] ?? null);

        if (! $branchId) {
            throw ValidationException::withMessages([
                'branch_id' => 'Vui lòng chọn chi nhánh làm việc.',
            ]);
        }

        $branchExists = Branch::query()
            ->whereKey($branchId)
            ->where('is_active', true)
            ->exists();

        if (! $branchExists) {
            throw ValidationException::withMessages([
                'branch_id' => 'Chi nhánh đã chọn không còn hoạt động.',
            ]);
        }

        $data['branch_id'] = (int) $branchId;

        return $data;
    }

    public function isSuperAdmin(User $user): bool
    {
        return $user->role === 'admin' || $user->isSuperAdmin();
    }

    /** @return array<string, string> */
    private function allRoleOptions(): array
    {
        return [
            'admin' => 'Super Admin',
            'director' => 'Giám đốc chi nhánh',
            'hr' => 'Nhân sự',
            'pm' => 'Quản lý dự án',
        ];
    }

    private function roleLabel(string $role): string
    {
        return $this->allRoleOptions()[$role] ?? $role;
    }
}
