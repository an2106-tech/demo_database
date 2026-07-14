<?php

namespace App\Livewire\Client;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Models\Department;
use App\Models\RecruitmentJob;
use App\Services\CandidateAccountService;
use App\Services\LocationSearchNormalizer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class BrowseJobs extends Component
{
    use WithPagination;

    #[Layout('layouts.client')]
    public string $display = 'grid';

    protected string $paginationTheme = 'bootstrap';

    public string $q = '';

    public string $city = '';

    public ?int $department_id = null;

    public ?int $category_id = null;

    public array $jobMatchLabels = [];

    protected array $queryString = [
        'display' => ['except' => 'grid'],
        'q' => ['except' => ''],
        'city' => ['except' => ''],
        'department_id' => ['except' => null],
        'category_id' => ['except' => null],
    ];

    public function mount(): void
    {
        if (! request()->has('display') && request()->has('view')) {
            $requestedView = request()->query('view');
            if (is_string($requestedView)) {
                $this->display = $requestedView;
            }
        }

        $this->q = (string) request()->query('q', '');
        $this->city = (string) request()->query('city', '');
        $dept = request()->query('department_id');
        $this->department_id = $dept !== null && $dept !== '' ? (int) $dept : null;

        $cat = request()->query('category_id');
        $this->category_id = $cat !== null && $cat !== '' ? (int) $cat : null;

        $this->normalizeDisplay();
    }

    public function updatedDisplay(): void
    {
        $this->normalizeDisplay();
    }

    public function updated($property): void
    {
        if ($property === 'city') {
            $this->department_id = null;
        }

        if (in_array($property, ['q', 'city', 'department_id', 'category_id'], true)) {
            $this->resetPage();
        }
    }

    public function setDisplay(string $display): void
    {
        $this->display = $display;
        $this->normalizeDisplay();
    }

    public function clearFilters(): void
    {
        $this->q = '';
        $this->city = '';
        $this->department_id = null;
        $this->category_id = null;
        $this->resetPage();
    }

    private function normalizeDisplay(): void
    {
        $this->display = in_array($this->display, ['grid', 'list'], true) ? $this->display : 'grid';
    }

    public function render(LocationSearchNormalizer $locationSearchNormalizer)
    {
        $now = Carbon::now();
        $location = trim($this->city) !== '' ? $locationSearchNormalizer->normalize($this->city) : null;
        $candidate = null;
        $resume = null;

        $user = Auth::user();
        if ($user) {
            $candidateService = app(CandidateAccountService::class);
            if ($candidateService->hasCandidateAccount($user)) {
                $candidate = $candidateService->resolveFor($user);
                if ($candidateService->candidateHasCv($candidate)) {
                    $resume = $candidate->resume()->first();
                }
            }
        }

        $jobsQuery = RecruitmentJob::query()
            ->where(fn (Builder $query) => $this->applyOpenJobConstraint($query, $now))
            ->with(['branch:id,name,image,city,address', 'workplace:id,name', 'department:id,name', 'skills:id,name']);

        if (trim($this->q) !== '') {
            $keyword = trim($this->q);
            $jobsQuery->where(function ($query) use ($keyword) {
                $query
                    ->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhereHas('branch', function ($branchQuery) use ($keyword) {
                        $branchQuery->where('name', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('department', function ($departmentQuery) use ($keyword) {
                        $departmentQuery->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        if ($location !== null) {
            $jobsQuery->whereHas('branch', function ($query) use ($location) {
                $this->applyLocationConstraint($query, $location);
            });
        }

        if (! empty($this->department_id)) {
            $jobsQuery->where('department_id', $this->department_id);
        }

        if (! empty($this->category_id)) {
            $jobsQuery->whereHas('categories', function ($query) {
                $query->where('categories.id', $this->category_id);
            });
        }

        $jobs = $jobsQuery->latest()->paginate(12);
        $this->jobMatchLabels = $candidate && $resume
            ? $this->buildJobMatchLabels($jobs->getCollection()->all(), $resume, $candidate)
            : [];
        $departments = $this->availableDepartments($now, $location);

        return view('livewire.client.browse-jobs', [
            'jobs' => $jobs,
            'departments' => $departments,
        ]);
    }

    private function buildJobMatchLabels(array $jobs, $resume, $candidate): array
    {
        $scores = [];

        foreach ($jobs as $job) {
            $scores[$job->id] = $this->jobMatchScore($job, $resume, $candidate);
        }

        $maxScore = max($scores ?: [0]);

        if ($maxScore <= 0) {
            return array_fill_keys(array_keys($scores), 'Phù hợp thấp');
        }

        $highThreshold = $maxScore * 0.7;
        $mediumThreshold = $maxScore * 0.4;

        $labels = [];
        foreach ($scores as $jobId => $score) {
            $labels[$jobId] = match (true) {
                $score >= $highThreshold => 'Phù hợp cao',
                $score >= $mediumThreshold => 'Phù hợp vừa',
                default => 'Phù hợp thấp',
            };
        }

        return $labels;
    }

    private function jobMatchScore($job, $resume, $candidate): int
    {
        $desiredJob = is_array($resume?->desired_job) ? $resume->desired_job : [];
        $profileSkills = $resume?->skills ?: ($candidate->skills ?? []);

        $searchTerms = $this->searchTerms([
            $desiredJob['position'] ?? null,
            $desiredJob['level'] ?? null,
            $desiredJob['location'] ?? ($desiredJob['workplace'] ?? null),
            $resume?->profile_title,
            $profileSkills,
        ]);

        if ($searchTerms === []) {
            return 0;
        }

        $title = $this->normalizeSearchText($job->title);
        $department = $this->normalizeSearchText($job->department?->name);
        $workplace = $this->normalizeSearchText($job->workplace?->name);
        $skills = $this->normalizeSearchText($job->skills->pluck('name')->implode(' '));
        $description = $this->normalizeSearchText(strip_tags((string) $job->description));
        $score = 0;

        foreach ($searchTerms as $term) {
            $score += str_contains($title, $term) ? 10 : 0;
            $score += str_contains($skills, $term) ? 7 : 0;
            $score += str_contains($department, $term) ? 5 : 0;
            $score += str_contains($workplace, $term) ? 4 : 0;
            $score += str_contains($description, $term) ? 1 : 0;
        }

        return $score;
    }

    private function searchTerms(array $values): array
    {
        $flattened = [];
        array_walk_recursive($values, function ($value) use (&$flattened) {
            if (is_string($value) && filled($value)) {
                $flattened[] = $value;
            }
        });

        $tokens = preg_split('/\s+/u', $this->normalizeSearchText(implode(' ', $flattened))) ?: [];

        return array_values(array_unique(array_filter(
            $tokens,
            fn (string $token) => mb_strlen($token) >= 3
        )));
    }

    private function normalizeSearchText(?string $value): string
    {
        return Str::lower(Str::ascii(trim((string) $value)));
    }

    private function applyOpenJobConstraint(Builder $query, Carbon $now): void
    {
        $query
            ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('deadline')
                    ->orWhere('deadline', '>=', $now);
            });
    }

    /**
     * @param array{term: string, province_values: array<int, string>, keywords: array<int, string>} $location
     */
    private function applyLocationConstraint(Builder $query, array $location): void
    {
        $query->where(function (Builder $branchQuery) use ($location) {
            if ($location['province_values'] !== []) {
                $branchQuery->whereIn('city', $location['province_values']);
            }

            foreach ($location['keywords'] as $keyword) {
                $branchQuery
                    ->orWhere('city', 'like', '%' . $keyword . '%')
                    ->orWhere('address', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%');
            }
        });
    }

    /**
     * @param array{term: string, province_values: array<int, string>, keywords: array<int, string>}|null $location
     */
    private function availableDepartments(Carbon $now, ?array $location)
    {
        return Department::query()
            ->whereHas('recruitmentJobs', function (Builder $query) use ($now, $location) {
                $this->applyOpenJobConstraint($query, $now);

                if ($location !== null) {
                    $query->whereHas('branch', function (Builder $branchQuery) use ($location) {
                        $this->applyLocationConstraint($branchQuery, $location);
                    });
                }
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
