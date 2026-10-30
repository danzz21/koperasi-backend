<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;

class AdminDashboardController extends Controller
{
    public function __construct(private readonly AdminDashboardService $dashboard) {}

    public function index()
    {
        return response()->json(['data' => $this->dashboard->overview()]);
    }

    public function stats()
    {
        return response()->json($this->dashboard->stats());
    }
}
