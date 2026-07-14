<?php

use App\Http\Controllers\JobApprovalController;
use App\Http\Controllers\EmployerApplicationPipelineController;
use App\Http\Controllers\OfferResponseController;
use App\Livewire\Client\About as PagesAbout;
use App\Livewire\Client\ApplicationDetail;
use App\Livewire\Client\ApplyJob;
use App\Livewire\Client\Blog;
use App\Livewire\Client\BrowseCategories;
use App\Livewire\Client\BrowseCompanies;
use App\Livewire\Client\BrowseJobs;
use App\Livewire\Client\CandidateDashboard;
use App\Livewire\Client\CandidateProfile as ClientCandidateProfile;
use App\Livewire\Client\CandidatesDetails;
use App\Livewire\Client\Contact;
use App\Livewire\Client\Earnings;
use App\Livewire\Client\Employers\BrowseCandidates;
use App\Livewire\Client\Employers\CandidateEarnings;
use App\Livewire\Client\Employers\CandidateProfile as EmpCandidateProfile;
use App\Livewire\Client\Employers\ChangePassword as EmployerChangePassword;
use App\Livewire\Client\Employers\CompanyProfile;
use App\Livewire\Client\Employers\EmployerPortal;
use App\Livewire\Client\Employers\EmployersDashboard;
use App\Livewire\Client\Employers\ManageCandidate;
use App\Livewire\Client\Employers\ManageJobs as EmployerManageJobs;
use App\Livewire\Client\Employers\Message as EmployerMessage;
use App\Livewire\Client\Employers\PostJob;
use App\Livewire\Client\Employers\SingleCompany;
use App\Livewire\Client\Employers\Transaction;
use App\Livewire\Client\Home;
use App\Livewire\Client\Job\JobDetail;
use App\Livewire\Client\JobListSideBars;
use App\Livewire\Client\JobPage;
use App\Livewire\Client\Login as PagesLogin;
use App\Livewire\Client\ManageJobs;
use App\Livewire\Client\Messages;
use App\Livewire\Client\Notifications;
use App\Livewire\Client\PostJobs as ClientPostJobs;
use App\Livewire\Client\ForgotPassword;
use App\Livewire\Client\Register as PagesRegister;
use App\Livewire\Client\ResetPassword;
use App\Livewire\Client\Sidebars;
use App\Livewire\Client\Single;
use App\Livewire\Client\SubmitResume;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', Home::class)->name('home');
Route::get('/dang-nhap', PagesLogin::class)->name('candidates.login');
Route::get('/dang-ky', PagesRegister::class)->name('candidates.register');
Route::get('/nha-tuyen-dung', EmployerPortal::class)->name('employers.portal');
Route::get('/nha-tuyen-dung/dang-nhap', PagesLogin::class)->name('employers.login');
Route::get('/nha-tuyen-dung/dang-ky', PagesRegister::class)->name('employers.register');
Route::get('/quen-mat-khau', ForgotPassword::class)->middleware('guest')->name('password.request');
Route::get('/dat-lai-mat-khau/{token}', ResetPassword::class)->middleware('guest')->name('password.reset');

// Public job detail page — shareable link for candidates (no login required)
Route::get('/jobs/{slug}', JobDetail::class)->name('jobs.public');

Route::get('/preview/public-file/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path, basename($path), [
        'Content-Disposition' => 'inline; filename="'.basename($path).'"',
        'X-Frame-Options' => 'SAMEORIGIN',
    ]);
})->where('path', '.*')->name('public-file.preview');

Route::prefix('offers')->name('offers.')->group(function () {
    Route::get('/{offer}/respond/accept', [OfferResponseController::class, 'accept'])
        ->name('respond.accept');
    Route::get('/{offer}/respond/decline', [OfferResponseController::class, 'decline'])
        ->name('respond.decline');
    Route::post('/{offer}/respond/decline', [OfferResponseController::class, 'submitDecline'])
        ->name('respond.decline.submit');
});

Route::prefix('jobs')->name('jobs.')->group(function () {
    Route::get('/{job}/direct-approve', [JobApprovalController::class, 'approve'])
        ->middleware('signed')
        ->name('direct_approve');
    Route::get('/{job}/direct-reject', [JobApprovalController::class, 'reject'])
        ->middleware('signed')
        ->name('direct_reject');
    Route::get('/{job}/view-in-filament', [JobApprovalController::class, 'viewInFilament'])
        ->middleware('signed')
        ->name('autologin_view');
});


