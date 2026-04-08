<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user:id,name,email')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->input('user_id')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->input('action')))
            ->when($request->filled('table_name'), fn ($q) => $q->where('table_name', $request->input('table_name')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $tables = AuditLog::query()
            ->select('table_name')
            ->distinct()
            ->orderBy('table_name')
            ->pluck('table_name');

        return view('admin.audit_logs.index', compact('logs', 'users', 'actions', 'tables'));
    }
}
