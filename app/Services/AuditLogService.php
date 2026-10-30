<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogService
{
    public function listing(Request $request)
    {
        $query = AuditLog::query()->with('user:id,nama_lengkap,username,role');

        foreach (['action', 'user_id', 'question_type'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->input('dari'));
        }

        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->input('sampai'));
        }

        return $query->latest()->paginate(25);
    }

    public function find(int|string $id): AuditLog
    {
        return AuditLog::with('user:id,nama_lengkap,username,role')->findOrFail($id);
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $perAction = AuditLog::query()
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
                    'user_id' => $row->user_id,
                    'nama' => $user?->nama_lengkap ?? 'System',
                    'role' => $user?->role ?? '-',
                    'total' => $row->total,
                ];
            });

        return [
            'total_log' => AuditLog::count(),
            'log_hari_ini' => AuditLog::whereDate('created_at', now()->toDateString())->count(),
            'per_aksi' => $perAction,
            'per_user' => $perUser,
        ];
    }
}
