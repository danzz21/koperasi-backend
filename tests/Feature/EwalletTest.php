<?php

namespace Tests\Feature;

use App\Filament\Pages\DashboardPpob;
use App\Models\EwalletTransaction;
use App\Support\EwalletService;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Alur E-Wallet Cooperation:
 * topup (menunggu verifikasi) → disetujui admin → dipakai bayar PPOB.
 */
class EwalletTest extends TestCase
{
    use DatabaseTransactions;

    private function anggota(): User
    {
        return User::factory()->create([
            'role'          => 'anggota',
            'status'        => 'aktif',
            'jenis_kelamin' => 'L',
            'tgl_lahir'     => '1995-01-01',
            'pekerjaan'     => 'Karyawan',
            'alamat'        => 'Jl. Contoh No. 1',
        ]);
    }

    private function isiSaldo(User $user, float $nominal = 200000): void
    {
        $trx = app(EwalletService::class)->buatPermintaanTopup($user->id, $nominal, 'transfer');
        app(EwalletService::class)->setujuiTopup($trx);
    }

    public function test_saldo_awal_saldo_nol_saat_dompet_baru_dibuat(): void
    {
        $user = $this->anggota();
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/anggota/ewallet')
            ->assertOk()
            ->assertJsonPath('data.saldo', 0)
            ->assertJsonPath('data.topup_pending', 0)
            ->assertJsonStructure(['data' => ['saldo', 'topup_pending', 'riwayat_singkat', 'total_topup', 'total_pembayaran']]);
    }

    public function test_permintaan_topup_berstatus_pending_sampai_disetujui(): void
    {
        $user = $this->anggota();
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/anggota/ewallet/topup', ['nominal' => 150000, 'metode' => 'transfer_bca'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        // Saldo belum berubah sebelum disetujui
        $this->getJson('/api/anggota/ewallet')
            ->assertJsonPath('data.saldo', 0)
            ->assertJsonPath('data.topup_pending', 1);

        $trx = EwalletTransaction::where('id_anggota', $user->id)->latest('id')->first();
        app(EwalletService::class)->setujuiTopup($trx);

        $this->getJson('/api/anggota/ewallet')
            ->assertJsonPath('data.saldo', 150000)
            ->assertJsonPath('data.topup_pending', 0)
            ->assertJsonPath('data.total_topup', 150000);
    }

    public function test_topup_ditolak_tidak_mengubah_saldo(): void
    {
        $user = $this->anggota();
        Sanctum::actingAs($user, ['*']);

        $trx = app(EwalletService::class)->buatPermintaanTopup($user->id, 75000, 'qr');
        app(EwalletService::class)->tolakTopup($trx);

        $this->assertSame('gagal', $trx->fresh()->status);
        $this->getJson('/api/anggota/ewallet')->assertJsonPath('data.saldo', 0);
    }

