<?php

use App\Livewire\Client\Header;
use App\Livewire\Client\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
route::get('/home', Home::class)->name('home');


