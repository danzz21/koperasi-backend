<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SystemConfig;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemConfigController extends Controller
{
    /**
     * GET /api/superadmin/config
     */
    public function index(Request $request)
    {
        $query = SystemConfig::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('key', 'like', "%{$q}%")
                    ->orWhere('label', 'like', "%{$q}%");
            });
        }

        return response()->json(['data' => $query->orderBy('key')->get()]);
    }

    /**
     * POST /api/superadmin/config
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key'         => 'required|string|max:100|unique:system_configs,key',
            'value'       => 'required',
            'label'       => 'nullable|string|max:150',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $config = SystemConfig::setValue(
            $request->key,
            $request->value,
            $request->label,
            $request->description,
        );

        AuditLogger::log('system_config.create', $config, [], [
            'key'   => $config->key,
            'value' => $config->value,
        ]);

        return response()->json(['message' => 'Konfigurasi berhasil disimpan.', 'data' => $config], 201);
    }

    /**
     * GET /api/superadmin/config/{key}
     */
    public function getValue($key)
    {
        $config = SystemConfig::where('key', $key)->first();

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
            'value'       => 'required',
            'label'       => 'nullable|string|max:150',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $config = SystemConfig::where('key', $key)->first();
        $old    = $config?->value;

        $config = SystemConfig::setValue(
            $key,
            $request->value,
            $request->label,
            $request->description,
        );

        AuditLogger::log('system_config.update', $config, ['value' => $old], [
            'key'   => $config->key,
            'value' => $config->value,
        ]);

        return response()->json(['message' => 'Konfigurasi berhasil diubah.', 'data' => $config]);
    }

    /**
     * DELETE /api/superadmin/config/{id}
     */
    public function destroy($id)
    {
        $config = SystemConfig::findOrFail($id);

        AuditLogger::log('system_config.delete', $config, ['key' => $config->key, 'value' => $config->value]);

        $config->delete();

        return response()->json(['message' => 'Konfigurasi berhasil dihapus.']);
    }
}