<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JiraController;

Route::get('/', [JiraController::class, 'index'])->name('index');
Route::post('/issues', [JiraController::class, 'store'])->name('issues.store');
Route::delete('/issues/{key}', [JiraController::class, 'destroy'])->name('issues.destroy');
Route::put('/issues/{issueKey}', [JiraController::class, 'update'])->name('issues.update');

Route::get('/issues/{issueKey}/comments', [JiraController::class, 'getComments'])->name('comments.get');
Route::delete('/issues/{issueKey}/comments/{commentId}', [JiraController::class, 'destroyComment'])->name('comments.destroy');
Route::post('/issues/{issueKey}/comments', [JiraController::class, 'storeComment'])->name('comments.store');
Route::put('/issues/{issueKey}/comments/{commentId}', [JiraController::class, 'updateComment'])->name('comments.update');

