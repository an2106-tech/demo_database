<?php

namespace App\Livewire\Client\Employers;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.employer')]
class CompanyProfile extends Component
{
    public $branch = null;

    public $canEdit = false;

    public string $name = '';

    public string $code = '';

    public ?string $province_code = null;

    public string $city = '';

    public ?int $employee_count = null;

    public ?string $description = null;

    public ?string $phone = null;

    public ?string $email_contact = null;

    public ?string $address = null;

    public ?string $website = null;

    public ?string $facebook_url = null;

    public ?string $twitter_url = null;

    public ?string $linkedin_url = null;

    public int $profileCompletion = 0;

    public array $missingProfileFields = [];

    public function mount(): void
    {
        $user = Auth::user();

        if ($user && in_array($user->role, ['hr', 'director', 'pm'], true)) {
            $this->branch = Branch::with(['workplaces'])
                ->find($user->branch_id);
            $this->canEdit = $user->role === 'director';

            $this->fillFromBranch();
        }
    }

    public function save(): void
    {
        abort_unless($this->canEdit, 403);

        $branch = $this->editableBranch();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'province_code' => ['nullable', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:100'],
            'employee_count' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email_contact' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
        ]);

        $branch->fill($validated);
        $branch->save();

        $this->branch = $branch->fresh(['workplaces']);
        $this->fillFromBranch();

        $this->dispatch('app-notify', message: 'Cập nhật hồ sơ công ty thành công.');
    }

    public function render(): mixed
    {
        return view('livewire.client.employers.company_profile', [
            'branch' => $this->branch,
            'canEdit' => $this->canEdit,
        ]);
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên công ty.',
            'city.required' => 'Vui lòng nhập thành phố.',
            'employee_count.integer' => 'Số lượng nhân sự phải là số.',
            'employee_count.min' => 'Số lượng nhân sự không hợp lệ.',
            'email_contact.email' => 'Email liên hệ không đúng định dạng.',
            '*.url' => 'Liên kết không đúng định dạng URL.',
        ];
    }

    private function fillFromBranch(): void
    {
        if (! $this->branch) {
            return;
        }

        $this->name = (string) $this->branch->name;
        $this->code = (string) ($this->branch->code ?? '');
        $this->province_code = $this->branch->province_code;
        $this->city = (string) $this->branch->city;
        $this->employee_count = $this->branch->employee_count;
        $this->description = $this->branch->description;
        $this->phone = $this->branch->phone;
        $this->email_contact = $this->branch->email_contact;
        $this->address = $this->branch->address;
        $this->website = $this->branch->website;
        $this->facebook_url = $this->branch->facebook_url;
        $this->twitter_url = $this->branch->twitter_url;
        $this->linkedin_url = $this->branch->linkedin_url;

        $this->refreshProfileStatus();
    }

    private function editableBranch(): Branch
    {
        $user = Auth::user();

        abort_unless($user?->branch_id, 404);

        return Branch::query()
            ->whereKey($user->branch_id)
            ->firstOrFail();
    }

    private function refreshProfileStatus(): void
    {
        $fields = [
            'name' => 'tên công ty',
            'city' => 'thành phố',
            'address' => 'địa chỉ',
            'phone' => 'số điện thoại',
            'email_contact' => 'email liên hệ',
            'description' => 'mô tả công ty',
            'website' => 'website',
        ];

        $this->missingProfileFields = collect($fields)
            ->filter(fn (string $label, string $field): bool => blank($this->{$field}))
            ->values()
            ->all();

        $filled = count($fields) - count($this->missingProfileFields);
        $this->profileCompletion = (int) round(($filled / count($fields)) * 100);
    }
}
