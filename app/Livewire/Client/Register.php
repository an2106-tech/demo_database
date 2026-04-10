<?php

namespace App\Livewire\Client;

use App\Enums\VietnamProvince;
use App\Models\Branch;
use App\Models\User;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Register extends Component
{
    #[Layout('layouts.client')]
    public string $role = 'candidate';

    public ?string $next_route = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?int $branch_id = null;

    public string $phone = '';

    public bool $terms_accepted = false;

    public string $province = '';

    public string $address = '';

    protected array $queryString = [
        'role' => ['except' => 'candidate'],
        'next_route' => ['except' => null],
    ];

    public function mount(): void
    {
        $requestedRole = request()->query('role');
        $this->role = $this->normalizeRole(is_string($requestedRole) ? $requestedRole : '');

        $nextRoute = request()->query('next_route');
        $this->next_route = is_string($nextRoute) && $nextRoute !== '' ? $nextRoute : null;
    }

    public function setRole(string $role): void
    {
        $this->resetErrorBag();
        $this->role = $this->normalizeRole($role);

        if ($this->role === 'candidate') {
            $this->province = '';
            $this->address = '';
            $this->branch_id = null;
        }
    }

    public function updatedProvince(string $value): void
    {
        $this->province = trim($value);
        $this->branch_id = null;
    }

    private function normalizeRole(string $role): string
    {
        $role = strtolower(trim($role));

        if ($role === 'hr') {
            return 'employer';
        }

        return in_array($role, ['candidate', 'employer'], true) ? $role : 'candidate';
    }

    public function register(): mixed
    {
        $authUser = Auth::user();
        if ($authUser && $this->role === 'candidate') {
            app(CandidateAccountService::class)->activateFor($authUser);

            session(['client_menu_type' => 'candidate']);

            return $this->redirectAfterActivation();
        }

        if (! in_array($this->role, ['candidate', 'employer'], true)) {
            $this->role = 'candidate';
        }

        $existing = User::query()->where('email', $this->email)->first();
        if (! $authUser && $existing && $this->role === 'candidate' && $existing->role === 'hr') {
            $this->addError('email', 'Email này đã có tài khoản HR. Vui lòng đăng nhập và kích hoạt tài khoản ứng viên.');

            return null;
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms_accepted' => ['accepted'],
        ];

        if ($this->role === 'employer') {
            $rules['branch_id'] = ['required', 'integer', 'exists:branches,id'];
            $rules['province'] = ['required', 'string', 'max:255'];
            $rules['address'] = ['required', 'string', 'max:255'];
        }

        $data = $this->validate($rules);

        if ($this->role === 'candidate') {
            $user = User::create([
                'name' => trim($data['name']),
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'candidate',
                'branch_id' => null,
                'avatar' => null,
                'is_active' => true,
                'metadata' => [
                    'account_type' => 'candidate',
                    'account_types' => ['candidate'],
                    'phone' => trim($data['phone']),
                ],
            ]);
        } else {
            $user = User::create([
                'name' => trim($data['name']),
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'hr',
                'branch_id' => $data['branch_id'],
                'avatar' => null,
                'is_active' => false,
                'metadata' => [
                    'account_type' => 'employer',
                    'account_types' => ['employer'],
                    'phone' => trim($data['phone']),
                    'province' => trim($data['province']),
                    'address' => trim($data['address']),
                ],
            ]);
        }

        if ($this->role === 'employer') {
            return redirect()
                ->route('auth.login', ['role' => 'employer'])
                ->with('status', 'Tài khoản nhà tuyển dụng đã được tạo và đang chờ super admin duyệt.');
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('home');
    }

    private function redirectAfterActivation(): mixed
    {
        $allowed = [
            'candidates.candidate_dashboard',
            'candidates.candidate_profile',
            'candidates.messages',
            'candidates.manage_jobs',
            'candidates.earnings',
            'candidates.change_password',
        ];

        if ($this->next_route && in_array($this->next_route, $allowed, true)) {
            return redirect()->route($this->next_route);
        }

        return redirect()->route('home');
    }

    public function render()
    {
        $provinceOptions = VietnamProvince::options();

        $branchesQuery = Branch::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($this->province !== '') {
            $branchesQuery->where('city', $this->province);
        } else {
            $branchesQuery->whereRaw('1=0');
        }

        $branches = $branchesQuery->get(['id', 'name', 'city']);

        return view('livewire.client.pages.register', [
            'branches' => $branches,
            'provinceOptions' => $provinceOptions,
        ]);
    }
}
