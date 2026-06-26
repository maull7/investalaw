<?php

use App\Http\Controllers\AiPreviewController;
use App\Http\Controllers\AiPromptController;
use App\Http\Controllers\AiSummaryController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentPartitionController;
use App\Http\Controllers\RegulationCategoryController;
use App\Http\Controllers\RegulationController;
use App\Http\Controllers\RegulationTypeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewDocumentController;
use App\Http\Controllers\ReviewReportController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\UserController;
use App\Models\Regulation;
use App\Models\ReviewDocument;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});
// //
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});
// //
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
    Route::post('/regulations/{regulation}/parse', [RegulationController::class, 'parseRegulation'])->name('regulations.parse');
    Route::post('/regulations/{regulation}/documents/{document}/parse', [RegulationController::class, 'parseDocument'])->name('regulations.documents.parse');
    Route::post('/regulations/{regulation}/documents', [RegulationController::class, 'uploadDocument'])->name('regulations.documents.store');
    Route::delete('/regulations/documents/{document}', [RegulationController::class, 'deleteDocument'])->name('regulations.documents.destroy');
    Route::get('/regulations/documents/{document}/view', [RegulationController::class, 'viewDocument'])->name('regulations.documents.view');
    Route::get('/regulations/{regulation}/file', [RegulationController::class, 'viewFile'])->name('regulations.file');
    Route::get('/regulations/{regulation}/analyze', [RegulationController::class, 'analyze'])->name('regulations.analyze');
    Route::post('/regulations/{regulation}/reanalyze', [RegulationController::class, 'reanalyze'])->name('regulations.reanalyze');

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
    Route::post('/review-documents/{reviewDocument}/ai-summaries/generate', [AiSummaryController::class, 'generate'])->name('ai-summaries.generate')->middleware('throttle:3,1');
    Route::get('/review-documents/{reviewDocument}/ai-summaries/{aiSummary}/prompt', [AiSummaryController::class, 'checkPrompt'])->name('ai-summaries.check-prompt');
    Route::get('/review-documents/{reviewDocument}/ai-summaries', [AiSummaryController::class, 'index'])->name('ai-summaries.index');
    Route::get('/review-documents/{reviewDocument}/ai-summaries/{aiSummary}', [AiSummaryController::class, 'show'])->name('ai-summaries.show');

    // AI Preview
    Route::get('/review-documents/{reviewDocument}/ai-preview', [AiPreviewController::class, 'show'])->name('ai-preview.show');
    Route::post('/review-documents/{reviewDocument}/ai-preview/generate', [AiPreviewController::class, 'generate'])->name('ai-preview.generate')->middleware('throttle:3,1');

    // Document Partitions
    Route::get('/review-documents/{reviewDocument}/partitions', [DocumentPartitionController::class, 'index'])->name('partitions.index');
    Route::post('/review-documents/{reviewDocument}/partitions', [DocumentPartitionController::class, 'store'])->name('partitions.store');
    Route::post('/review-documents/{reviewDocument}/partitions/{documentPartition}/extract-toc', [DocumentPartitionController::class, 'extractToc'])->name('partitions.extract-toc');
    Route::get('/review-documents/{reviewDocument}/partitions/{documentPartition}/debug-toc', [DocumentPartitionController::class, 'debugToc'])->name('partitions.debug-toc');
    Route::get('/review-documents/{reviewDocument}/partitions/parsed-text', [DocumentPartitionController::class, 'showParsedText'])->name('partitions.parsed-text');
    Route::get('/review-documents/{reviewDocument}/partitions/regulations', [DocumentPartitionController::class, 'showRegulations'])->name('partitions.regulations');
    Route::post('/review-documents/{reviewDocument}/partitions/analyse', [DocumentPartitionController::class, 'generateAnalysis'])->name('partitions.analyse')->middleware('throttle:3,1');
    Route::post('/review-documents/{reviewDocument}/partitions/{documentPartition}/analysis', [DocumentPartitionController::class, 'saveAnalysis'])->name('partitions.save-analysis');
    Route::post('/review-documents/{reviewDocument}/partitions/{documentPartition}/detect-structure', [DocumentPartitionController::class, 'detectStructure'])->name('partitions.detect-structure')->middleware('throttle:3,1');
    Route::post('/review-documents/{reviewDocument}/bab-structures/{documentBabStructure}/detect', [DocumentPartitionController::class, 'detectStructure'])->name('bab-structures.detect')->middleware('throttle:3,1');
    Route::post('/review-documents/{reviewDocument}/bab-structures/{documentBabStructure}/detect-ajax', [DocumentPartitionController::class, 'detectStructureAjax'])->name('bab-structures.detect-ajax')->middleware('throttle:3,1');
    Route::post('/review-documents/{reviewDocument}/bab-structures/batch-detect', [DocumentPartitionController::class, 'batchDetectStructure'])->name('bab-structures.batch-detect')->middleware('throttle:3,1');
    Route::post('/review-documents/{reviewDocument}/partitions/parse-pdf', [DocumentPartitionController::class, 'parsePdf'])->name('partitions.parse-pdf')->middleware('throttle:3,1');
    Route::get('/review-documents/{reviewDocument}/partitions/{documentPartition}/content', [DocumentPartitionController::class, 'showPartitionContent'])->name('partitions.content');

    // AI Prompts management
    Route::resource('ai-prompts', AiPromptController::class);

    // TEMP DEBUG: Show regulation text directly
    Route::get('/debug-reg-text/{id}', function ($id) {
        $reg = Regulation::find($id);
        if (! $reg || ! $reg->parsed_text) {
            return 'No text';
        }

        return '<pre>'.e(mb_substr($reg->parsed_text, 0, 1000)).'</pre>';
    })->name('debug.reg-text');

    // TEMP DEBUG: Test parsed-text view directly
    Route::get('/debug-parsed-view', function () {
        try {
            $user = User::first();
            Auth::login($user);
            $rd = ReviewDocument::find(2);

            $debug = [];
            $debug[] = 'User: '.auth()->user()->name;
            $debug[] = "Doc: {$rd->id} - {$rd->title}";
            $debug[] = 'Regs count: '.$rd->regulations()->count();
            $debug[] = 'isParsed: '.($rd->isParsed() ? 'yes' : 'no');

            $reg = $rd->regulations()->first();
            if ($reg) {
                $debug[] = "Reg {$reg->id}: parsed=".($reg->isParsed() ? 'yes' : 'no').' text_len='.mb_strlen($reg->parsed_text ?? '');
            }

            $result = app(DocumentPartitionController::class)->showParsedText($rd);
            $debug[] = 'Controller returned: '.get_class($result);
            $debug[] = 'View name: '.$result->getName();

            $data = $result->getData();
            $debug[] = 'Regulations in view data: '.count($data['regulations'] ?? []);
            if (! empty($data['regulations'])) {
                $debug[] = 'First reg has_text: '.($data['regulations'][0]['has_text'] ? 'yes' : 'no');
                $debug[] = 'First reg main_parsed: '.($data['regulations'][0]['main_parsed'] ? 'yes' : 'no');
                $debug[] = 'First reg main_chars: '.$data['regulations'][0]['main_chars'];
            }

            $html = $result->render();
            $debug[] = 'HTML length: '.strlen($html);
            $debug[] = 'Has Regulasi Acuan: '.(strpos($html, 'Regulasi Acuan') !== false ? 'yes' : 'no');
            $debug[] = 'Has File Regulasi Utama: '.(strpos($html, 'File Regulasi Utama') !== false ? 'yes' : 'no');
            $debug[] = 'Has OTORITAS: '.(strpos($html, 'OTORITAS') !== false ? 'yes' : 'no');

            return response('<pre>'.implode("\n", $debug).'</pre>');
        } catch (Exception $e) {
            return response('ERROR: '.$e->getMessage()."\nFile: ".$e->getFile().':'.$e->getLine());
        }
    })->name('debug.parsed-view');
});
