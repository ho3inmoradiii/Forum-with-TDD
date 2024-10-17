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

Route::resource('threads', ThreadsController::class)->except(['edit', 'update', 'destroy']);

Route::group(['prefix' => 'threads', 'middleware' => 'auth'], function () {
    Route::get('/create', [ThreadsController::class, 'create'])->name('threads.create');
    Route::post('/', [ThreadsController::class, 'store'])->name('threads.store');
});

Route::post('/threads/{thread}/replies', [RepliesController::class, 'store'])->name('replies.store');
