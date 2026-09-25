<?php

namespace Tests\Feature;

use App\Filament\Pages\PendingAnggota;
use App\Filament\Pages\PendingPembayaran;
use App\Filament\Pages\PendingPinjaman;
use App\Filament\Pages\PendingSimpananPokok;
use App\Filament\Pages\PendingSukarela;
use App\Models\Pembayaran;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use App\Models\User;
use App\Support\PendingApprovalActions;
use App\Support\PendingApprovals;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Alur "Persetujuan Pending" di panel admin:
 * angka di kartu dashboard, isi halaman detail, dan aksi terima/tolak.
 */
class PendingApprovalsTest extends TestCase
{
    use DatabaseTransactions;

    /** Anggota pending yang dipakai bersama seluruh data pada satu test. */
    private ?User $anggota = null;

    private function pendingAnggota(): User
    {
        return $this->anggota ??= User::factory()->create([
            'role'          => 'anggota',
            'status'        => 'pending',
            'jenis_kelamin' => 'L',
            'tgl_lahir'     => '1995-01-01',
            'pekerjaan'     => 'Karyawan',
            'alamat'        => 'Jl. Contoh No. 1',
        ]);
    }

    private function pendingSimpanan(string $jenis): Simpanan
    {
        return Simpanan::create([
            'id_anggota' => $this->pendingAnggota()->id,
            'jenis'     => $jenis,
            'nominal'   => 250000,
            'bunga'     => 0,
            'status'    => 'tidak_aktif',
        ]);
    }

    private function pendingPinjaman(): Pinjaman
    {
        return Pinjaman::create([
            'id_anggota'       => $this->pendingAnggota()->id,
            'tipe'            => 'murabahah',
            'nominal'         => 2000000,
            'sisa_pinjaman'   => 2000000,
            'tenor'           => 10,
            'bunga_per_bulan' => 0,
            'angsuran'        => 200000,
            'status'          => 'pending',
        ]);
    }

    private function pendingPembayaran(): Pembayaran
    {
        static $counter = 0;
        $counter++;

        return Pembayaran::create([
            'id_anggota'   => $this->pendingAnggota()->id,
            'no_referensi' => 'CIC-TEST-'.uniqid().'-'.$counter,
            'jenis'        => 'cicilan',
            'nominal'      => 200000,
            'metode'       => 'transfer',
            'status'       => 'pending',
        ]);
    }

    public function test_counts_match_the_data_shown_on_each_page(): void
    {
        $this->pendingAnggota();
        $this->pendingSimpanan('sukarela');
        $this->pendingSimpanan('pokok');
        $this->pendingPinjaman();
        $this->pendingPembayaran();

        $counts = PendingApprovals::counts();

        $this->assertSame(1, $counts['anggota']);
        $this->assertSame(1, $counts['sukarela']);
        $this->assertSame(1, $counts['pokok']);
        $this->assertSame(1, $counts['pinjaman']);
        $this->assertSame(1, $counts['pembayaran']);
        $this->assertSame(5, $counts['total']);

        // Query tiap halaman harus menghasilkan jumlah yang sama dengan kartunya
        $this->assertSame($counts['anggota'], PendingAnggota::pendingQuery()->count());
        $this->assertSame($counts['sukarela'], PendingSukarela::pendingQuery()->count());
        $this->assertSame($counts['pokok'], PendingSimpananPokok::pendingQuery()->count());
        $this->assertSame($counts['pinjaman'], PendingPinjaman::pendingQuery()->count());
        $this->assertSame($counts['pembayaran'], PendingPembayaran::pendingQuery()->count());
    }

    public function test_active_data_is_not_counted_as_pending(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);

        Simpanan::create(['id_anggota' => $user->id, 'jenis' => 'sukarela', 'nominal' => 1000, 'bunga' => 0, 'status' => 'aktif']);
        Pinjaman::create(['id_anggota' => $user->id, 'tipe' => 'qard', 'nominal' => 1000, 'sisa_pinjaman' => 1000, 'tenor' => 1, 'bunga_per_bulan' => 0, 'angsuran' => 1000, 'status' => 'aktif']);
        Pembayaran::create(['id_anggota' => $user->id, 'no_referensi' => 'X', 'jenis' => 'cicilan', 'nominal' => 1000, 'metode' => 'tunai', 'status' => 'diverifikasi']);

        $this->assertSame(0, PendingApprovals::counts()['total']);
    }

    public function test_approve_and_reject_anggota(): void
    {
        $anggota = $this->pendingAnggota();

        $this->assertTrue(PendingApprovalActions::approve(PendingApprovalActions::ANGGOTA, $anggota));
        $this->assertSame('aktif', $anggota->fresh()->status);
        $this->assertNotNull($anggota->fresh()->profile_completed_at);

        $lain = $this->pendingAnggota();
        $this->assertTrue(PendingApprovalActions::reject(PendingApprovalActions::ANGGOTA, $lain));
        $this->assertSame('nonaktif', $lain->fresh()->status);
    }

    public function test_approve_and_reject_simpanan(): void
    {
        $sukarela = $this->pendingSimpanan('sukarela');
        PendingApprovalActions::approve(PendingApprovalActions::SUKARELA, $sukarela);
        $this->assertSame('aktif', $sukarela->fresh()->status);

        $pokok = $this->pendingSimpanan('pokok');
        PendingApprovalActions::reject(PendingApprovalActions::POKOK, $pokok);
        $this->assertSame('ditolak', $pokok->fresh()->status);
    }

    public function test_approve_and_reject_pinjaman(): void
    {
        $pinjaman = $this->pendingPinjaman();

        $this->assertTrue(PendingApprovalActions::approve(PendingApprovalActions::PINJAMAN, $pinjaman));
        $fresh = $pinjaman->fresh();
        $this->assertSame('aktif', $fresh->status);
        $this->assertNotNull($fresh->tgl_persetujuan);
        $this->assertNotNull($fresh->tgl_cair);

        $lain = $this->pendingPinjaman();
        PendingApprovalActions::reject(PendingApprovalActions::PINJAMAN, $lain);
        $this->assertSame('ditolak', $lain->fresh()->status);
    }

    public function test_approve_and_reject_pembayaran(): void
    {
        $pembayaran = $this->pendingPembayaran();

        $this->assertTrue(PendingApprovalActions::approve(PendingApprovalActions::PEMBAYARAN, $pembayaran));
        $fresh = $pembayaran->fresh();
        $this->assertSame('diverifikasi', $fresh->status);
        $this->assertNotNull($fresh->tgl_pembayaran);

        $lain = $this->pendingPembayaran();
        PendingApprovalActions::reject(PendingApprovalActions::PEMBAYARAN, $lain);
        $this->assertSame('gagal', $lain->fresh()->status);
    }
}
