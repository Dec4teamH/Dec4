<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GithubController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('dashboard',GithubController::class)->middleware(['auth', 'verified']);

// graph commit表示のルーテイング
Route::get('/graph/commit',function(){
    return view('Gitgraph',["state"=>"commit"]);
})->name("commit");
// graph merge表示のルーテイング
Route::get('/graph/merge',function(){
    return view('Gitgraph',["state"=>"merge"]);
})->name("merge");

Route::get('/popup',function(){
    return view("popup");
});

require __DIR__.'/auth.php';
