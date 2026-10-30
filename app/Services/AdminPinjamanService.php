<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AdminPinjamanService
{
    public function listing()
    {
        return DB::table('pinjamen')->paginate();
    }

    public function pending()
    {
        return DB::table('pinjamen')->where('status', 'pending')->get();
    }
}
