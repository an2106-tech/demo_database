<?php

namespace App\Livewire\Client\Employers;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

class ManageCandidate extends Component
{
    #[Layout('layouts.employer')]
    public function analyzeWithAi($submissionId, \App\Services\AiMatchingService $aiService)
    {
        $submission = \App\Models\CandidateJobSubmission::find($submissionId);
        
        if ($submission) {
            $success = $aiService->calculateMatch($submission);
            
            if ($success) {
                session()->flash('message', 'Phân tích AI hoàn tất cho ' . $submission->candidate->name);
            } else {
                session()->flash('error', 'Không thể phân tích AI. Vui lòng kiểm tra lại API Key hoặc nội dung CV.');
            }
        }
    }

    public function deleteCandidate($candidateId)
    {
        $candidate = Candidate::find($candidateId);
        if ($candidate) {
            $candidate->delete();
            session()->flash('message', 'Đã xóa ứng viên thành công.');
        }
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $candidates = Candidate::query()
            ->with(['applications.job'])
            ->when(
                $user?->branchScopeId(),
                fn (Builder $query, int $branchId) => $query->whereHas(
                    'applications.job',
                    fn (Builder $jobQuery) => $jobQuery->where('branch_id', $branchId)
                )
            )
            ->latest()
            ->get();

        return view('livewire.client.employers.manage_candidate', ['candidates' => $candidates]);
    }
}
