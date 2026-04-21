<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

class CandidatesDetails extends Component
{
    public $candidate;
    public $latestSubmission;

    public function mount()
    {
        $id = request()->query('id');
        
        if (!$id) {
            return redirect()->route('home');
        }

        $this->candidate = \App\Models\Candidate::with(['resume', 'user', 'applications.job'])->findOrFail($id);
        
        // Lấy thông tin submission gần nhất để xem điểm AI (nếu có)
        $this->latestSubmission = \App\Models\CandidateJobSubmission::where('candidate_id', $this->candidate->id)
            ->latest()
            ->first();
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.candidates-details');
    }
}
