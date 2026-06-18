<?php

use App\Http\Controllers\AiPreviewController;
use App\Http\Controllers\AiPromptController;
use App\Http\Controllers\AiSummaryController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegulationCategoryController;
use App\Http\Controllers\RegulationController;
use App\Http\Controllers\RegulationTypeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewDocumentController;
use App\Http\Controllers\ReviewReportController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});
//
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('regulation-categories', RegulationCategoryController::class);
    Route::post('/regulation-categories/{regulationCategory}/upload', [RegulationCategoryController::class, 'uploadFile'])->name('regulation-categories.upload-file');
    Route::delete('/regulation-categories/file/{file}', [RegulationCategoryController::class, 'deleteFile'])->name('regulation-categories.delete-file');
    Route::get('/regulation-categories/file/{file}/view', [RegulationCategoryController::class, 'viewFile'])->name('regulation-categories.view-file');

    // Sub Category management
    Route::post('/regulation-categories/{regulationCategory}/sub-categories', [RegulationCategoryController::class, 'storeSubCategory'])->name('sub-categories.store');
    Route::put('/sub-categories/{subCategory}', [RegulationCategoryController::class, 'updateSubCategory'])->name('sub-categories.update');
    Route::patch('/sub-categories/{subCategory}/toggle', [RegulationCategoryController::class, 'toggleSubCategory'])->name('sub-categories.toggle');
    Route::delete('/sub-categories/{subCategory}', [RegulationCategoryController::class, 'destroySubCategory'])->name('sub-categories.destroy');

    // Sub Category index page
    Route::get('/sub-categories', [SubCategoryController::class, 'index'])->name('sub-categories.index');
    Route::post('/sub-categories', [SubCategoryController::class, 'store'])->name('sub-categories.create');

    // Regulation Types
    Route::resource('regulation-types', RegulationTypeController::class);

    // Regulations
    Route::get('/regulations/search', [RegulationController::class, 'search'])->name('regulations.search');
    Route::resource('regulations', RegulationController::class);
    Route::post('/regulations/{regulation}/documents', [RegulationController::class, 'uploadDocument'])->name('regulations.documents.store');
    Route::delete('/regulations/documents/{document}', [RegulationController::class, 'deleteDocument'])->name('regulations.documents.destroy');
    Route::get('/regulations/documents/{document}/view', [RegulationController::class, 'viewDocument'])->name('regulations.documents.view');

    Route::resource('review-documents', ReviewDocumentController::class);
    Route::post('/review-documents/{reviewDocument}/submit', [ReviewDocumentController::class, 'submit'])->name('review-documents.submit');
    Route::get('/review-documents/{reviewDocument}/file', [ReviewDocumentController::class, 'viewFile'])->name('review-documents.view-file');

    Route::get('/reviews/{reviewDocument}/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::resource('reviews', ReviewController::class)->except(['create']);
    Route::post('/reviews/{review}/complete', [ReviewController::class, 'complete'])->name('reviews.complete');
    Route::post('/reviews/{review}/request-revision', [ReviewController::class, 'requestRevision'])->name('reviews.request-revision');
    Route::post('/reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');

    Route::get('/reports/{review}', [ReviewReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{review}/pdf', [ReviewReportController::class, 'exportPdf'])->name('reports.pdf');

    // User management
    Route::resource('users', UserController::class);

    // AI Summaries
    Route::post('/review-documents/{reviewDocument}/ai-summaries/generate', [AiSummaryController::class, 'generate'])->name('ai-summaries.generate');
    Route::get('/review-documents/{reviewDocument}/ai-summaries/{aiSummary}/prompt', [AiSummaryController::class, 'checkPrompt'])->name('ai-summaries.check-prompt');
    Route::get('/review-documents/{reviewDocument}/ai-summaries', [AiSummaryController::class, 'index'])->name('ai-summaries.index');
    Route::get('/review-documents/{reviewDocument}/ai-summaries/{aiSummary}', [AiSummaryController::class, 'show'])->name('ai-summaries.show');

    // AI Preview
    Route::get('/review-documents/{reviewDocument}/ai-preview', [AiPreviewController::class, 'show'])->name('ai-preview.show');
    Route::post('/review-documents/{reviewDocument}/ai-preview/generate', [AiPreviewController::class, 'generate'])->name('ai-preview.generate');

    // AI Prompts management
    Route::resource('ai-prompts', AiPromptController::class);
});
