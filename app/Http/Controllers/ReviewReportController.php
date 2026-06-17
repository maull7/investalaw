<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\ReviewReportService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReviewReportController extends Controller
{
    public function __construct(
        private readonly ReviewReportService $reportService
    ) {}

    public function show(Review $review): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);
        $this->authorize('view', $review);

        $reportData = $this->reportService->generateReportData($review);

        return view('reports.show', $reportData);
    }

    public function exportPdf(Review $review): Response
    {
        abort_if(auth()->user()->isSubAdmin(), 403);
        $this->authorize('view', $review);

        $pdfContent = $this->reportService->generatePdf($review);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="review-report-'.$review->id.'.pdf"');
    }
}
