<?php

namespace App\Livewire\Client\Employers;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Models\Branch;
use App\Models\Department;
use App\Models\RecruitmentJob;
use App\Models\Skill;
use App\Models\Workplace;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PostJob extends Component
{
    public string $title = '';

    public string $description = '';

    public ?int $branch_id = null;

    public ?int $department_id = null;

    public ?int $workplace_id = null;

    public ?string $public_url = null;

    public ?string $deadline = null;

    public int $positions_count = 1;

    public string $status = ''; // Will be set in mount

    public ?string $salary_min = null;

    public ?string $salary_max = null;

    public array $skills = [];

    public array $selected_categories = [];

    public string $skills_level = 'mid';

    public ?int $jobId = null;

    public function mount($id = null): void
    {
        // Nếu candidate đang đăng nhập, chuyển hướng đến login của ứng viên
        $user = Auth::user();

        if ($id) {
            $job = RecruitmentJob::findOrFail($id);
            if ($job->created_by !== Auth::id()) {
                abort(403);
            }
            $this->jobId = $job->id;
            $this->title = $job->title;
            $this->description = $job->description ?? '';
            $this->branch_id = $job->branch_id;
            $this->department_id = $job->department_id;
            $this->workplace_id = $job->workplace_id;
            $this->public_url = $job->public_url;
            $this->deadline = $job->deadline ? $job->deadline->format('Y-m-d') : null;
            $this->positions_count = $job->positions_count;
            $this->status = $job->status->value ?? 'draft';

            if (is_array($job->salary_range)) {
                $this->salary_min = $job->salary_range['min'] ?? null;
                $this->salary_max = $job->salary_range['max'] ?? null;
            }

            $this->skills = $job->skills->pluck('id')->toArray();
            $this->selected_categories = $job->categories->pluck('id')->toArray();
            $firstSkill = $job->skills->first();
            if ($firstSkill) {
                $this->skills_level = $firstSkill->pivot->level ?? 'mid';
            }
        } else {
            if ($user?->branchScopeId()) {
                $this->branch_id = $user->branchScopeId();
            }
        }

        // Default status for NEW jobs
        if (!$id) {
            $this->status = in_array($user->role, ['director', 'admin']) 
                ? StatusRecruitmentJobsEnum::PUBLISHED->value 
                : StatusRecruitmentJobsEnum::PENDING->value;
        }
    }

    public function updatedBranchId($value): void
    {
        $this->branch_id = filled($value) ? (int) $value : null;
        $this->department_id = null;
        $this->workplace_id = null;
    }

    public function updatedDepartmentId($value): void
    {
        $this->department_id = filled($value) ? (int) $value : null;
        $this->workplace_id = null;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'branch_id' => ['required', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'workplace_id' => ['nullable', 'exists:workplaces,id'],
            'public_url' => ['nullable', 'url', 'max:2048', 'unique:recruitment_jobs,public_url'],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'positions_count' => ['required', 'integer', 'min:1', 'max:99'],
            'status' => ['nullable', 'string'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0'],
            'skills' => ['required', 'array', 'min:1'],
            'skills.*' => ['integer', 'distinct', 'exists:skills,id'],
            'skills_level' => ['required', 'in:junior,mid,senior'],
            'selected_categories' => ['required', 'array', 'min:1'],
            'selected_categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ];
    }

    public function save(): mixed
    {
        $validated = $this->validate();

        if (
            filled($validated['salary_min'] ?? null)
            && filled($validated['salary_max'] ?? null)
            && (float) $validated['salary_min'] > (float) $validated['salary_max']
        ) {
            $this->addError('salary_max', 'Mức lương tối đa phải lớn hơn hoặc bằng mức lương tối thiểu.');

            return null;
        }

        $branchId = (int) $validated['branch_id'];

        if ($validated['department_id']) {
            $departmentBelongsToBranch = Department::query()
                ->whereKey($validated['department_id'])
                ->where('branch_id', $branchId)
                ->exists();

            if (! $departmentBelongsToBranch) {
                $this->addError('department_id', 'Phòng ban không thuộc chi nhánh đã chọn.');

                return null;
            }
        }

        if ($validated['workplace_id']) {
            $workplaceBelongsToBranch = Workplace::query()
                ->whereKey($validated['workplace_id'])
                ->where('branch_id', $branchId)
                ->exists();

            if (! $workplaceBelongsToBranch) {
                $this->addError('workplace_id', 'Nơi làm việc không thuộc chi nhánh đã chọn.');

                return null;
            }
        }

        $user = Auth::user();
        $finalStatus = $validated['status'];

        // Logic: if not Director/Admin, must be PENDING (even on edits as requested)
        if (!in_array($user->role, ['director', 'admin'])) {
            $finalStatus = StatusRecruitmentJobsEnum::PENDING->value;
        }

        $data = [
            'title' => trim($validated['title']),
            'description' => trim($validated['description']),
            'status' => $finalStatus,
            'salary_range' => $this->buildSalaryRange(),
            'deadline' => $validated['deadline'] ?: null,
            'positions_count' => (int) $validated['positions_count'],
            'public_url' => $validated['public_url'] ?: null,
            'department_id' => $validated['department_id'] ?: null,
            'branch_id' => $branchId,
            'workplace_id' => $validated['workplace_id'] ?: null,
        ];

        if ($this->jobId) {
            $job = RecruitmentJob::findOrFail($this->jobId);
            if ($job->created_by !== Auth::id()) {
                abort(403);
            }
            if ($job->title !== trim($validated['title'])) {
                $data['slug'] = $this->generateUniqueSlug($validated['title']);
            }
            $job->update($data);
        } else {
            $data['slug'] = $this->generateUniqueSlug($validated['title']);
            $data['created_by'] = Auth::id();
            $job = RecruitmentJob::create($data);
        }

        $pivotData = [];
        foreach (($validated['skills'] ?? []) as $skillId) {
            $pivotData[(int) $skillId] = [
                'level' => $validated['skills_level'],
                'is_required' => true,
            ];
        }
        $job->skills()->sync($pivotData);

        $job->categories()->sync($validated['selected_categories']);

        $statusMessage = match ($job->status) {
            StatusRecruitmentJobsEnum::PUBLISHED => 'Tin tuyển dụng đã được đăng thành công.',
            StatusRecruitmentJobsEnum::PENDING => 'Tin tuyển dụng đã được gửi và đang chờ phê duyệt.',
            default => 'Tin tuyển dụng đã được lưu nháp.',
        };

        // Notify Directors of the branch if pending
        if ($job->status === StatusRecruitmentJobsEnum::PENDING) {
            $directors = \App\Models\User::where('role', 'director')
                ->where('branch_id', $job->branch_id)
                ->get();

            foreach ($directors as $director) {
                $filamentUrl = route('filament.admin.resources.recruitment-jobs.edit', ['record' => $job->id]);

                // Database Notification
                DB::table('notifications')->insert([
                    'user_id' => $director->id,
                    'type' => 'job_pending_approval',
                    'data' => json_encode([
                        'job_id' => $job->id,
                        'title' => $job->title,
                        'creator' => Auth::user()->name,
                        'message' => 'Có tin tuyển dụng mới đang chờ bạn duyệt.',
                        'filament_url' => $filamentUrl,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Email Notification
                try {
                    Mail::to($director->email)->send(new \App\Mail\JobApprovalNotificationMail(
                        $job,
                        $director->name,
                        Auth::user()->name,
                        $director->id
                    ));
                } catch (\Exception $e) {
                    \Log::error('Lỗi khi gửi email thông báo phê duyệt: '.$e->getMessage());
                }
            }

        }

        session()->flash('status', $statusMessage);

        return redirect()->route('employers.manage_jobs');
    }

    #[Layout('layouts.employer')]
    public function render()
    {
        $user = Auth::user();

        $branches = Branch::query()
            ->when($user?->branchScopeId(), fn ($query, int $branchId) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->when($this->branch_id, fn ($query) => $query->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        $workplaces = Workplace::query()
            ->when($this->branch_id, fn ($query) => $query->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        $skillsOptions = Skill::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $categoriesOptions = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.client.employers.post_job', [
            'branches' => $branches,
            'departments' => $departments,
            'workplaces' => $workplaces,
            'skillsOptions' => $skillsOptions,
            'categoriesOptions' => $categoriesOptions,
            'isBranchLocked' => (bool) $user?->branchScopeId(),
        ]);
    }

    private function buildSalaryRange(): ?array
    {
        $min = filled($this->salary_min) ? (float) $this->salary_min : null;
        $max = filled($this->salary_max) ? (float) $this->salary_max : null;

        if ($min === null && $max === null) {
            return null;
        }

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug !== '' ? $baseSlug : 'job';
        $originalSlug = $slug;
        $suffix = 2;

        while (RecruitmentJob::query()->where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
