<?php

namespace Tests\Feature;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Livewire\Client\Employers\CompanyProfile;
use App\Livewire\Client\Employers\SingleCompany;
use App\Models\Branch;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_director_can_update_own_branch_company_profile(): void
    {
        Storage::fake('public');

        $branch = Branch::query()->create([
            'name' => 'FPT Ho Chi Minh',
            'city' => 'ha_noi',
            'is_active' => true,
        ]);

        $director = User::factory()->create([
            'role' => 'director',
            'branch_id' => $branch->id,
            'is_active' => true,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ]);

        $this->actingAs($director);

        Livewire::test(CompanyProfile::class)
            ->assertSet('canEdit', true)
            ->set('name', 'FPT Careers Ho Chi Minh')
            ->set('city', 'ho_chi_minh')
            ->set('employee_count', 250)
            ->set('description', 'Hiring hub for technology and education teams.')
            ->set('phone', '0901234567')
            ->set('email_contact', 'hr.hcm@fpt.test')
            ->set('address', 'District 9, Ho Chi Minh City')
            ->set('website', 'https://fpt.test')
            ->set('facebook_url', 'https://facebook.com/fptcareers')
            ->set('twitter_url', 'https://twitter.com/fptcareers')
            ->set('linkedin_url', 'https://linkedin.com/company/fptcareers')
            ->set('logo', UploadedFile::fake()->image('logo.png', 240, 120))
            ->call('save')
            ->assertHasNoErrors();

        $branch->refresh();

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'FPT Careers Ho Chi Minh',
            'city' => 'ho_chi_minh',
            'province_code' => '79',
            'employee_count' => 250,
            'email_contact' => 'hr.hcm@fpt.test',
            'website' => 'https://fpt.test',
        ]);

        $this->assertNotNull($branch->image);
        Storage::disk('public')->assertExists($branch->image);
    }

    public function test_hr_cannot_update_company_profile(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Ha Noi',
            'city' => 'ha_noi',
            'is_active' => true,
        ]);

        $hr = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ]);

        $this->actingAs($hr);

        Livewire::test(CompanyProfile::class)
            ->assertSet('canEdit', false)
            ->set('name', 'Changed by HR')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'FPT Ha Noi',
        ]);
    }

    public function test_pm_cannot_update_company_profile(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Da Nang',
            'city' => 'da_nang',
            'is_active' => true,
        ]);

        $pm = User::factory()->create([
            'role' => 'pm',
            'branch_id' => $branch->id,
            'is_active' => true,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ]);

        $this->actingAs($pm);

        Livewire::test(CompanyProfile::class)
            ->assertSet('canEdit', false)
            ->set('name', 'Changed by PM')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'FPT Da Nang',
        ]);
    }

    public function test_company_profile_rejects_invalid_city_phone_and_duplicate_email(): void
    {
        Branch::query()->create([
            'name' => 'Other Branch',
            'city' => 'ha_noi',
            'email_contact' => 'shared@fpt.test',
            'is_active' => true,
        ]);

        $branch = Branch::query()->create([
            'name' => 'FPT Can Tho',
            'city' => 'can_tho',
            'is_active' => true,
        ]);

        $director = User::factory()->create([
            'role' => 'director',
            'branch_id' => $branch->id,
            'is_active' => true,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ]);

        $this->actingAs($director);

        Livewire::test(CompanyProfile::class)
            ->set('city', 'not_a_province')
            ->set('phone', 'abc-phone')
            ->set('email_contact', 'shared@fpt.test')
            ->call('save')
            ->assertHasErrors(['city', 'phone', 'email_contact']);
    }

    public function test_public_company_page_uses_branch_profile_and_published_jobs(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Careers Public',
            'code' => 'PUB',
            'city' => 'ho_chi_minh',
            'province_code' => '79',
            'description' => 'Real company profile description.',
            'employee_count' => 300,
            'phone' => '0901234567',
            'email_contact' => 'public@fpt.test',
            'website' => 'https://fpt.test',
            'facebook_url' => 'https://facebook.com/fptcareers',
            'is_active' => true,
        ]);

        $creator = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        RecruitmentJob::query()->create([
            'title' => 'Public Laravel Developer',
            'slug' => 'public-laravel-developer',
            'description' => 'Build public hiring workflows.',
            'status' => StatusRecruitmentJobsEnum::PUBLISHED->value,
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'salary_range' => ['min' => 15000000, 'max' => 25000000],
            'deadline' => now()->addWeek(),
            'created_by' => $creator->id,
        ]);

        Livewire::test(SingleCompany::class, ['branch' => $branch])
            ->assertSee('FPT Careers Public')
            ->assertSee('Real company profile description.')
            ->assertSee('Public Laravel Developer')
            ->assertSee('public@fpt.test')
            ->assertDontSee('Duis ac augue');
    }
}
