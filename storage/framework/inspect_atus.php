<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::where('email', 'like', '%atus%')->orWhere('name', 'like', '%atus%')->first();
if (! $user) {
    echo "not-found" . PHP_EOL;
    exit;
}
echo 'id=' . $user->id . PHP_EOL;
echo 'name=' . $user->name . PHP_EOL;
echo 'email=' . $user->email . PHP_EOL;
echo 'db_role=' . $user->role . PHP_EOL;
echo 'spatie_roles=' . implode(',', $user->roles->pluck('name')->all()) . PHP_EOL;
echo 'can_panel=' . ($user->canAccessPanel(Filament\Facades\Filament::getPanel('admin')) ? 'yes' : 'no') . PHP_EOL;
echo 'perm_app=' . ($user->can('ViewAny:Application') ? 'yes' : 'no') . PHP_EOL;
echo 'perm_job=' . ($user->can('ViewAny:RecruitmentJob') ? 'yes' : 'no') . PHP_EOL;
echo 'perm_user=' . ($user->can('ViewAny:User') ? 'yes' : 'no') . PHP_EOL;
