<?php

use App\Http\Controllers\ThreadsController;
use App\Http\Controllers\RepliesController;
use App\Http\Controllers\ChannelsController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';

Route::group(['prefix' => 'threads', 'middleware' => 'auth'], function () {
    Route::get('/create', [ThreadsController::class, 'create'])->name('threads.create');
    Route::post('/', [ThreadsController::class, 'store'])->name('threads.store');
});
Route::group(['middleware' => 'auth'], function () {
    Route::post('/replies/{reply}/favorite', [FavoriteController::class, 'store'])->name('reply.favorite.store');
    Route::delete('/replies/{reply}/favorite', [FavoriteController::class, 'delete'])->name('reply.favorite.delete');

    Route::delete('/threads/{thread}', [ThreadsController::class, 'destroy'])->name('threads.destroy');

    Route::post('/threads/{channel}/{thread}/replies', [RepliesController::class, 'store'])->name('replies.store');
    Route::delete('/replies/{reply}', [RepliesController::class, 'destroy'])->name('replies.destroy');
    Route::put('/replies/{reply}', [RepliesController::class, 'update'])->name('replies.update');
});

Route::get('/threads', [ThreadsController::class, 'index'])->name('threads.index');
Route::get('/threads/{channel}/{thread}', [ThreadsController::class, 'show'])->name('threads.show');
Route::get('/threads/{channel}/{thread}/replies', [RepliesController::class, 'index'])->name('replies.index');
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
