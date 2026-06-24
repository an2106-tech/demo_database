<?php

namespace App\Livewire\Client;

use App\Models\CandidateResume;
use App\Models\Candidate;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class CandidateProfile extends Component
{
    use WithFileUploads;

    public int $candidateId;

    public string $name = '';

    public string $email = '';

    public ?string $phone = null;

    public ?int $experience_years = null;

    public $avatar = null;

    public $cv = null;

    public ?string $profile_title = null;

    public array $personal_info = [
        'date_of_birth' => null,
        'gender' => null,
        'country' => null,
        'city' => null,
        'address' => null,
        'website' => null,
        'linkedin' => null,
    ];

    public ?string $career_objective = null;

    public array $desired_job = [
        'position' => null,
        'level' => null,
        'workplace' => null,
        'expected_salary' => null,
        'location' => null,
    ];

    public array $experiences = [];

    public array $educations = [];

    public array $certifications = [];

    public array $languages = [];

    public array $skills = [];

    public array $achievements = [];

    public array $activities = [];

    public array $references = [];

    public ?string $extra = null;

    public string $activeSection = 'personal-info';

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $candidate = app(CandidateAccountService::class)->resolveFor($user);

        $this->candidateId = $candidate->id;
        $this->name = (string) $candidate->name;
        $this->email = (string) ($candidate->email ?? $user->email);
        $this->phone = $candidate->phone;
        $this->experience_years = $candidate->experience_years;

        $resume = CandidateResume::query()->firstOrCreate(
            ['candidate_id' => $candidate->id],
            [],
        );

        $this->profile_title = $resume->profile_title;
        $this->career_objective = $resume->career_objective;
        $this->personal_info = array_merge($this->personal_info, $resume->personal_info ?? []);
        $this->desired_job = array_merge($this->desired_job, $resume->desired_job ?? []);

        $this->experiences = is_array($resume->experiences) ? $resume->experiences : [];
        $this->educations = is_array($resume->educations) ? $resume->educations : [];
        $this->certifications = is_array($resume->certifications) ? $resume->certifications : [];
        $this->languages = is_array($resume->languages) ? $resume->languages : [];
        $this->skills = is_array($resume->skills) ? $resume->skills : [];
        $this->achievements = is_array($resume->achievements) ? $resume->achievements : [];
        $this->activities = is_array($resume->activities) ? $resume->activities : [];
        $this->references = is_array($resume->references) ? $resume->references : [];
        $this->extra = is_array($resume->extra) ? ($resume->extra['text'] ?? null) : null;
    }

    public function save(): void
    {
        $this->saveSection();
    }

    public function saveSection(?string $nextSection = null): void
    {
        // Validation logic remains same as original save()
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'avatar' => ['nullable', 'image', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            'cv' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx'],

            'profile_title' => ['nullable', 'string', 'max:255'],
            'personal_info' => ['array'],
            'personal_info.date_of_birth' => ['nullable', 'date'],
            'personal_info.gender' => ['nullable', 'string', 'max:50'],
            'personal_info.country' => ['nullable', 'string', 'max:100'],
            'personal_info.city' => ['nullable', 'string', 'max:100'],
            'personal_info.address' => ['nullable', 'string', 'max:255'],
            'personal_info.website' => ['nullable', 'string', 'max:255'],
            'personal_info.linkedin' => ['nullable', 'string', 'max:255'],

            'career_objective' => ['nullable', 'string', 'max:4000'],

            'desired_job' => ['array'],
            'desired_job.position' => ['nullable', 'string', 'max:255'],
            'desired_job.level' => ['nullable', 'string', 'max:100'],
            'desired_job.workplace' => ['nullable', 'string', 'max:100'],
            'desired_job.expected_salary' => ['nullable', 'string', 'max:100'],
            'desired_job.location' => ['nullable', 'string', 'max:255'],

            'experiences' => ['array'],
            'experiences.*.company' => ['nullable', 'string', 'max:255'],
            'experiences.*.position' => ['nullable', 'string', 'max:255'],
            'experiences.*.from' => ['nullable', 'string', 'max:50'],
            'experiences.*.to' => ['nullable', 'string', 'max:50'],
            'experiences.*.description' => ['nullable', 'string', 'max:4000'],

            'educations' => ['array'],
            'educations.*.school' => ['nullable', 'string', 'max:255'],
            'educations.*.degree' => ['nullable', 'string', 'max:255'],
            'educations.*.from' => ['nullable', 'string', 'max:50'],
            'educations.*.to' => ['nullable', 'string', 'max:50'],
            'educations.*.description' => ['nullable', 'string', 'max:4000'],

            'certifications' => ['array'],
            'certifications.*.name' => ['nullable', 'string', 'max:255'],
            'certifications.*.issuer' => ['nullable', 'string', 'max:255'],
            'certifications.*.date' => ['nullable', 'string', 'max:50'],
            'certifications.*.description' => ['nullable', 'string', 'max:2000'],

            'languages' => ['array'],
            'languages.*.name' => ['nullable', 'string', 'max:100'],
            'languages.*.level' => ['nullable', 'string', 'max:100'],

            'skills' => ['array'],
            'skills.*.name' => ['nullable', 'string', 'max:100'],
            'skills.*.level' => ['nullable', 'string', 'max:100'],

            'achievements' => ['array'],
            'achievements.*.title' => ['nullable', 'string', 'max:255'],
            'achievements.*.date' => ['nullable', 'string', 'max:50'],
            'achievements.*.description' => ['nullable', 'string', 'max:2000'],

            'activities' => ['array'],
            'activities.*.title' => ['nullable', 'string', 'max:255'],
            'activities.*.from' => ['nullable', 'string', 'max:50'],
            'activities.*.to' => ['nullable', 'string', 'max:50'],
            'activities.*.description' => ['nullable', 'string', 'max:2000'],

            'references' => ['array'],
            'references.*.name' => ['nullable', 'string', 'max:255'],
            'references.*.company' => ['nullable', 'string', 'max:255'],
            'references.*.position' => ['nullable', 'string', 'max:255'],
            'references.*.phone' => ['nullable', 'string', 'max:50'],
            'references.*.email' => ['nullable', 'string', 'max:255'],
            'references.*.note' => ['nullable', 'string', 'max:2000'],

            'extra' => ['nullable', 'string', 'max:4000'],
        ]);

        $candidate = Candidate::query()->findOrFail($this->candidateId);
        $user = Auth::user();
        $resume = CandidateResume::query()->firstOrCreate(['candidate_id' => $candidate->id], []);

        DB::transaction(function () use ($candidate, $resume, $user) {
            $candidate->name = trim($this->name);
            $candidate->email = trim($this->email);
            $candidate->phone = $this->phone;
            $candidate->experience_years = $this->experience_years;

            if ($this->avatar && $user) {
                $oldAvatar = $user->avatar;
                $avatarPath = $this->avatar->storePublicly("users/{$user->id}/avatar", 'public');

                $user->avatar = $avatarPath;
                $user->save();

                if ($oldAvatar && $oldAvatar !== $avatarPath && Storage::disk('public')->exists($oldAvatar)) {
                    Storage::disk('public')->delete($oldAvatar);
                }
            }

            if ($this->cv) {
                $path = $this->cv->storePublicly("candidates/{$candidate->id}/cv", 'public');

                $candidate->cv_file = $path;

                $candidate->attachments()
                    ->where('type', 'cv')
                    ->delete();

                $candidate->attachments()->create([
                    'path' => $path,
                    'type' => 'cv',
                    'original_filename' => method_exists($this->cv, 'getClientOriginalName')
                        ? $this->cv->getClientOriginalName()
                        : null,
                    'mime_type' => method_exists($this->cv, 'getMimeType')
                        ? $this->cv->getMimeType()
                        : null,
                    'size_bytes' => method_exists($this->cv, 'getSize')
                        ? $this->cv->getSize()
                        : null,
                ]);
            }

            $candidate->save();

            $resume->fill([
                'profile_title' => $this->profile_title,
                'personal_info' => $this->personal_info,
                'career_objective' => $this->career_objective,
                'desired_job' => $this->desired_job,
                'experiences' => $this->experiences,
                'educations' => $this->educations,
                'certifications' => $this->certifications,
                'languages' => $this->languages,
                'skills' => $this->skills,
                'achievements' => $this->achievements,
                'activities' => $this->activities,
                'references' => $this->references,
                'extra' => ['text' => $this->extra],
            ]);
            $resume->save();
        });

        $this->avatar = null;
        $this->cv = null;
        
        if ($nextSection) {
            $this->activeSection = $nextSection;
        }

        $this->dispatch('app-notify', message: 'Cập nhật thành công.');
    }

    public function addExperience(): void
    {
        $this->experiences[] = ['company' => null, 'position' => null, 'from' => null, 'to' => null, 'description' => null];
    }

    public function removeExperience(int $index): void
    {
        unset($this->experiences[$index]);
        $this->experiences = array_values($this->experiences);
    }

    public function addEducation(): void
    {
        $this->educations[] = ['school' => null, 'degree' => null, 'from' => null, 'to' => null, 'description' => null];
    }

    public function removeEducation(int $index): void
    {
        unset($this->educations[$index]);
        $this->educations = array_values($this->educations);
    }

    public function addCertification(): void
    {
        $this->certifications[] = ['name' => null, 'issuer' => null, 'date' => null, 'description' => null];
    }

    public function removeCertification(int $index): void
    {
        unset($this->certifications[$index]);
        $this->certifications = array_values($this->certifications);
    }

    public function addLanguage(): void
    {
        $this->languages[] = ['name' => null, 'level' => null];
    }

    public function removeLanguage(int $index): void
    {
        unset($this->languages[$index]);
        $this->languages = array_values($this->languages);
    }

    public function addSkill(): void
    {
        $this->skills[] = ['name' => null, 'level' => null];
    }

    public function removeSkill(int $index): void
    {
        unset($this->skills[$index]);
        $this->skills = array_values($this->skills);
    }

    public function addAchievement(): void
    {
        $this->achievements[] = ['title' => null, 'date' => null, 'description' => null];
    }

    public function removeAchievement(int $index): void
    {
        unset($this->achievements[$index]);
        $this->achievements = array_values($this->achievements);
    }

    public function addActivity(): void
    {
        $this->activities[] = ['title' => null, 'from' => null, 'to' => null, 'description' => null];
    }

    public function removeActivity(int $index): void
    {
        unset($this->activities[$index]);
        $this->activities = array_values($this->activities);
    }

    public function addReference(): void
    {
        $this->references[] = ['name' => null, 'company' => null, 'position' => null, 'phone' => null, 'email' => null, 'note' => null];
    }

    public function removeReference(int $index): void
    {
        unset($this->references[$index]);
        $this->references = array_values($this->references);
    }

    public function getCurrentCvUrlProperty(): ?string
    {
        $candidate = Candidate::query()->find($this->candidateId);
        if (! $candidate?->cv_file) {
            return null;
        }

        return Route::has('public-file.preview')
            ? route('public-file.preview', ['path' => $candidate->cv_file])
            : asset('storage/' . ltrim($candidate->cv_file, '/'));
    }

    public function getCurrentAvatarUrlProperty(): string
    {
        $avatar = Auth::user()?->avatar;

        if (! $avatar) {
            return asset('assets/img/avatar_detail.jpg');
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }

        return asset('storage/' . ltrim($avatar, '/'));
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.candidate-profile');
    }
}
