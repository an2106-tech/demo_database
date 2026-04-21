<?php

namespace App\Http\Controllers;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Models\RecruitmentJob;
use Illuminate\Http\Request;

class JobApprovalController extends Controller
{
    /**
     * Duyệt tin trực tiếp từ email (Signed URL).
     */
    public function approve(Request $request, RecruitmentJob $job)
    {
        if ($job->status !== StatusRecruitmentJobsEnum::PENDING) {
            return view('emails.approval-result', [
                'success' => false,
                'message' => 'Tin tuyển dụng này đã được xử lý trước đó hoặc không ở trạng thái chờ duyệt.',
                'job' => $job
            ]);
        }

        $job->update(['status' => StatusRecruitmentJobsEnum::PUBLISHED]);

        return view('emails.approval-result', [
            'success' => true,
            'message' => 'Chúc mừng! Tin tuyển dụng "' . $job->title . '" đã được phê duyệt và đăng công khai.',
            'job' => $job
        ]);
    }

    /**
     * Từ chối tin trực tiếp từ email (Signed URL).
     */
    public function reject(Request $request, RecruitmentJob $job)
    {
        if ($job->status !== StatusRecruitmentJobsEnum::PENDING) {
            return view('emails.approval-result', [
                'success' => false,
                'message' => 'Tin tuyển dụng này đã được xử lý hoặc không hợp lệ.',
                'job' => $job
            ]);
        }

        $job->update(['status' => StatusRecruitmentJobsEnum::DRAFT]);

        return view('emails.approval-result', [
            'success' => true,
            'message' => 'Yêu cầu tuyển dụng đã được từ chối và chuyển về bản nháp.',
            'job' => $job
        ]);
    }


    /**
     * Xem tin trực tiếp trong Filament với tính năng tự động đăng nhập.
     */
    public function viewInFilament(Request $request, RecruitmentJob $job)
    {
        $userId = $request->query('user_id');
        
        if ($userId) {
            \Illuminate\Support\Facades\Auth::loginUsingId($userId);
        }

        return redirect()->route('filament.admin.resources.recruitment-jobs.index', ['activeTab' => 'pending']);
    }
}


