<?php

namespace App\Http\Controllers;

use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'in:admin,sub_admin,reviewer'],
            'action' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $actions = UserActivityLog::query()
            ->whereHas('user', fn ($query) => $query->where('role', '!=', 'user'))
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $logs = UserActivityLog::query()
            ->with('user:id,name,role')
            ->whereHas('user', fn ($query) => $query->where('role', '!=', 'user'))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->whereAny(['action', 'description', 'subject_type'], 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->whereAny(['name', 'email'], 'like', "%{$search}%"));
                });
            })
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->whereHas('user', fn ($query) => $query->where('role', $role)))
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('activity-logs.index', compact('logs', 'actions'));
    }
}
