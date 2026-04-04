<?php

namespace App\Livewire\Client\pages;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Register extends Component
{
    #[Layout('layouts.client')]
    public string $role = 'candidate';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?int $branch_id = null;

    public string $phone = '';
    public bool $terms_accepted = false;

    protected array $queryString = [
        'role' => ['except' => 'candidate'],
    ];

    public function mount(): void
    {
        $r = request()->query('role');
        $this->role = $this->normalizeRole(is_string($r) ? $r : '');
    }

    public function setRole(string $role): void
    {
        $this->resetErrorBag();
        $this->role = $this->normalizeRole($role);
    }

    private function normalizeRole(string $role): string
    {
        $role = strtolower(trim($role));

        // Backward-compat: allow old querystring `role=hr`
        if ($role === 'hr') {
            return 'employer';
        }

        return in_array($role, ['candidate', 'employer'], true) ? $role : 'candidate';
    }

    public function register(): mixed
    {
        if (! in_array($this->role, ['candidate', 'employer'], true)) {
            $this->role = 'candidate';
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
                'is_active' => true,
                'metadata' => [
                    'account_type' => 'employer',
                    'phone' => trim($data['phone']),
                ],
            ]);
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('home');
    }

    /**
     * @return array<string, mixed>
     */


    public function render()
    {
        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'city']);

        return view('livewire.client.pages.register', [
            'branches' => $branches,
        ]);
    }
}