Route::redirect('/candidate-profile.html', '/candidates/candidate-profile');
Route::redirect('/candidate-dashboard.html', '/candidates/candidate-dashboard');
Route::redirect('/message.html', '/candidates/messages');
Route::redirect('/manage-jobs.html', '/candidates/manage-jobs');
Route::redirect('/candidate-earnings.html', '/candidates/earnings');
Route::redirect('/change-password.html', '/candidates/change-password');
Route::redirect('/submit-resume.html', '/candidates/submit-resume');
Route::redirect('/candidates/browse_job', '/candidates/browse-job');
Route::redirect('/candidates/joblist_sidebar', '/candidates/joblist-sidebar');
Route::redirect('/candidates/browse_categories', '/candidates/browse-categories');
Route::redirect('/candidates/browse_companies', '/candidates/browse-companies');
Route::redirect('/candidates/candidate_detail', '/candidates/candidate-detail');
Route::redirect('/candidates/submit_resume', '/candidates/submit-resume');
Route::redirect('/candidates/candidate_dashboard', '/candidates/candidate-dashboard');
Route::redirect('/candidates/candidate_profile', '/candidates/candidate-profile');
Route::redirect('/candidates/manage_jobs', '/candidates/manage-jobs');
Route::redirect('/candidates/change_password', '/candidates/change-password');
Route::redirect('/candidates/candidate-dashboard.html', '/candidates/candidate-dashboard');
Route::redirect('/candidates/candidate-profile.html', '/candidates/candidate-profile');
Route::redirect('/candidates/message.html', '/candidates/messages');
Route::redirect('/candidates/manage-jobs.html', '/candidates/manage-jobs');
Route::redirect('/candidates/candidate-earnings.html', '/candidates/earnings');
Route::redirect('/candidates/change-password.html', '/candidates/change-password');

Route::redirect('/auth/sign_up', '/auth/sign-up');
Route::redirect('/auth/post_jobs', '/auth/post-jobs');

Route::redirect('/employers/single_company', '/employers/single-company');
Route::redirect('/employers/post_job', '/employers/post-job');
Route::redirect('/employers/job_detail', '/employers/job-detail');

Route::prefix('candidates')->name('candidates.')->group(function () {
    Route::get('/browse-job', BrowseJobs::class)->name('browse_job');
    Route::get('/sidebar', Sidebars::class)->name('sidebar');
    Route::get('/joblist-sidebar', JobListSideBars::class)->name('joblist_sidebar');
    Route::get('/browse-categories', BrowseCategories::class)->name('browse_categories');
    Route::get('/browse-companies', BrowseCompanies::class)->name('browse_companies');
    Route::get('/candidate-detail', CandidatesDetails::class)->middleware('auth')->name('candidate_detail');
    Route::get('/job-detail/{id}', JobDetail::class)->name('job_detail');
    Route::get('jobs/{job}/apply', ApplyJob::class)->name('apply_job');
    Route::get('applications/{application}/verify-email', function (\App\Models\Application $application) {
        $candidate = $application->candidate;
        abort_unless($candidate && filled($candidate->email), 404);

        $metadata = is_array($candidate->metadata) ? $candidate->metadata : [];
        $metadata['guest_email_verified_at'] = now()->toDateTimeString();
        $metadata['guest_email_verified_application_id'] = $application->id;
        $metadata['guest_email_verified_email'] = $candidate->email;
        $candidate->forceFill(['metadata' => $metadata])->save();

        return redirect()
            ->route('candidates.login', ['email' => $candidate->email])
            ->with('status', 'Email ứng tuyển đã được xác thực. Bạn có thể đăng nhập hoặc đăng ký để theo dõi hồ sơ.');
    })->middleware('signed')->name('applications.verify_email');

    Route::middleware(['auth', 'candidate.account'])->group(function () {
        Route::get('submit-resume', SubmitResume::class)->name('submit_resume');
        Route::get('candidate-dashboard', CandidateDashboard::class)->name('candidate_dashboard');
        Route::get('candidate-profile', ClientCandidateProfile::class)->name('candidate_profile');
        Route::get('download-cv', function (\Illuminate\Http\Request $request) {
            $user = auth()->user();
            $candidate = app(\App\Services\CandidateAccountService::class)->resolveFor($user);
            $resume = \App\Models\CandidateResume::where('candidate_id', $candidate->id)->first();
            
            if ($request->has('preview')) {
                return view('pdf.cv-template', [
                    'candidate' => $candidate,
                    'resume' => $resume,
                ]);
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.cv-template', [
                'candidate' => $candidate,
                'resume' => $resume,
            ]);
            
            return $pdf->download('CV_' . str_replace(' ', '_', $candidate->name) . '.pdf');
        })->name('cv.download');

        Route::get('messages', Messages::class)->name('messages');
        Route::get('manage-jobs', ManageJobs::class)->name('manage_jobs');
        Route::get('applications/{application}', ApplicationDetail::class)->name('application_detail');
        Route::get('earnings', Earnings::class)->name('earnings');
        Route::get('notifications', Notifications::class)->name('notifications');
        Route::get('change-password', EmployerChangePassword::class)->name('change_password');
    });
});

Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/login', PagesLogin::class)->name('login');
    Route::get('/sign-up', PagesRegister::class)->name('sign_up');
    Route::get('/post-jobs', ClientPostJobs::class)->name('post_jobs');
});

