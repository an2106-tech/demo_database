<?php

use App\Http\Controllers\JobApprovalController;
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
use App\Livewire\Client\PostJobs as ClientPostJobs;
use App\Livewire\Client\Register as PagesRegister;
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
        ->middleware('signed:relative')
        ->name('respond.accept');
    Route::get('/{offer}/respond/decline', [OfferResponseController::class, 'decline'])
        ->middleware('signed:relative')
        ->name('respond.decline');
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
    Route::get('/single-company', SingleCompany::class)->name('single_company');
    Route::get('/job-detail/{id}', JobDetail::class)->name('job_detail');

    Route::middleware(['auth', 'employer.account'])->group(function () {
        Route::get('/post-job', PostJob::class)->name('post_job');
        Route::get('/edit-job/{id}', PostJob::class)->name('edit_job');
        Route::get('/dashboard', EmployersDashboard::class)->name('dashboard');
        Route::get('/company-profile', CompanyProfile::class)->name('company_profile');
        Route::get('/message', EmployerMessage::class)->name('message');
        Route::get('/manage-candidates', ManageCandidate::class)->name('manage_candidates');
        Route::get('/transaction', Transaction::class)->name('transaction');
        Route::get('/change-password', EmployerChangePassword::class)->name('change_password');
        Route::get('/candidate-profile', EmpCandidateProfile::class)->name('candidate_profile');
        Route::get('/manage-jobs', EmployerManageJobs::class)->name('manage_jobs');
        Route::get('/candidate-earnings', CandidateEarnings::class)->name('candidate_earnings');
        Route::get('/application-pipeline', \App\Livewire\Client\Employers\ApplicationPipeline::class)->name('application_pipeline');
    });
});


Route::prefix('pages')->name('pages.')->group(function () {
    Route::get('/about', PagesAbout::class)->name('about');
    Route::get('/blog', Blog::class)->name('blog');
    Route::get('/single', Single::class)->name('single');
    Route::get('/job-page', JobPage::class)->name('job');
    Route::get('/contact', Contact::class)->name('contact');
});

Route::middleware('guest')->group(function () {
    Route::redirect('/login', '/dang-nhap')->name('login');
    Route::redirect('/register', '/dang-ky')->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/candidates/candidate_dashboard', CandidateDashboard::class)->name('candidates.candidate_dashboard');
    Route::get('/employers/dashboard', EmployersDashboard::class)->name('employers.dashboard');
    Route::get('/director/approve-jobs', \App\Livewire\Client\Director\ApproveJobs::class)->name('director.approve_jobs');
});
