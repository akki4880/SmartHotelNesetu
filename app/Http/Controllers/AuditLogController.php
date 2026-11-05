<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AuditLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:audit_logs,view', only: ['index', 'show']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = AuditLog::with(['user', 'hotel'])
            ->where('hotel_id', auth()->user()->hotel_id)
            ->latest();

        // 🔍 Filters 

        // Date Range
        if (request('from_date')) {
            $query->whereDate('created_at', '>=', request('from_date'));
        }

        if (request('to_date')) {
            $query->whereDate('created_at', '<=', request('to_date'));
        }

        // User Name (relation)
        if (request('user_name')) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . request('user_name') . '%');
            });
        }

        // Module
        if (request('module')) {
            $query->where('module', 'like', '%' . request('module') . '%');
        }

        // Action
        if (request('action')) {
            $query->where('action', request('action'));
        }

        // ✅ Get unique users from audit logs
        $users = \App\Models\User::whereIn('id', function ($q) {
            $q->select('user_id')
                ->from('audit_logs')
                ->whereNotNull('user_id');
        })->get();

        // ✅ Get unique modules
        $modules = AuditLog::select('module')
            ->where('hotel_id', auth()->user()->hotel_id)
            ->whereNotNull('module')
            ->distinct()
            ->pluck('module');
        
        $actions = \App\Models\AuditLog::select('action')
            ->where('hotel_id', auth()->user()->hotel_id)
            ->whereNotNull('action')
            ->distinct()
            ->pluck('action');

        // Final Data
        $audits = $query->paginate(perPage())->withQueryString();

        return view('pages.audit-logs.index', compact('audits', 'users', 'modules','actions'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AuditLog $auditLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AuditLog $auditLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AuditLog $auditLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuditLog $auditLog)
    {
        //
    }
}
