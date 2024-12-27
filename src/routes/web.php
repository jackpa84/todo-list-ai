<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;


Route::get('/', function () {
    return redirect()->route('tasks.index');
});

Auth::routes();


Route::middleware('auth')->group(function () {
    Route::resource('tasks', TaskController::class);
    Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('tasks/prioritize', [TaskController::class, 'prioritizeTasks'])->name('tasks.prioritize');
    Route::post('chatbot', [TaskController::class, 'chatbot'])->name('chatbot');
    Route::post('/generate-task-details', [TaskController::class, 'generateTaskDetails'])->name('tasks.generate.details');
});


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
