<?php

namespace Tests\Feature;

use App\Filament\Pages\PendingAnggota;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\PendingPembayaran;
use App\Filament\Pages\PendingPinjaman;
use App\Filament\Pages\PendingSimpananPokok;
use App\Filament\Pages\PendingSukarela;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Halaman-halaman detail "Persetujuan Pending" harus benar-benar bisa dibuka
 * di panel admin dan aksi terima/tolaknya mengubah data.
 */
class PendingApprovalPagesTest extends TestCase
{
    use DatabaseTransactions;

    private function loginAsAdmin(): User
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        return $admin;
    }

    private function pendingAnggota(): User
    {
        return User::factory()->create([
            'role'          => 'anggota',
            'status'        => 'pending',
            'jenis_kelamin' => 'L',
            'tgl_lahir'     => '1995-01-01',
            'pekerjaan'     => 'Karyawan',
            'alamat'        => 'Jl. Contoh No. 1',
        ]);
    }

    public function test_pending_anggota_page_lists_and_approves_a_member(): void
    {
        $this->loginAsAdmin();
        $anggota = $this->pendingAnggota();

        Livewire::test(PendingAnggota::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$anggota])
            ->callTableAction('approve', $anggota);

        $this->assertSame('aktif', $anggota->fresh()->status);
    }

    public function test_pending_anggota_page_rejects_a_member(): void
    {
        $this->loginAsAdmin();
        $anggota = $this->pendingAnggota();

        Livewire::test(PendingAnggota::class)
            ->assertSuccessful()
            ->callTableAction('reject', $anggota);

        $this->assertSame('nonaktif', $anggota->fresh()->status);
    }

    public function test_other_pending_pages_render_with_empty_state(): void
    {
        $this->loginAsAdmin();

        foreach ([PendingSukarela::class, PendingSimpananPokok::class, PendingPinjaman::class, PendingPembayaran::class] as $page) {
            Livewire::test($page)->assertSuccessful();
        }
    }

    public function test_dashboard_renders_with_pending_links(): void
    {
        $this->loginAsAdmin();
        $anggota = $this->pendingAnggota();

        $data = (new Dashboard)->getViewData();

        // Angka kartu = jumlah data yang tampil di halaman detail
        $this->assertSame(1, $data['pendingAnggota']);
        $this->assertSame(PendingAnggota::pendingQuery()->count(), $data['pendingAnggota']);
        $this->assertSame(1, $data['totalPending']);
        $this->assertSame($anggota->id, PendingAnggota::pendingQuery()->first()?->id);

        // Halaman dashboard (beserta kartu pending & tautannya) harus ter-render
        Livewire::test(Dashboard::class)
            ->assertSuccessful()
            ->assertSee('Persetujuan Pending')
            ->assertSee('Anggota Baru')
            ->assertSee('Dashboard Utama');
    }

    /**
     * Render penuh lewat HTTP untuk setiap halaman pending + dashboard.
     * Ini menangkap error view/asset yang tidak terlihat di test Livewire.
     */
    public function test_pending_pages_render_over_http(): void
    {
        $this->loginAsAdmin();
        $this->pendingAnggota();

        $urls = [
            '/admin',
            '/admin/pending/anggota',
            '/admin/pending/sukarela',
            '/admin/pending/simpanan-pokok',
            '/admin/pending/pinjaman',
            '/admin/pending/pembayaran',
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);

            $this->assertTrue(
                $response->isSuccessful(),
                $url.' gagal dengan HTTP '.$response->getStatusCode()
            );
        }
    }
}
