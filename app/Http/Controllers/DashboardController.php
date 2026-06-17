<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $documentsQuery = ReviewDocument::query();
        $reviewsQuery = Review::query();

        if (! $user->isAdmin() && ! $user->isSubAdmin() && ! $user->isReviewer()) {
            $documentsQuery->where('user_id', $user->id);
            $reviewsQuery->whereHas('reviewDocument', fn ($q) => $q->where('user_id', $user->id));
        }

        if ($user->isReviewer()) {
            $reviewsQuery->where('reviewer_id', $user->id);
        }

        $stats = [
            'total_documents' => $documentsQuery->count(),
            'pending_documents' => (clone $documentsQuery)->where('status', 'submitted')->count(),
            'approved_documents' => (clone $documentsQuery)->where('status', 'approved')->count(),
            'total_reviews' => $reviewsQuery->count(),
        ];

        $recentDocuments = $documentsQuery->with('user')->latest()->take(5)->get();

        return view('dashboard.index', compact('stats', 'recentDocuments'));
    }
}
