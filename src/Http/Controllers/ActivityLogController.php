<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Safarjaisur\AdminPanel\Models\ActivityLog;
use Safarjaisur\AdminPanel\Models\AdminUser;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%' . $request->string('search') . '%';
                $query->where(function ($query) use ($search): void {
                    $query->where('action', 'like', $search)
                        ->orWhere('subject_type', 'like', $search)
                        ->orWhere('ip_address', 'like', $search);
                });
            })
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->input('user_id')))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->input('action')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->paginate(config('sjadminpanel.pagination.per_page'))
            ->withQueryString();

        return view('sjadminpanel::activity.index', [
            'logs' => $logs,
            'users' => AdminUser::query()->orderBy('name')->get(['id', 'name']),
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
