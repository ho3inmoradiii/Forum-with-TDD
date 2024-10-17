<?php

use App\Http\Controllers\ThreadsController;
use App\Http\Controllers\RepliesController;
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

Route::get('/threads', [ThreadsController::class, 'index'])->name('threads.index');
Route::get('/threads/{thread}', [ThreadsController::class, 'show'])->name('threads.show');
Route::post('/threads', [ThreadsController::class, 'store'])->name('threads.store')->middleware('auth');

Route::post('/threads/{thread}/replies', [RepliesController::class, 'store'])->name('replies.store');
