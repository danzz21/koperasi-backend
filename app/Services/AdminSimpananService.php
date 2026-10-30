<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AdminSimpananService
{
    public function listing()
    {
        return DB::table('simpanans')->paginate();
    }

    public function pending()
    {
        return DB::table('simpanans')->where('status', 'tidak_aktif')->get();
    }
}
