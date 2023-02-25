<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GithubController;
use App\Http\Controllers\DetailController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\IssueController;
use Illuminate\Cache\Repository;

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
Route::group(['middleware' => 'auth'], function () {
    Route::resource('dashboard',GithubController::class)->middleware(['auth', 'verified']);

    Route::resource('detail',DetailController::class);
    Route::get('/pullrequest/{id}', [DetailController::class, 'pullrequest']);

    Route::resource('repository',RepositoryController::class);

    Route::resource('organization',OrganizationController::class);

    Route::resource('issue',IssueController::class);
});

// graph commit表示のルーテイング
Route::get('/graph/commit',function(){
    return view('Gitgraph',["state"=>"commit"]);
})->name("commit");
// graph merge表示のルーテイング
Route::get('/graph/merge',function(){
    return view('Gitgraph',["state"=>"merge"]);
})->name("merge");

Route::get('graph/issue',function(){
    return view('Gitissue_view');
})->name('issue');


require __DIR__.'/auth.php';
