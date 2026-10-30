<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogs) {}

    /**
     * GET /api/superadmin/audit-log
     */
    public function index(Request $request)
    {
        return response()->json(['data' => $this->auditLogs->listing($request)]);
    }

    /**
     * GET /api/superadmin/audit-log/{id}
     */
    public function show($id)
    {
        return response()->json([
            'data' => $this->auditLogs->find($id),
        ]);
    }

    /**
     * GET /api/superadmin/audit-log/ringkasan
     * Rekap aktivitas per user dan per aksi.
     */
    public function ringkasan()
    {
        return response()->json(['data' => $this->auditLogs->summary()]);
    }
}
