<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasskeyAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasskeyAuthController extends Controller
{
    public function __construct(private readonly PasskeyAuthService $passkeys) {}

    public function registerOptions(Request $request): JsonResponse
    {
        return $this->passkeys->registerOptions($request);
    }

    public function registerVerify(Request $request): JsonResponse
    {
        return $this->passkeys->registerVerify($request);
    }

    public function loginOptions(Request $request): JsonResponse
    {
        return $this->passkeys->loginOptions($request);
    }

    public function loginVerify(Request $request): JsonResponse
    {
        return $this->passkeys->loginVerify($request);
    }

    public function index(Request $request): JsonResponse
    {
        return $this->passkeys->index($request);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        return $this->passkeys->destroy($request, $id);
    }
}
