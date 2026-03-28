<?php

use App\Livewire\Client\About;
use App\Livewire\Client\Auth\Register;
use App\Livewire\Client\BrowseCategories;
use App\Livewire\Client\BrowseCompanies;
use App\Livewire\Client\BrowseJobs;
use App\Livewire\Client\CandidatesDetails;
use App\Livewire\Client\employers\BrowseCandidates;
use App\Livewire\Client\Employers\CandidateEarnings;
use App\Livewire\Client\Employers\CandidateProfile;
use App\Livewire\Client\Employers\ChangePassword;
use App\Livewire\Client\Employers\CompanyProfile;
use App\Livewire\Client\Employers\EmployersDashboard;
use App\Livewire\Client\Employers\ManageCandidate;
use App\Livewire\Client\Employers\ManageJobs;
use App\Livewire\Client\Employers\Message;
use App\Livewire\Client\Employers\PostJob;
use App\Livewire\Client\Employers\SingleCompany;
use App\Livewire\Client\Employers\Transaction;
use App\Livewire\Client\Home;
use App\Livewire\Client\JobListSideBars;
use App\Livewire\Client\Login;
use App\Livewire\Client\ManageJobs;
use App\Livewire\Client\Messages;
use App\Livewire\Client\Sidebars;
use App\Livewire\Client\SignUp;
use App\Livewire\Client\SubmitResume;
use Illuminate\Support\Facades\Route;


Route::get('/', Home::class)->name('home');
Route::prefix('candidates')->name('candidates.')->group(function () {
    route::get('/browse_job', BrowseJobs::class)->name('browse_job');
    route::get('/sidebar', Sidebars::class)->name('sidebar');
    route::get('joblist_sidebar', JobListSideBars::class)->name('joblist_sidebar');
    route::get('browse_categories', BrowseCategories::class)->name('browse_categories');
    route::get('browse_companies', BrowseCompanies::class)->name('browse_companies');
    route::get('candidate_detail', CandidatesDetails::class)->name('candidate_detail');
});
Route::prefix('auth')->name('auth.')->group(function(){
    route::get('/login',Login::class)->name('login');
    route::get('sign_up',SignUp::class)->name('sign_up');
    route::get('post_jobs',PostJob::class)->name('post_jobs');
});
Route::prefix('employers')->name('employers.')->group(function () {

    // URL thực tế sẽ là: /employers/browse
    // Tên route để gọi trong thẻ <a> là: employers.browse
    Route::get('/browse', BrowseCandidates::class)->name('browse');
    Route::get('/single_company', SingleCompany::class)->name('single_company');
    Route::get('/post_job', PostJob::class)->name('post_job');
    Route::get('/dashboard', EmployersDashboard::class)->name('dashboard');
    Route::get('/company-profile', CompanyProfile::class)->name('company_profile');
    Route::get('/message', Message::class)->name('message');
    Route::get('/manage-candidates', ManageCandidate::class)->name('manage_candidates');
    Route::get('/transaction', Transaction::class)->name('transaction');
    Route::get('/change-password', ChangePassword::class)->name('change_password');
    Route::get('/candidate-profile', CandidateProfile::class)->name('candidate_profile');
    Route::get('/manage-jobs', ManageJobs::class)->name('manage_jobs');
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

