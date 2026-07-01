<?php

namespace Tests\Feature;

use App\Livewire\Client\Notifications;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_only_sees_their_own_notifications(): void
    {
        [$user, $otherUser] = $this->makeCandidateUsers();

        UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'account',
            'data' => [
                'title' => 'Own notification',
                'message' => 'Only this candidate can see this.',
            ],
        ]);

        UserNotification::query()->create([
            'user_id' => $otherUser->id,
            'type' => 'account',
            'data' => [
                'title' => 'Other notification',
                'message' => 'This belongs to another user.',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('candidates.notifications'))
            ->assertOk()
            ->assertSee('Own notification')
            ->assertDontSee('Other notification')
            ->assertSee(route('candidates.notifications'), false);
    }

    public function test_user_can_mark_only_their_notification_as_read(): void
    {
        [$user, $otherUser] = $this->makeCandidateUsers();

        $ownNotification = UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'account',
            'data' => ['title' => 'Read me'],
        ]);

        $otherNotification = UserNotification::query()->create([
            'user_id' => $otherUser->id,
            'type' => 'account',
            'data' => ['title' => 'Do not read me'],
        ]);

        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->assertSet('unreadCount', 1)
            ->call('markAsRead', $ownNotification->id)
            ->assertSet('unreadCount', 0);

        $this->assertNotNull($ownNotification->refresh()->read_at);
        $this->assertNull($otherNotification->refresh()->read_at);
    }

    public function test_mark_all_as_read_only_updates_current_user_notifications(): void
    {
        [$user, $otherUser] = $this->makeCandidateUsers();

        UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'account',
            'data' => ['title' => 'First'],
        ]);

        UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'account',
            'data' => ['title' => 'Second'],
        ]);

        $otherNotification = UserNotification::query()->create([
            'user_id' => $otherUser->id,
            'type' => 'account',
            'data' => ['title' => 'Other'],
        ]);

        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->assertSet('unreadCount', 2)
            ->call('markAllAsRead')
            ->assertSet('unreadCount', 0);

        $this->assertSame(0, UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count());
        $this->assertNull($otherNotification->refresh()->read_at);
    }

    public function test_employer_can_open_notification_center(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Notification Branch',
            'code' => 'NOTI',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ]);

        UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'job_pending_approval',
            'data' => [
                'title' => 'Employer notification',
                'message' => 'Employer account update.',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('employers.notifications'))
            ->assertOk()
            ->assertSee('Employer notification')
            ->assertSee(route('employers.notifications'), false);
    }

    private function makeCandidateUsers(): array
    {
        $user = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        Candidate::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
        ]);

        $otherUser = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        Candidate::query()->create([
            'user_id' => $otherUser->id,
            'name' => $otherUser->name,
            'email' => $otherUser->email,
            'phone' => '0907654321',
            'cv_file' => 'candidates/other/cv.pdf',
        ]);

        return [$user, $otherUser];
    }
}
