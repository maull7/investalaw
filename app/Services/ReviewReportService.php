<?php

namespace App\Services;

use App\Models\Review;
use Barryvdh\DomPDF\Facade\Pdf;

class ReviewReportService
{
    /** @return array<string, mixed> */
    public function generateReportData(Review $review): array
    {
        $review->load(['reviewDocument.regulations', 'reviewer', 'findings.category', 'findings.regulation.type']);

        $findings = $review->findings;
        $compliantCount = $findings->where('compliance_status', 'compliant')->count();
        $partiallyCompliantCount = $findings->where('compliance_status', 'partially_compliant')->count();
        $nonCompliantCount = $findings->where('compliance_status', 'non_compliant')->count();

        return [
            'review' => $review,
            'document' => $review->reviewDocument,
            'reviewer' => $review->reviewer,
            'findings' => $findings,
            'summary' => [
                'total_regulations' => $findings->count(),
                'compliant' => $compliantCount,
                'partially_compliant' => $partiallyCompliantCount,
                'non_compliant' => $nonCompliantCount,
                'compliance_rate' => $findings->count() > 0
                    ? round(($compliantCount / $findings->count()) * 100, 2)
                    : 0,
            ],
        ];
    }

    public function generatePdf(Review $review): string
    {
        $data = $this->generateReportData($review);

        $pdf = Pdf::loadView('reports.review-pdf', $data);

        return $pdf->output();
    }
}
