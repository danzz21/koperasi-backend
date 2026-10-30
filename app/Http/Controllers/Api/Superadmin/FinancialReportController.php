<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\FinancialReportService;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function __construct(private readonly FinancialReportService $reports) {}

    /**
     * GET /api/superadmin/financial-report?year=2026
     * Laporan laba rugi & neraca ringkas (khusus superadmin).
     */
    public function index(Request $request)
    {
        $year = (int) ($request->year ?? now()->year);

        return response()->json([
            'data' => $this->reports->annual($year),
        ]);
    }

    /**
     * GET /api/superadmin/financial-report/daily?tanggal=2026-09-18
     */
    public function dailyReport(Request $request)
    {
        $tanggal = $request->tanggal ?? now()->toDateString();

        return response()->json([
            'data' => $this->reports->daily($tanggal),
        ]);
    }
}