Route::prefix('employers')->name('employers.')->group(function () {
    Route::get('/browse', BrowseCandidates::class)->name('browse');
    Route::get('/single-company/{branch?}', SingleCompany::class)->name('single_company');
    Route::get('/job-detail/{id}', JobDetail::class)->name('job_detail');

    Route::middleware(['auth', 'employer.account'])->group(function () {
        Route::get('/post-job', PostJob::class)->name('post_job');
        Route::get('/edit-job/{id}', PostJob::class)->name('edit_job');
        Route::get('/dashboard', EmployersDashboard::class)->name('dashboard');
        Route::get('/company-profile', CompanyProfile::class)->name('company_profile');
        Route::get('/message', EmployerMessage::class)->name('message');
        Route::get('/manage-candidates', ManageCandidate::class)->name('manage_candidates');
        Route::get('/candidates/{candidate}', CandidatesDetails::class)->name('candidate_detail');
        Route::get('/transaction', Transaction::class)->name('transaction');
        Route::get('/notifications', Notifications::class)->name('notifications');
        Route::get('/change-password', EmployerChangePassword::class)->name('change_password');
        Route::get('/candidate-profile', EmpCandidateProfile::class)->name('candidate_profile');
        Route::get('/manage-jobs', EmployerManageJobs::class)->name('manage_jobs');
        Route::get('/candidate-earnings', CandidateEarnings::class)->name('candidate_earnings');
        Route::get('/application-pipeline', \App\Livewire\Client\Employers\ApplicationPipeline::class)->name('application_pipeline');
        Route::post('/application-pipeline/{application}/advance', function (\App\Models\Application $application) {
            $user = auth()->user();
            $branchId = $user?->branchScopeId();
            $application->loadMissing('job');

            abort_unless($user, 403);

            if ($branchId) {
                abort_unless((int) ($application->branch_id ?: $application->job?->branch_id) === (int) $branchId, 403);
            } elseif (! $user->isSuperAdmin() && ! in_array($user->role, ['admin', 'director'], true)) {
                abort_unless((int) $application->job?->created_by === (int) $user->id, 403);
            }

            $pipelineService = app(\App\Services\ApplicationPipelineService::class);
            $currentStatus = $application->status instanceof \App\Enums\StatusApplicationEnum
                ? $application->status
                : \App\Enums\StatusApplicationEnum::tryFrom((string) $application->status);

            if ($currentStatus === \App\Enums\StatusApplicationEnum::SCREENING) {
                return back()->with('warning', 'Vui lòng dùng nút Lên lịch PV để chuyển hồ sơ sang vòng phỏng vấn.');
            }

            $nextStatus = collect($pipelineService->allowedTransitions($application->status))
                ->first(fn (\App\Enums\StatusApplicationEnum $status): bool => $status !== \App\Enums\StatusApplicationEnum::REJECTED);

            if (! $nextStatus) {
                return back()->with('warning', 'Hồ sơ này chưa có bước kế tiếp phù hợp.');
            }

            try {
                $pipelineService->transition($application, $nextStatus, $user, 'HR chuyển nhanh từ Pipeline.');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                return back()->with('error', $exception->errors()['status'][0] ?? 'Không thể chuyển trạng thái hồ sơ.');
            }

            $statusLabels = [
                \App\Enums\StatusApplicationEnum::CV_REVIEWING->value => 'Chờ sàng lọc CV',
                \App\Enums\StatusApplicationEnum::SCREENING->value => 'Sơ tuyển',
                \App\Enums\StatusApplicationEnum::INTERVIEW_SCHEDULED->value => 'Đã lên lịch phỏng vấn',
                \App\Enums\StatusApplicationEnum::INTERVIEWING->value => 'Chờ đánh giá phỏng vấn',
                \App\Enums\StatusApplicationEnum::OFFERED->value => 'Đề nghị tuyển dụng',
                \App\Enums\StatusApplicationEnum::HIRED->value => 'Đã tuyển',
                \App\Enums\StatusApplicationEnum::REJECTED->value => 'Từ chối',
            ];

            return back()->with('message', 'Đã chuyển hồ sơ sang: '.($statusLabels[$nextStatus->value] ?? $nextStatus->value).'.');
        })->name('application_pipeline.advance');
        Route::post('/application-pipeline/{application}/schedule-interview', function (\App\Models\Application $application) {
            $user = auth()->user();
            $application->loadMissing(['candidate', 'job.branch']);
            $branchId = (int) ($application->branch_id ?: $application->job?->branch_id);

            abort_unless($user, 403);

            if ($user->branchScopeId()) {
                abort_unless((int) $user->branchScopeId() === $branchId, 403);
            } elseif (! $user->isSuperAdmin() && ! in_array($user->role, ['admin', 'director'], true)) {
                abort_unless((int) $application->job?->created_by === (int) $user->id, 403);
            }

            $status = $application->status instanceof \App\Enums\StatusApplicationEnum
                ? $application->status
                : \App\Enums\StatusApplicationEnum::tryFrom((string) $application->status);

            abort_unless(in_array($status, [
                \App\Enums\StatusApplicationEnum::SCREENING,
                \App\Enums\StatusApplicationEnum::INTERVIEW_SCHEDULED,
                \App\Enums\StatusApplicationEnum::INTERVIEWING,
            ], true), 403);

            $validated = request()->validate([
                'round_name' => ['required', 'string', 'max:100'],
                'scheduled_at' => ['required', 'date'],
                'duration_minutes' => ['required', 'integer', \Illuminate\Validation\Rule::in([30, 45, 60, 90])],
                'type' => ['required', \Illuminate\Validation\Rule::in(['online', 'offline'])],
                'meeting_link' => [
                    \Illuminate\Validation\Rule::requiredIf(request('type') === 'online'),
                    'nullable',
                    'url',
                    'max:500',
                ],
                'workplace_id' => [
                    \Illuminate\Validation\Rule::requiredIf(request('type') === 'offline'),
                    'nullable',
                    \Illuminate\Validation\Rule::exists('workplaces', 'id')->where(fn ($query) => $query
                        ->where('branch_id', $branchId)
                        ->where('is_interview_room', true)
                        ->where('is_active', true)),
                ],
                'interviewer_id' => [
                    'required',
                    \Illuminate\Validation\Rule::exists('users', 'id')->where(fn ($query) => $query
                        ->where('branch_id', $branchId)
                        ->where('is_active', true)
                        ->whereIn('role', ['hr', 'pm', 'director'])),
                ],
                'notes' => ['nullable', 'string', 'max:1000'],
            ]);

            $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_at'], config('app.interview_timezone', 'Asia/Ho_Chi_Minh'));

            if ($scheduledAt->lt(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')))) {
                return back()
                    ->withInput()
                    ->with('error', 'Thời gian phỏng vấn không được ở quá khứ.');
            }

            $existingInterview = $application->interviews()->latest('id')->first();
            $roundNumber = (int) ($existingInterview?->round_number ?: 1);
            $interview = $existingInterview ?? new \App\Models\Interview([
                'application_id' => $application->id,
                'round_number' => $roundNumber,
                'result' => 'pending',
            ]);

            $interview->fill([
                'application_id' => $application->id,
                'interviewer_id' => (int) $validated['interviewer_id'],
                'round_name' => trim((string) $validated['round_name']) ?: 'Phỏng vấn vòng '.$roundNumber,
                'duration_minutes' => (int) $validated['duration_minutes'],
                'scheduled_at' => $scheduledAt,
                'type' => $validated['type'],
                'meeting_link' => $validated['type'] === 'online' ? trim((string) ($validated['meeting_link'] ?? '')) : null,
                'workplace_id' => $validated['type'] === 'offline' ? (int) $validated['workplace_id'] : null,
                'notes' => trim((string) ($validated['notes'] ?? '')) ?: null,
            ]);
            $interview->save();

            $interview->loadMissing(['application.candidate', 'application.job.branch', 'interviewer', 'workplace']);
            app(\App\Services\InterviewCalendarService::class)->store($interview);

            $comment = sprintf(
                '%s: %s, %s, %d phút, %s.',
                $existingInterview ? 'Đã cập nhật lịch phỏng vấn' : 'Đã tạo lịch phỏng vấn',
                $interview->scheduled_at->copy()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('H:i, d/m/Y'),
                $interview->type === 'offline' ? 'Offline' : 'Online',
                (int) ($interview->duration_minutes ?: 60),
                app(\App\Services\InterviewCalendarService::class)->resolveLocation($interview),
            );

            if ($status === \App\Enums\StatusApplicationEnum::SCREENING) {
                app(\App\Services\ApplicationPipelineService::class)->transition(
                    $application,
                    \App\Enums\StatusApplicationEnum::INTERVIEW_SCHEDULED,
                    $user,
                    $comment,
                );
            } else {
                $application->recordStatusHistory($status?->value, $status?->value, $comment);
            }

            $recipients = [];
            if (filled($application->snapshotCandidateEmail())) {
                $recipients[$application->snapshotCandidateEmail()] = 'candidate';
            }
            if (filled($interview->interviewer?->email)) {
                $recipients[$interview->interviewer->email] = 'interviewer';
            }

            $sentCount = 0;
            foreach ($recipients as $email => $label) {
                try {
                    \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\InterviewScheduledMail($interview, $label));
                    $sentCount++;
                } catch (\Throwable $exception) {
                    \Illuminate\Support\Facades\Log::warning('Failed to send HR portal interview schedule mail.', [
                        'interview_id' => $interview->id,
                        'recipient' => $email,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }

            if ($sentCount > 0) {
                $interview->forceFill(['invite_sent_at' => now()])->save();
            }

            return redirect()
                ->route('employers.application_pipeline')
                ->with('message', $existingInterview ? 'Đã cập nhật lịch phỏng vấn.' : 'Đã tạo lịch phỏng vấn.');
        })->name('application_pipeline.schedule_interview');
        Route::post('/application-pipeline/{application}/evaluate-interview', function (\App\Models\Application $application) {
            $user = auth()->user();
            $application->loadMissing(['job.branch']);
            $branchId = (int) ($application->branch_id ?: $application->job?->branch_id);

            abort_unless($user, 403);

            if ($user->branchScopeId()) {
                abort_unless((int) $user->branchScopeId() === $branchId, 403);
            } elseif (! $user->isSuperAdmin() && ! in_array($user->role, ['admin', 'director'], true)) {
                abort_unless((int) $application->job?->created_by === (int) $user->id, 403);
            }

            $status = $application->status instanceof \App\Enums\StatusApplicationEnum
                ? $application->status
                : \App\Enums\StatusApplicationEnum::tryFrom((string) $application->status);

            abort_unless(in_array($status, [
                \App\Enums\StatusApplicationEnum::INTERVIEW_SCHEDULED,
                \App\Enums\StatusApplicationEnum::INTERVIEWING,
            ], true), 403);

            $interview = $application->interviews()->latest('id')->first();

            if (! $interview) {
                return back()->with('error', 'Ho so chua co lich phong van de danh gia.');
            }

            $validated = request()->validate([
                'technical_score' => ['required', 'numeric', 'min:0', 'max:10'],
                'problem_solving_score' => ['required', 'numeric', 'min:0', 'max:10'],
                'communication_score' => ['required', 'numeric', 'min:0', 'max:10'],
                'culture_score' => ['required', 'numeric', 'min:0', 'max:10'],
                'conclusion' => ['required', \Illuminate\Validation\Rule::in(['pass', 'hold', 'fail'])],
                'notes' => ['nullable', 'string', 'max:1500'],
            ]);

            $criteria = [
                [
                    'name' => 'Kinh nghiem va chuyen mon',
                    'score' => (float) $validated['technical_score'],
                    'note' => null,
                ],
                [
                    'name' => 'Tu duy giai quyet van de',
                    'score' => (float) $validated['problem_solving_score'],
                    'note' => null,
                ],
                [
                    'name' => 'Giao tiep va phoi hop',
                    'score' => (float) $validated['communication_score'],
                    'note' => null,
                ],
                [
                    'name' => 'Phu hop van hoa FPT Education',
                    'score' => (float) $validated['culture_score'],
                    'note' => null,
                ],
            ];
            $average = round(collect($criteria)->avg('score'), 2);
            $conclusion = $validated['conclusion'];

            \Illuminate\Support\Facades\DB::transaction(function () use ($application, $interview, $user, $status, $criteria, $average, $conclusion, $validated): void {
                $scorecard = \App\Models\Scorecard::withTrashed()->firstOrNew([
                    'interview_id' => $interview->id,
                    'evaluator_id' => $user->id,
                ]);

                if (method_exists($scorecard, 'trashed') && $scorecard->trashed()) {
                    $scorecard->restore();
                }

                $scorecardData = [
                    'application_id' => $application->id,
                    'interview_id' => $interview->id,
                    'evaluator_id' => $user->id,
                    'criteria' => $criteria,
                    'average_score' => $average,
                    'conclusion' => $conclusion,
                    'notes' => trim((string) ($validated['notes'] ?? '')) ?: null,
                ];

                if (\Illuminate\Support\Facades\Schema::hasColumn('scorecards', 'recommended_conclusion')) {
                    $scorecardData['recommended_conclusion'] = $conclusion;
                }

                $scorecard->fill($scorecardData);
                $scorecard->save();

                $interview->forceFill([
                    'result' => $conclusion === 'fail' ? 'fail' : ($conclusion === 'pass' ? 'pass' : 'pending'),
                ])->save();

                $comment = 'Danh gia phong van: '.match ($conclusion) {
                    'pass' => 'Dat',
                    'fail' => 'Khong dat',
                    default => 'Can theo doi/phong van them',
                }.'. Diem TB: '.number_format($average, 2).'/10.'
                    .(filled($validated['notes'] ?? null) ? ' Nhan xet: '.trim((string) $validated['notes']) : '');

                $pipelineService = app(\App\Services\ApplicationPipelineService::class);

                if ($conclusion === 'pass') {
                    if ($status === \App\Enums\StatusApplicationEnum::INTERVIEW_SCHEDULED) {
                        $pipelineService->transition($application, \App\Enums\StatusApplicationEnum::INTERVIEWING, $user, $comment);
                        $application->refresh();
                    }

                    $pipelineService->transition($application, \App\Enums\StatusApplicationEnum::OFFERED, $user, $comment);

                    return;
                }

                if ($conclusion === 'fail') {
                    $pipelineService->transition($application, \App\Enums\StatusApplicationEnum::REJECTED, $user, $comment);

                    return;
                }

                if ($status === \App\Enums\StatusApplicationEnum::INTERVIEW_SCHEDULED) {
                    $pipelineService->transition($application, \App\Enums\StatusApplicationEnum::INTERVIEWING, $user, $comment);
                } else {
                    $application->recordStatusHistory($status?->value, $status?->value, $comment);
                }
            });

            return redirect()
                ->route('employers.application_pipeline')
                ->with('message', 'Da luu danh gia phong van.');
        })->name('application_pipeline.evaluate_interview');

        Route::post('/applications/{application}/advance', [EmployerApplicationPipelineController::class, 'advance'])->name('applications.advance');
    });
});


Route::prefix('pages')->name('pages.')->group(function () {
    Route::get('/about', PagesAbout::class)->name('about');
    Route::get('/blog', Blog::class)->name('blog');
    Route::get('/single/{post?}', Single::class)->name('single');
    Route::get('/job-page', JobPage::class)->name('job');
    Route::get('/contact', Contact::class)->name('contact');
});

Route::middleware('guest')->group(function () {
    Route::redirect('/login', '/dang-nhap')->name('login');
    Route::redirect('/register', '/dang-ky')->name('register');
});

Route::middleware(['auth', 'candidate.account'])->group(function () {
    Route::get('/candidates/candidate_dashboard', CandidateDashboard::class)->name('candidates.candidate_dashboard');
});

Route::middleware(['auth', 'employer.account'])->group(function () {
    Route::get('/employers/dashboard', EmployersDashboard::class)->name('employers.dashboard');
    Route::get('/director/approve-jobs', \App\Livewire\Client\Director\ApproveJobs::class)->name('director.approve_jobs');
});
