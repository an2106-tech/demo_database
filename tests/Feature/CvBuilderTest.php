<?php

namespace Tests\Feature;

use App\Livewire\Client\CvBuilder;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class CvBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_save_cv_content_and_selected_online_template(): void
    {
        [$user, $candidate] = $this->candidateAccount();

        $this->actingAs($user);

        Livewire::withQueryParams(['template' => 'ats-classic'])
            ->test(CvBuilder::class)
            ->assertSet('selectedTemplate', 'ats-classic')
            ->set('name', 'Nguyễn Minh Khang')
            ->set('email', 'minh-khang@example.com')
            ->set('profile_title', 'Giảng viên Công nghệ thông tin')
            ->set('career_objective', 'Phát triển môi trường học tập thực tiễn.')
            ->set('skills', [['name' => 'Laravel', 'level' => 'Thành thạo']])
            ->call('save')
            ->assertHasNoErrors()
            ->assertNotSet('lastSavedAt', null)
            ->assertDispatched('app-notify');

        $resume = CandidateResume::query()->where('candidate_id', $candidate->id)->firstOrFail();
        $candidate->refresh();

        $this->assertSame('Giảng viên Công nghệ thông tin', $resume->profile_title);
        $this->assertSame('ats-classic', data_get($resume->extra, 'builder_template'));
        $this->assertSame('online', data_get($candidate->metadata, 'primary_cv.type'));
        $this->assertSame('ats-classic', data_get($candidate->metadata, 'primary_cv.template'));
    }

    public function test_invalid_cv_fields_report_errors_and_do_not_start_download(): void
    {
        [$user] = $this->candidateAccount();

        $this->actingAs($user);

        Livewire::test(CvBuilder::class)
            ->set('email', 'email-khong-hop-le')
            ->set('profile_title', '')
            ->call('downloadPdf')
            ->assertHasErrors(['email', 'profile_title'])
            ->assertDispatched('cv-action-failed')
            ->assertNotDispatched('download-cv-file');
    }

    public function test_open_and_download_actions_save_then_dispatch_pdf_urls(): void
    {
        [$user] = $this->candidateAccount();

        $this->actingAs($user);

        Livewire::test(CvBuilder::class)
            ->call('setTemplate', 'tech-executive')
            ->call('openPdf')
            ->assertHasNoErrors()
            ->assertDispatched('open-pdf-window')
            ->call('downloadPdf')
            ->assertHasNoErrors()
            ->assertDispatched('download-cv-file');
    }

    public function test_editing_online_cv_does_not_replace_an_attachment_primary_cv(): void
    {
        [$user, $candidate] = $this->candidateAccount([
            'metadata' => [
                'primary_cv' => [
                    'type' => 'attachment',
                    'attachment_id' => 99,
                    'title' => 'CV đã tải lên',
                ],
            ],
        ]);

        $this->actingAs($user);

        Livewire::test(CvBuilder::class)
            ->call('setTemplate', 'tech-executive')
            ->call('save')
            ->assertHasNoErrors();

        $candidate->refresh();
        $resume = CandidateResume::query()->where('candidate_id', $candidate->id)->firstOrFail();

        $this->assertSame('attachment', data_get($candidate->metadata, 'primary_cv.type'));
        $this->assertSame(99, data_get($candidate->metadata, 'primary_cv.attachment_id'));
        $this->assertSame('tech-executive', data_get($resume->extra, 'builder_template'));
    }

    public function test_builder_uses_the_current_primary_online_template_before_the_last_edited_template(): void
    {
        [$user, $candidate] = $this->candidateAccount([
            'metadata' => [
                'primary_cv' => [
                    'type' => 'online',
                    'template' => 'ats-classic',
                ],
            ],
        ]);

        CandidateResume::query()
            ->where('candidate_id', $candidate->id)
            ->update(['extra' => ['builder_template' => 'fpt-modern']]);

        $this->actingAs($user);

        Livewire::test(CvBuilder::class)
            ->assertSet('selectedTemplate', 'ats-classic');
    }

    public function test_candidate_id_cannot_be_changed_from_the_livewire_client(): void
    {
        [$user] = $this->candidateAccount();
        $otherCandidate = Candidate::query()->create([
            'name' => 'Ứng viên khác',
            'email' => 'other-candidate@example.com',
        ]);

        $this->actingAs($user);
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(CvBuilder::class)
            ->set('candidateId', $otherCandidate->id);
    }

    public function test_failed_save_removes_the_new_avatar_and_keeps_the_current_avatar(): void
    {
        Storage::fake('public');
        [$user] = $this->candidateAccount();
        $user->forceFill(['avatar' => 'users/current-avatar.jpg'])->save();
        Storage::disk('public')->put('users/current-avatar.jpg', 'current-avatar');

        $this->actingAs($user);
        $component = Livewire::test(CvBuilder::class)
            ->set('avatar', UploadedFile::fake()->image('new-avatar.jpg'));

        DB::partialMock()
            ->shouldReceive('transaction')
            ->once()
            ->andThrow(new RuntimeException('Simulated database failure.'));

        $component
            ->call('save')
            ->assertHasErrors('save')
            ->assertDispatched('cv-action-failed');

        $this->assertSame('users/current-avatar.jpg', $user->fresh()->avatar);
        Storage::disk('public')->assertExists('users/current-avatar.jpg');
        $this->assertSame(
            ['users/current-avatar.jpg'],
            Storage::disk('public')->allFiles('users'),
        );
    }

    public function test_candidate_can_stream_each_supported_online_cv_template(): void
    {
        [$user] = $this->candidateAccount();

        $this->actingAs($user);

        foreach (['fpt-modern', 'ats-classic', 'tech-executive'] as $template) {
            $this->get(route('candidates.cv.download', [
                'template' => $template,
                'mode' => 'stream',
            ]))
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');
        }
    }

    /**
     * @return array{User, Candidate}
     */
    private function candidateAccount(array $candidateAttributes = []): array
    {
        $user = User::factory()->create([
            'name' => 'Ứng viên CV Builder',
            'email' => 'cv-builder@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);
        $candidate = Candidate::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0901234567',
        ], $candidateAttributes));

        CandidateResume::query()->create([
            'candidate_id' => $candidate->id,
            'profile_title' => 'Lập trình viên Laravel',
            'career_objective' => 'Xây dựng sản phẩm tuyển dụng hữu ích.',
        ]);

        return [$user, $candidate];
    }
}
