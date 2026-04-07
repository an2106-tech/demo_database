<?php

use App\Livewire\Client\ApplyJob;
use App\Livewire\Client\BrowseCategories;
use App\Livewire\Client\BrowseCompanies;
use App\Livewire\Client\BrowseJobs;
use App\Livewire\Client\CandidateDashboard;
use App\Livewire\Client\CandidateProfile as ClientCandidateProfile;
use App\Livewire\Client\CandidatesDetails;
use App\Livewire\Client\ChangePassword as ClientChangePassword;
use App\Livewire\Client\Earnings;
use App\Livewire\Client\Employers\BrowseCandidates;
use App\Livewire\Client\Employers\CandidateEarnings;
use App\Livewire\Client\Employers\CandidateProfile as EmployerCandidateProfile;
use App\Livewire\Client\Employers\ChangePassword as EmployerChangePassword;
use App\Livewire\Client\Employers\CompanyProfile;
use App\Livewire\Client\Employers\EmployersDashboard;
use App\Livewire\Client\Employers\ManageCandidate;
use App\Livewire\Client\Employers\Message as EmployerMessage;
use App\Livewire\Client\Employers\PostJob;
use App\Livewire\Client\Employers\SingleCompany;
use App\Livewire\Client\Employers\Transaction;
use App\Livewire\Client\Home;
use App\Livewire\Client\JobListSideBars;
use App\Livewire\Client\Login;
use App\Livewire\Client\ManageJobs;
use App\Livewire\Client\Messages;
use App\Livewire\Client\ManageJobs as CandidateManageJobs;
use App\Livewire\Client\Messages as CandidateMessages;
use App\Livewire\Client\pages\About as PagesAbout;
use App\Livewire\Client\pages\Blog;
use App\Livewire\Client\pages\Contact;
use App\Livewire\Client\pages\JobPage;
use App\Livewire\Client\pages\Register as PagesRegister;
use App\Livewire\Client\pages\Single;
use App\Livewire\Client\Sidebars;
use App\Livewire\Client\SignUp;
use App\Livewire\Client\SubmitResume;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::redirect('/candidate-profile.html', '/candidates/candidate_profile');
Route::redirect('/candidate-dashboard.html', '/candidates/candidate_dashboard');
Route::redirect('/submit-resume.html', '/candidates/submit_resume');

Route::prefix('candidates')->name('candidates.')->group(function () {
    Route::get('/browse_job', BrowseJobs::class)->name('browse_job');
    Route::get('/sidebar', Sidebars::class)->name('sidebar');
    Route::get('/joblist_sidebar', JobListSideBars::class)->name('joblist_sidebar');
    Route::get('/browse_categories', BrowseCategories::class)->name('browse_categories');
    Route::get('/browse_companies', BrowseCompanies::class)->name('browse_companies');
    Route::get('/candidate_detail', CandidatesDetails::class)->name('candidate_detail');

    Route::middleware(['auth', 'candidate.account'])->group(function () {
        Route::get('submit_resume', SubmitResume::class)->name('submit_resume');
        Route::get('candidate_dashboard', CandidateDashboard::class)->name('candidate_dashboard');
        Route::get('candidate_profile', ClientCandidateProfile::class)->name('candidate_profile');
        Route::get('jobs/{job}/apply', ApplyJob::class)->name('apply_job');
        Route::get('messages', Messages::class)->name('messages');
        Route::get('manage_jobs', ManageJobs::class)->name('manage_jobs');
        Route::get('earnings', Earnings::class)->name('earnings');
        Route::get('change_password', ClientChangePassword::class)->name('change_password');
        Route::get('/submit_resume', SubmitResume::class)->name('submit_resume');
        Route::get('/candidate_dashboard', CandidateDashboard::class)->name('candidate_dashboard');
        Route::get('/candidate_profile', ClientCandidateProfile::class)->name('candidate_profile');
        Route::get('/jobs/{job}/apply', ApplyJob::class)->name('apply_job');
        Route::get('/messages', CandidateMessages::class)->name('messages');
        Route::get('/manage_jobs', CandidateManageJobs::class)->name('manage_jobs');
        Route::get('/earnings', Earnings::class)->name('earnings');
        Route::get('/change_password', ClientChangePassword::class)->name('change_password');
    });
});

Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/sign_up', SignUp::class)->name('sign_up');
    Route::get('/post_jobs', PostJob::class)->name('post_jobs');
});

Route::prefix('employers')->name('employers.')->group(function () {
    Route::get('/browse', BrowseCandidates::class)->name('browse');
    Route::get('/single_company', SingleCompany::class)->name('single_company');
    Route::get('/post_job', PostJob::class)->name('post_job');
    Route::get('/job_detail', JobDetail::class)->name('job_detail');
    Route::get('/dashboard', EmployersDashboard::class)->name('dashboard');
    Route::get('/company-profile', CompanyProfile::class)->name('company_profile');
    Route::get('/message', EmployerMessage::class)->name('message');
    Route::get('/manage-candidates', ManageCandidate::class)->name('manage_candidates');
    Route::get('/transaction', Transaction::class)->name('transaction');
    Route::get('/change-password', EmployerChangePassword::class)->name('change_password');
    Route::get('/candidate-profile', EmployerCandidateProfile::class)->name('candidate_profile');
    Route::get('/manage-jobs', CandidateManageJobs::class)->name('manage_jobs');
    Route::get('/candidate-earnings', CandidateEarnings::class)->name('candidate_earnings');
});

Route::prefix('pages')->name('pages.')->group(function () {
    Route::get('/about', PagesAbout::class)->name('about');
    Route::get('/blog', Blog::class)->name('blog');
    Route::get('/single', Single::class)->name('single');
    Route::get('/job-page', JobPage::class)->name('job');
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', PagesRegister::class)->name('register');
    Route::get('/contact', Contact::class)->name('contact');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', PagesRegister::class)->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/candidates/candidate_dashboard', CandidateDashboard::class)->name('candidates.candidate_dashboard');
    Route::get('/employers/dashboard', EmployersDashboard::class)->name('employers.dashboard');
});
