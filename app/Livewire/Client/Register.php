<?php

namespace App\Livewire\Client;

use App\Enums\VietnamProvince;
use App\Models\Branch;
use App\Models\User;
use App\Rules\VietnamPhone;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Register extends Component
{
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

    public bool $showRoleTabs = true;

    protected array $queryString = [
        'role' => ['except' => 'candidate'],
        'next_route' => ['except' => null],
    ];

    public function mount(): void
    {
        $routeName = request()->route()?->getName();

        if ($routeName === 'employers.register') {
            $this->role = 'employer';
        } elseif ($routeName === 'candidates.register') {
            $this->role = 'candidate';
        } else {
            $requestedRole = request()->query('role');
            $this->role = $this->normalizeRole(is_string($requestedRole) ? $requestedRole : '');
        }

        $nextRoute = request()->query('next_route');
        $this->next_route = is_string($nextRoute) && $nextRoute !== '' ? $nextRoute : null;

        $authUser = Auth::user();
        if (! $authUser) {
            $this->showRoleTabs = true;

            return;
        }

        $this->showRoleTabs = false;

        if ($routeName && $this->shouldLeaveRegister($authUser)) {
            $this->redirectAuthenticatedUser($authUser);

            return;
        }

        if ($this->role === 'employer') {
            $this->name = trim((string) $authUser->name);
            $this->email = trim((string) $authUser->email);
            $this->phone = trim((string) ((is_array($authUser->metadata) ? ($authUser->metadata['phone'] ?? '') : '') ?: ''));
            $this->province = trim((string) ((is_array($authUser->metadata) ? ($authUser->metadata['province'] ?? '') : '') ?: ''));
            $this->address = trim((string) ((is_array($authUser->metadata) ? ($authUser->metadata['address'] ?? '') : '') ?: ''));
        }
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
        if ($authUser && $this->shouldLeaveRegister($authUser)) {
            return $this->redirectAuthenticatedUser($authUser);
        }

        if ($authUser && $this->role === 'candidate') {
            app(CandidateAccountService::class)->activateFor($authUser);

            return $this->redirectAfterActivation();
        }

        if ($authUser && $this->role === 'employer') {
            $data = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:20', new VietnamPhone()],
                'branch_id' => ['required', 'integer', 'exists:branches,id'],
                'province' => ['required', 'string', 'max:255', 'in:' . implode(',', array_keys(VietnamProvince::options()))],
                'address' => ['required', 'string', 'max:255'],
                'terms_accepted' => ['accepted'],
            ]);

            if (! Branch::query()
                ->whereKey($data['branch_id'])
                ->where('city', $data['province'])
                ->where('is_active', true)
                ->exists()) {
                $this->addError('branch_id', 'Chi nhánh không thuộc tỉnh/thành đã chọn.');

                return null;
            }

            $metadata = is_array($authUser->metadata) ? $authUser->metadata : [];
            $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];
            $accountTypes[] = 'employer';
            if (app(CandidateAccountService::class)->hasCandidateAccount($authUser)) {
                $accountTypes[] = 'candidate';
            }

            $metadata['account_types'] = array_values(array_unique(array_filter($accountTypes, 'is_string')));
            $metadata['account_type'] = 'employer';
            $metadata['phone'] = trim($data['phone']);
            $metadata['province'] = trim($data['province']);
            $metadata['address'] = trim($data['address']);
            $preservePrivilegedRole = in_array($authUser->role, ['admin', 'director'], true);

            if (! $preservePrivilegedRole) {
                $metadata['approval_status'] = 'pending';
                $metadata['requested_at'] = now()->toISOString();
            }

            $authUser->fill([
                'name' => trim($data['name']),
                'role' => $preservePrivilegedRole ? $authUser->role : 'hr',
                'branch_id' => $data['branch_id'],
                'is_active' => $preservePrivilegedRole,
                'metadata' => $metadata,
            ]);
            $authUser->save();

            if (! $preservePrivilegedRole) {
                Auth::logout();

                if (request()->hasSession()) {
                    request()->session()->invalidate();
                    request()->session()->regenerateToken();
                }

                return redirect()
                    ->route('employers.login')
                    ->with('status', 'Yêu cầu nhà tuyển dụng đã được gửi duyệt. Vui lòng chờ quản trị viên kích hoạt.');
            }

            session(['client_menu_type' => 'employer']);

            return redirect()
                ->route('employers.dashboard')
                ->with('status', 'Tài khoản nhà tuyển dụng đã được kích hoạt trên tài khoản hiện tại.');
        }

        if (! in_array($this->role, ['candidate', 'employer'], true)) {
            $this->role = 'candidate';
        }

        $existing = User::query()->where('email', $this->email)->first();
        if (! $authUser && $existing && $this->role === 'candidate' && $existing->role === 'hr') {
            $this->addError('email', 'Email này đã có tài khoản HR. Vui lòng đăng nhập cổng nhà tuyển dụng rồi kích hoạt thêm hồ sơ ứng viên.');

            return null;
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', new VietnamPhone()],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms_accepted' => ['accepted'],
        ];

        if ($this->role === 'employer') {
            $rules['branch_id'] = ['required', 'integer', 'exists:branches,id'];
            $rules['province'] = ['required', 'string', 'max:255', 'in:' . implode(',', array_keys(VietnamProvince::options()))];
            $rules['address'] = ['required', 'string', 'max:255'];
        }

        $data = $this->validate($rules);

        if ($this->role === 'employer' && ! Branch::query()
            ->whereKey($data['branch_id'])
            ->where('city', $data['province'])
            ->where('is_active', true)
            ->exists()) {
            $this->addError('branch_id', 'Chi nhánh không thuộc tỉnh/thành đã chọn.');

            return null;
        }

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
                    'approval_status' => 'pending',
                    'requested_at' => now()->toISOString(),
                ],
            ]);
        }

        if ($this->role === 'employer') {
            return redirect()
                ->route('employers.login')
                ->with('status', 'Tài khoản nhà tuyển dụng đã được gửi duyệt. Vui lòng chờ quản trị viên kích hoạt.');
        }

        Auth::login($user);
        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        app(CandidateAccountService::class)->resolveFor($user);

        return redirect()
            ->route('candidates.candidate_profile')
            ->with('status', 'Hoàn thiện hồ sơ ứng viên trước khi ứng tuyển.');
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

        return redirect()
            ->route('candidates.candidate_profile')
            ->with('status', 'Hoàn thiện hồ sơ ứng viên trước khi ứng tuyển.');
    }

    private function shouldLeaveRegister(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($this->role === 'candidate') {
            return ! in_array($user->role, ['hr'], true);
        }

        if ($this->role === 'employer') {
            return ! in_array($user->role, ['candidate'], true);
        }

        return true;
    }

    private function redirectAuthenticatedUser(User $user): mixed
    {
        if ($user->role === 'admin') {
            session()->forget('client_menu_type');

            return $this->redirect('/admin');
        }

        if (in_array($user->role, ['director', 'hr', 'pm'], true)) {
            app(CandidateAccountService::class)->setPreferredAccountType($user, 'employer');
            session(['client_menu_type' => 'employer']);

            return $this->redirect(route('employers.dashboard'));
        }

        app(CandidateAccountService::class)->setPreferredAccountType($user, 'candidate');
        session(['client_menu_type' => 'candidate']);

        return $this->redirect(route('candidates.candidate_dashboard'));
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.max' => 'Họ và tên không được vượt quá :max ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.max' => 'Số điện thoại không được vượt quá :max ký tự.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'branch_id.required' => 'Vui lòng chọn chi nhánh.',
            'branch_id.exists' => 'Chi nhánh không hợp lệ.',
            'province.required' => 'Vui lòng chọn tỉnh/thành.',
            'province.in' => 'Tỉnh/thành không hợp lệ.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'address.max' => 'Địa chỉ không được vượt quá :max ký tự.',
            'terms_accepted.accepted' => 'Vui lòng chấp nhận điều khoản.',
        ];
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
            'showRoleTabs' => $this->showRoleTabs,
            'isAuthenticated' => Auth::check(),
        ])->layout('layouts.auth', [
            'authTitle' => 'Đăng ký',
            'authContextRole' => $this->role,
        ]);
    }
}
