<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SimpananController;
use App\Http\Controllers\Api\PinjamanController;
use App\Http\Controllers\Api\CicilanController;
use App\Http\Controllers\Api\ProfilController;
use App\Http\Controllers\Api\PpobController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminAnggotaController;
use App\Http\Controllers\Api\Admin\AdminPinjamanController;
use App\Http\Controllers\Api\Admin\AdminSimpananController;
use App\Http\Controllers\Api\Admin\AdminPpobController;
use App\Http\Controllers\Api\Admin\AdminPaymentController;

/*
|--------------------------------------------------------------------------
| API Routes — Koperasi Syariah K-Samara
|--------------------------------------------------------------------------
*/

// ── Auth (Public) ──────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',           [AuthController::class, 'login']);
    Route::post('register',        [AuthController::class, 'register']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
});

// ── Authenticated Routes ────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);

    // ── ANGGOTA ─────────────────────────────────────────────────────────────
    Route::middleware('role:anggota')->prefix('anggota')->group(function () {

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Profil
        Route::get('profil',              [ProfilController::class, 'index']);
        Route::post('profil/update',      [ProfilController::class, 'update']);
        Route::post('profil/update-foto', [ProfilController::class, 'updateFoto']);
        Route::post('profil/change-pin',  [ProfilController::class, 'changePin']);
        Route::post('profil/verify-pin',  [ProfilController::class, 'verifyPin']);

        // Simpanan
        Route::prefix('simpanan')->group(function () {
            Route::get('/',                [SimpananController::class, 'index']);
            Route::post('set-tenor',       [SimpananController::class, 'setTenor']);
            Route::post('pokok/store',     [SimpananController::class, 'storePokok']);
            Route::post('wajib/store',     [SimpananController::class, 'storeWajib']);
            Route::post('sukarela/store',  [SimpananController::class, 'storeSukarela']);
        });

        // Pinjaman
        Route::prefix('pinjaman')->group(function () {
            Route::get('/',                  [PinjamanController::class, 'index']);
            Route::post('ajukan',            [PinjamanController::class, 'ajukan']);
            Route::post('create-pin',        [PinjamanController::class, 'createPin']);
            Route::post('verify-pin',        [PinjamanController::class, 'verifyPin']);
            Route::post('process-after-pin', [PinjamanController::class, 'processAfterPin']);
            Route::get('check-active',       [PinjamanController::class, 'checkActive']);
        });

        // Cicilan
        Route::prefix('cicilan')->group(function () {
            Route::get('/',              [CicilanController::class, 'index']);
            Route::post('bayar',         [CicilanController::class, 'bayar']);
            Route::get('riwayat',        [CicilanController::class, 'riwayat']);
            Route::post('set-tenor',     [CicilanController::class, 'setTenor']);
        });

        // PPOB
        Route::prefix('ppob')->group(function () {
            Route::get('/',              [PpobController::class, 'index']);
            Route::get('produk/pulsa',   [PpobController::class, 'produkPulsa']);
            Route::get('produk/data',    [PpobController::class, 'produkData']);
            Route::get('produk/listrik', [PpobController::class, 'produkListrik']);
            Route::get('produk/ewallet', [PpobController::class, 'produkEwallet']);
            Route::post('order',         [PpobController::class, 'order']);
            Route::get('status/{orderId}', [PpobController::class, 'status']);
            Route::post('simulasi-bayar', [PpobController::class, 'simulasiBayar']);
            Route::get('riwayat',        [PpobController::class, 'riwayat']);
        });

        // Payment Gateway
        Route::prefix('payment')->group(function () {
            Route::get('riwayat',                     [PaymentGatewayController::class, 'riwayat']);
            Route::get('status/{orderId}',             [PaymentGatewayController::class, 'status']);
            Route::post('proses-cicilan',              [PaymentGatewayController::class, 'prosesCicilan']);
            Route::post('proses-simpanan',             [PaymentGatewayController::class, 'prosesSimpanan']);
            Route::post('simulasi-bayar',              [PaymentGatewayController::class, 'simulasiBayar']);
            Route::get('metode',                       [PaymentGatewayController::class, 'getMetode']);
        });
    });

    // ── ADMIN ────────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // Dashboard
        Route::get('dashboard',        [AdminDashboardController::class, 'index']);
        Route::get('dashboard/stats',  [AdminDashboardController::class, 'stats']);

        // Anggota
        Route::prefix('anggota')->group(function () {
            Route::get('/',               [AdminAnggotaController::class, 'index']);
            Route::post('/',              [AdminAnggotaController::class, 'store']);
            Route::get('{id}',            [AdminAnggotaController::class, 'show']);
            Route::put('{id}',            [AdminAnggotaController::class, 'update']);
            Route::delete('{id}',         [AdminAnggotaController::class, 'destroy']);
            Route::post('{id}/verify',    [AdminAnggotaController::class, 'verify']);
            Route::post('{id}/reject',    [AdminAnggotaController::class, 'reject']);
            Route::get('pending/list',    [AdminAnggotaController::class, 'pending']);
        });

        // Pinjaman Admin
        Route::prefix('pinjaman')->group(function () {
            Route::get('/',               [AdminPinjamanController::class, 'index']);
            Route::get('pending',         [AdminPinjamanController::class, 'pending']);
            Route::post('{jenis}/{id}/approve', [AdminPinjamanController::class, 'approve']);
            Route::post('{jenis}/{id}/reject',  [AdminPinjamanController::class, 'reject']);
        });

        // Simpanan Admin
        Route::prefix('simpanan')->group(function () {
            Route::get('/',                  [AdminSimpananController::class, 'index']);
            Route::get('pending',            [AdminSimpananController::class, 'pending']);
            Route::post('{jenis}/{id}/approve', [AdminSimpananController::class, 'approve']);
            Route::post('{jenis}/{id}/reject',  [AdminSimpananController::class, 'reject']);
        });

        // Pembayaran cicilan pending
        Route::prefix('pembayaran')->group(function () {
            Route::get('pending',            [AdminPaymentController::class, 'pendingCicilan']);
            Route::post('{id}/verifikasi',   [AdminPaymentController::class, 'verifikasi']);
            Route::post('{id}/tolak',        [AdminPaymentController::class, 'tolak']);
        });

        // PPOB Admin
        Route::prefix('ppob')->group(function () {
            Route::get('/',             [AdminPpobController::class, 'index']);
            Route::get('{id}',          [AdminPpobController::class, 'show']);
            Route::post('update-status',[AdminPpobController::class, 'updateStatus']);
            Route::get('export/csv',    [AdminPpobController::class, 'exportCsv']);
        });

        // Payment Gateway Admin
        Route::get('payment-gateway',       [AdminPaymentController::class, 'index']);
    });
});
