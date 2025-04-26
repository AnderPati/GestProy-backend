<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\FolderController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', [ProfileController::class, 'show']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::delete('/profile/delete-image', [ProfileController::class, 'deleteImage']);
    Route::get('/profile/storage-usage', [ProfileController::class, 'storageUsage']);

    Route::apiResource('projects', ProjectController::class);

    Route::apiResource('projects.tasks', TaskController::class)->shallow();

    Route::get('/projects/{project}/files', [ProjectFileController::class, 'index']);
    Route::post('/projects/{project}/files', [ProjectFileController::class, 'store']);
    Route::get('/files/{file}/download', [ProjectFileController::class, 'download']);
    Route::put('/files/{file}', [ProjectFileController::class, 'update']);
    Route::delete('/files/{file}', [ProjectFileController::class, 'destroy']);
    Route::get('/folders/{folder}/download', [ProjectFileController::class, 'downloadFolder']);

    Route::get('/projects/{project}/folders', [FolderController::class, 'index']);
    Route::post('/projects/{project}/folders', [FolderController::class, 'store']);
    Route::put('/projects/{project}/folders/{folder}', [FolderController::class, 'update']);
    Route::delete('/projects/{project}/folders/{folder}', [FolderController::class, 'destroy']);    
});