<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * GET /api/superadmin/audit-log
     */
    public function index(Request $request)
    {
        $query = AuditLog::query()->with('user:id,nama_lengkap,username,role');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('question_type')) {
            $query->where('question_type', $request->question_type);
        }

        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        return response()->json(['data' => $query->latest()->paginate(25)]);
    }

    /**
     * GET /api/superadmin/audit-log/{id}
     */
    public function show($id)
    {
        return response()->json([
            'data' => AuditLog::with('user:id,nama_lengkap,username,role')->findOrFail($id),
        ]);
    }

    /**
     * GET /api/superadmin/audit-log/ringkasan
     * Rekap aktivitas per user dan per aksi.
     */
    public function ringkasan()
    {
        $perAksi = AuditLog::query()
            ->selectRaw('action, COUNT(*) as total')
            ->groupBy('action')
            ->orderByDesc('total')
            ->get();

        $perUser = AuditLog::query()
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                $user = User::find($row->user_id);

                return [
                    'user_id'  => $row->user_id,
                    'nama'     => $user?->nama_lengkap ?? 'System',
                    'role'     => $user?->role ?? '-',
                    'total'    => $row->total,
                ];
            });

        return response()->json([
            'data' => [
                'total_log'      => AuditLog::count(),
                'log_hari_ini'   => AuditLog::whereDate('created_at', now()->toDateString())->count(),
                'per_aksi'       => $perAksi,
                'per_user'       => $perUser,
            ],
        ]);
    }
}