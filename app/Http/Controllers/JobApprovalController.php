<?php

namespace App\Http\Controllers;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Models\RecruitmentJob;
use Illuminate\Http\Request;

class JobApprovalController extends Controller
{
    public function approve(Request $request, RecruitmentJob $job)
    {
        if ($job->status !== StatusRecruitmentJobsEnum::PENDING) {
            return view('emails.approval-result', [
                'success' => false,
                'message' => 'Tin tuyển dụng này đã được xử lý trước đó hoặc không ở trạng thái chờ duyệt.',
                'job' => $job,
            ]);
        }

        $job->update(['status' => StatusRecruitmentJobsEnum::PUBLISHED]);

        return view('emails.approval-result', [
            'success' => true,
            'message' => 'Chúc mừng! Tin tuyển dụng "'.$job->title.'" đã được phê duyệt và đăng công khai.',
            'job' => $job,
        ]);
    }

    public function reject(Request $request, RecruitmentJob $job)
    {
        if ($job->status !== StatusRecruitmentJobsEnum::PENDING) {
            return view('emails.approval-result', [
                'success' => false,
                'message' => 'Tin tuyển dụng này đã được xử lý hoặc không hợp lệ.',
                'job' => $job,
            ]);
        }

        $job->update(['status' => StatusRecruitmentJobsEnum::DRAFT]);

        return view('emails.approval-result', [
            'success' => true,
            'message' => 'Yêu cầu tuyển dụng đã được từ chối và chuyển về bản nháp.',
            'job' => $job,
        ]);
    }

    public function viewInFilament(Request $request, RecruitmentJob $job)
    {
        return redirect()->route('filament.admin.resources.recruitment-jobs.index', ['activeTab' => 'pending']);
    }
}
