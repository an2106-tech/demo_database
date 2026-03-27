<?php

use App\Livewire\Client\employers\BrowseCandidates;
use App\Livewire\Client\Employers\SingleCompany;
use App\Livewire\Client\Header;
use App\Livewire\Client\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/home', Home::class)->name('home');

Route::prefix('employers')->name('employers.')->group(function () {
    
    // URL thực tế sẽ là: /employers/browse
    // Tên route để gọi trong thẻ <a> là: employers.browse
    Route::get('/browse', BrowseCandidates::class)->name('browse');
    Route::get('/single_company', SingleCompany::class)->name('single_company');
});


