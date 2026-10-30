<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\SystemConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemConfigController extends Controller
{
    public function __construct(private readonly SystemConfigService $configs) {}

    /**
     * GET /api/superadmin/config
     */
    public function index(Request $request)
    {
        return response()->json(['data' => $this->configs->listing($request->input('q'))]);
    }

    /**
     * POST /api/superadmin/config
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:100|unique:system_configs,key',
            'value' => 'required',
            'label' => 'nullable|string|max:150',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $config = $this->configs->create($validator->validated());

        return response()->json(['message' => 'Konfigurasi berhasil disimpan.', 'data' => $config], 201);
    }

    /**
     * GET /api/superadmin/config/{key}
     */
    public function getValue($key)
    {
        $config = $this->configs->find($key);

        if (! $config) {
            return response()->json(['message' => 'Konfigurasi tidak ditemukan.'], 404);
        }

        return response()->json(['data' => $config]);
    }

    /**
     * PUT /api/superadmin/config/{key}
     */
    public function setValue(Request $request, $key)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required',
            'label' => 'nullable|string|max:150',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $config = $this->configs->update($key, $validator->validated());

        return response()->json(['message' => 'Konfigurasi berhasil diubah.', 'data' => $config]);
    }

    /**
     * DELETE /api/superadmin/config/{id}
     */
    public function destroy($id)
    {
        $this->configs->delete($id);

        return response()->json(['message' => 'Konfigurasi berhasil dihapus.']);
    }
}
