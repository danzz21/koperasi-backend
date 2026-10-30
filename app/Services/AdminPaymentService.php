<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AdminPaymentService
{
    public function listing()
    {
        return DB::table('payment_gateway_transaksi')->paginate();
    }

    public function pendingInstallments()
    {
        return DB::table('pembayarans')
            ->where('jenis', 'cicilan')
            ->where('status', 'pending')
            ->get();
    }
}
