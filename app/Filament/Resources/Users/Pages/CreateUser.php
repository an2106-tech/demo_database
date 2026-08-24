<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\AdminUserManagementGuard;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return app(AdminUserManagementGuard::class)->normalize(Auth::user(), $data);
    }

    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->record;

        $user->syncRoles($user->role === 'admin' ? ['super_admin'] : [$user->role]);
    }
}
