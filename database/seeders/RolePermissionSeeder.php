<?php

namespace Database\Seeders;

use Filament\Facades\Filament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $panel = Filament::getPanel('admin');
        $guardName = config('auth.defaults.guard', 'web');

        if ($panel) {
            Filament::setCurrentPanel($panel);
            $guardName = $panel->getAuthGuard();
        }

        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--option' => 'permissions',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = Permission::query()
            ->where('guard_name', $guardName)
            ->pluck('name')
            ->all();

        $rolesToKeep = ['super_admin', 'director', 'pm', 'hr'];

        $roles = collect($rolesToKeep)
            ->mapWithKeys(fn (string $roleName): array => [
                $roleName => Role::firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => $guardName,
                ]),
            ]);

        $roles['super_admin']->syncPermissions($allPermissions);

        $roles['director']->syncPermissions($this->permissionsFor($guardName, [
            'Application',
            'Candidate',
            'RecruitmentJob',
        ], [
            'ViewAny',
            'View',
            'Create',
            'Update',
        ], [
            'ViewAny:Branch',
            'View:Branch',
            'ViewAny:Department',
            'View:Department',
            'ViewAny:Workplace',
            'View:Workplace',
        ]));

        $roles['pm']->syncPermissions($this->permissionsFor($guardName, [
            'Application',
            'Candidate',
            'RecruitmentJob',
        ], [
            'ViewAny',
            'View',
            'Create',
            'Update',
        ], [
            'ViewAny:Branch',
            'View:Branch',
            'ViewAny:Department',
            'View:Department',
            'ViewAny:Workplace',
            'View:Workplace',
        ]));

        $roles['hr']->syncPermissions($this->permissionsFor($guardName, [
            'Application',
            'Candidate',
            'RecruitmentJob',
        ], [
            'ViewAny',
            'View',
            'Create',
            'Update',
            'Delete',
            'DeleteAny',
        ], [
            'ViewAny:Branch',
            'View:Branch',
            'ViewAny:Department',
            'View:Department',
            'ViewAny:Workplace',
            'View:Workplace',
            'ViewAny:User',
            'View:User',
        ]));

        Role::query()
            ->where('guard_name', $guardName)
            ->whereNotIn('name', $rolesToKeep)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function permissionsFor(
        string $guardName,
        array $resources,
        array $actions,
        array $extraPermissions = [],
    ): array {
        $expected = collect($resources)
            ->flatMap(fn (string $resource): array => collect($actions)
                ->map(fn (string $action): string => "{$action}:{$resource}")
                ->all())
            ->merge($extraPermissions)
            ->unique()
            ->values()
            ->all();

        return Permission::query()
            ->where('guard_name', $guardName)
            ->whereIn('name', $expected)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}
