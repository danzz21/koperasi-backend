<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index(Request $request)
    {
        return response()->json($this->notifications->forMember($request->user()));
    }

    public function markRead(Request $request, string $id)
    {
        $this->notifications->markRead($request->user(), $id);

        return response()->json(['message' => 'Notifikasi ditandai sudah dibaca.']);
    }
}
