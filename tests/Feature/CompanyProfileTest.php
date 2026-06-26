<?php

namespace Tests\Feature;

use App\Livewire\Client\Employers\CompanyProfile;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_director_can_update_own_branch_company_profile(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Ho Chi Minh',
            'city' => 'ho_chi_minh',
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
            ->set('city', 'Ho Chi Minh')
            ->set('province_code', 'HCM')
            ->set('employee_count', 250)
            ->set('description', 'Hiring hub for technology and education teams.')
            ->set('phone', '02812345678')
            ->set('email_contact', 'hr.hcm@fpt.test')
            ->set('address', 'District 9, Ho Chi Minh City')
            ->set('website', 'https://fpt.test')
            ->set('facebook_url', 'https://facebook.com/fptcareers')
            ->set('twitter_url', 'https://twitter.com/fptcareers')
            ->set('linkedin_url', 'https://linkedin.com/company/fptcareers')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'FPT Careers Ho Chi Minh',
            'city' => 'Ho Chi Minh',
            'province_code' => 'HCM',
            'employee_count' => 250,
            'email_contact' => 'hr.hcm@fpt.test',
            'website' => 'https://fpt.test',
        ]);
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
}