    public function test_validasi_nominal_topup(): void
    {
        Sanctum::actingAs($this->anggota(), ['*']);

        $this->postJson('/api/anggota/ewallet/topup', ['nominal' => 5000, 'metode' => 'transfer'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nominal']);

        $this->postJson('/api/anggota/ewallet/topup', ['nominal' => 99999999, 'metode' => 'transfer'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nominal']);

        $this->postJson('/api/anggota/ewallet/topup', ['nominal' => 20000, 'metode' => 'dana'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['metode']);
    }

    public function test_anggota_belum_lengkap_dikunci_dari_endpoint_ewallet(): void
    {
        // Pendaftaran lewat formulir (belum lengkapi profil) → belum boleh pakai e-wallet
        $pending = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        Sanctum::actingAs($pending, ['*']);

        $this->getJson('/api/anggota/ewallet')
            ->assertForbidden()
            ->assertJsonPath('code', 'PROFILE_INCOMPLETE');
    }

    public function test_pembayaran_ppob_dengan_saldo_ewallet(): void
    {
        $user = $this->anggota();
        $this->isiSaldo($user, 200000);
        Sanctum::actingAs($user, ['*']);

        $before = DB::table('ewallets')->where('id_anggota', $user->id)->value('saldo');

        $response = $this->postJson('/api/anggota/ppob/order', [
            'kode_produk'   => 'TSEL10',   // harga 11.000
            'jenis_produk'  => 'pulsa',
            'nomor_tujuan'  => '081234567890',
            'payment_method' => 'ewallet',
        ])->assertCreated();

        $this->assertTrue($response->json('paid'));
        // Saldo 200.000 dipotong 11.000 → 189.000
        $this->assertSame(189000.0, (float) app(EwalletService::class)->saldo($user->id));
        $this->assertEqualsWithDelta((float) $before, 200000, 0.01);

        // Transaksi PPOB langsung lunas & tercatat di mutasi e-wallet
        $ppob = DB::table('ppob_transaksi')->where('id_anggota', $user->id)->latest('id_transaksi')->first();
        $this->assertSame('success', $ppob->status);

        $payment = DB::table('payment_gateway_transaksi')->where('id_anggota', $user->id)->latest('id')->first();
        $this->assertSame('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);

        $mutasi = EwalletTransaction::where('id_anggota', $user->id)->where('tipe', 'bayar')->first();
        $this->assertNotNull($mutasi);
        $this->assertSame(11000.0, (float) $mutasi->nominal);
        $this->assertSame(189000.0, (float) $mutasi->balance_after);
    }

    public function test_pembayaran_ditolak_bila_saldo_ewallet_kurang(): void
    {
        $user = $this->anggota();
        $this->isiSaldo($user, 12000);
        Sanctum::actingAs($user, ['*']);

        $jumlahAwal = DB::table('ppob_transaksi')->where('id_anggota', $user->id)->count();

        $this->postJson('/api/anggota/ppob/order', [
            'kode_produk'   => 'TSEL50',   // harga 52.000 > saldo
            'jenis_produk'  => 'pulsa',
            'nomor_tujuan'  => '081234567890',
            'payment_method' => 'ewallet',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Saldo E-Wallet tidak cukup. Topup saldo dulu sebelum membeli produk ini.')
            ->assertJsonPath('kurang', 40000);

        // Tidak ada transaksi yatim & saldo tetap
        $this->assertSame($jumlahAwal, DB::table('ppob_transaksi')->where('id_anggota', $user->id)->count());
        $this->assertSame(12000.0, (float) app(EwalletService::class)->saldo($user->id));
    }

    public function test_dashboard_ppob_menampilkan_statistik_ewallet(): void
    {
        $user = $this->anggota();
        $this->isiSaldo($user, 300000);
        Sanctum::actingAs(User::factory()->admin()->create(), ['*']);

        $data = (new DashboardPpob)->getViewData();

        $this->assertArrayHasKey('ewalletStats', $data);
        $this->assertSame(300000.0, $data['ewalletStats']['total_saldo']);
        $this->assertSame(300000.0, $data['ewalletStats']['topup_bulan']);
        $this->assertSame(0, $data['ewalletStats']['pending']);

        // Permintaan baru → muncul di daftar pending dashboard
        app(EwalletService::class)->buatPermintaanTopup($user->id, 50000, 'transfer_bri');
        $data = (new DashboardPpob)->getViewData();
        $this->assertSame(1, $data['ewalletStats']['pending']);
        $this->assertCount(1, $data['ewalletPending']);
    }

    public function test_admin_menyetujui_topup_dari_dashboard(): void
    {
        $user  = $this->anggota();
        $trx   = app(EwalletService::class)->buatPermintaanTopup($user->id, 120000, 'transfer');
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        (new DashboardPpob)->approveTopup($trx->id);

        $this->assertSame('sukses', $trx->fresh()->status);
        $this->assertSame(120000.0, (float) app(EwalletService::class)->saldo($user->id));

        // Menolak permintaan yang sudah diproses tidak boleh menggandakan saldo
        (new DashboardPpob)->approveTopup($trx->id);
        $this->assertSame(120000.0, (float) app(EwalletService::class)->saldo($user->id));
    }

    public function test_tombol_setujui_dari_dashboard_berfungsi(): void
    {
        $user = $this->anggota();
        $trx  = app(EwalletService::class)->buatPermintaanTopup($user->id, 80000, 'transfer_bsi');

        $this->actingAs(User::factory()->admin()->create());
        Filament::setCurrentPanel('admin');

        Livewire::test(DashboardPpob::class)
            ->assertSuccessful()
            ->assertSee('Tracking E-Wallet')
            ->assertSee('Permintaan Topup Menunggu Verifikasi')
            ->call('approveTopup', $trx->id);

        $this->assertSame('sukses', $trx->fresh()->status);
        $this->assertSame(80000.0, (float) app(EwalletService::class)->saldo($user->id));
    }

    public function test_halaman_dashboard_ppob_render_punya_http(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get('/admin/dashboard-ppob');

        $this->assertTrue(
            $response->isSuccessful(),
            'Dashboard PPOB gagal dengan HTTP '.$response->getStatusCode()
        );
        $response->assertSee('Tracking E-Wallet');
    }
}
