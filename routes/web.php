<?php

use App\Livewire\Client\BrowseCategories;
use App\Livewire\Client\BrowseCompanies;
use App\Livewire\Client\BrowseJobs;
use App\Livewire\Client\CandidateDashboard;
use App\Livewire\Client\CandidateProfile;
use App\Livewire\Client\CandidatesDetails;
use App\Livewire\Client\ChangePassword;
use App\Livewire\Client\Earnings;
use App\Livewire\Client\employers\BrowseCandidates;
use App\Livewire\Client\Employers\EmployersDashboard;
use App\Livewire\Client\Employers\PostJob;
use App\Livewire\Client\Employers\SingleCompany;
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
Route::prefix('candidates')->name('candidates.')->group(function(){
    route::get('/browse_job',BrowseJobs::class)->name('browse_job');
    route::get('/sidebar',Sidebars::class)->name('sidebar');
    route::get('joblist_sidebar',JobListSideBars::class)->name('joblist_sidebar');
    route::get('browse_categories',BrowseCategories::class)->name('browse_categories');
    route::get('browse_companies', BrowseCompanies::class)->name('browse_companies');
    route::get('candidate_detail', CandidatesDetails::class)->name('candidate_detail');
    route::get('submit_resume',SubmitResume::class)->name('submit_resume');
    route::get('candidate_dashboard',CandidateDashboard::class)->name('candidate_dashboard');
    route::get('candidate_profile',CandidateProfile::class)->name('candidate_profile');
    route::get('messages',Messages::class)->name('messages');
    route::get('manage_jobs',ManageJobs::class)->name('manage_jobs');
    route::get('earnings',Earnings::class)->name('earnings');
    route::get('change_password',ChangePassword::class)->name('change_password');
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
});


