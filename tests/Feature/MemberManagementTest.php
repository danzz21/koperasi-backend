<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Simpanan;
use App\Models\User;
use App\Support\MemberAccountActions;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Manajemen anggota di panel admin: detail, edit, reset password,
 * nonaktifkan/aktifkan, dan hapus.
 */
class MemberManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function anggota(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role'          => 'anggota',
            'status'        => 'aktif',
            'jenis_kelamin' => 'L',
            'tgl_lahir'     => '1995-01-01',
            'pekerjaan'     => 'Karyawan',
            'alamat'        => 'Jl. Contoh No. 1',
        ], $overrides));
    }

    /* ─────────────────── Reset password ─────────────────── */

    public function test_reset_password_ke_default_dan_token_dicabut(): void
    {
        $user = $this->anggota();
        $user->tokens()->create(['name' => 'hp-anggota', 'token' => 'abc', 'abilities' => ['anggota']]);

        $this->assertSame('123', MemberAccountActions::DEFAULT_PASSWORD);

        MemberAccountActions::resetPassword($user);

        $user->refresh();

        $this->assertTrue(Hash::check('123', $user->password));
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_reset_password_tidak_berpengaruh_ke_anggota_lain(): void
    {
        $user = $this->anggota(['password' => Hash::make('rahasia123')]);
        $lain = $this->anggota(['password' => Hash::make('rahasia456')]);

        MemberAccountActions::resetPassword($user);

        $this->assertTrue(Hash::check('123', $user->fresh()->password));
        $this->assertTrue(Hash::check('rahasia456', $lain->fresh()->password));
    }

    /* ─────────────────── Nonaktif / aktif ─────────────────── */

    public function test_anggota_nonaktif_tidak_bisa_login_lagi(): void
    {
        $user = $this->anggota(['username' => 'anggota_nonaktif', 'email' => 'nonaktif@koperasi.test']);

        MemberAccountActions::setActive($user, false);
        $this->assertSame('nonaktif', $user->fresh()->status);

        $this->postJson('/api/auth/login', ['username' => 'anggota_nonaktif', 'password' => 'password'])
            ->assertForbidden();
    }

    public function test_anggota_bisa_diaktifkan_kembali(): void
    {
        $user = $this->anggota(['status' => 'nonaktif']);

        MemberAccountActions::setActive($user, true);

        $this->assertSame('aktif', $user->fresh()->status);
    }

    /* ─────────────────── Hapus anggota ─────────────────── */

    public function test_hapus_anggota_beserta_relasinya(): void
    {
        $user     = $this->anggota();
        $walletId = DB::table('ewallets')->insertGetId(['id_anggota' => $user->id, 'saldo' => 100000]);

        $this->assertTrue(MemberAccountActions::delete($user));

        $this->assertNull(User::find($user->id));
        // ewallets memakai foreign key cascade
        $this->assertNull(DB::table('ewallets')->find($walletId));
    }

    /* ─────────────────── Ringkasan detail ─────────────────── */

    public function test_ringkasan_menampilkan_saldo_dan_aktivitas(): void
    {
        $user = $this->anggota();

        Simpanan::create(['id_anggota' => $user->id, 'jenis' => 'pokok', 'nominal' => 500000, 'bunga' => 0, 'status' => 'aktif']);
        Simpanan::create(['id_anggota' => $user->id, 'jenis' => 'sukarela', 'nominal' => 250000, 'bunga' => 0, 'status' => 'aktif']);
        DB::table('ewallets')->insert(['id_anggota' => $user->id, 'saldo' => 75000]);

        $summary = MemberAccountActions::summary($user);

        $this->assertSame(500000.0, $summary['simpanan_pokok']);
        $this->assertSame(250000.0, $summary['simpanan_sukarela']);
        $this->assertSame(750000.0, $summary['total_simpanan']);
        $this->assertSame(75000.0, $summary['saldo_ewallet']);
        $this->assertSame(0, $summary['pinjaman_aktif']);
        $this->assertSame(0, $summary['jumlah_passkey']);
    }

    /* ─────────────────── Panel admin (Livewire) ─────────────────── */

    public function test_daftar_anggota_memiliki_aksi_lengkap(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Filament::setCurrentPanel('admin');

        Livewire::test(ListUsers::class)
            ->assertSuccessful()
            ->assertTableActionExists('view')
            ->assertTableActionExists('edit')
            ->assertTableActionExists('resetPassword')
            ->assertTableActionExists('nonaktifkan')
            ->assertTableActionExists('aktifkan')
            ->assertTableActionExists('delete');
    }

    public function test_aksi_reset_password_dari_panel_berfungsi(): void
    {
        $user = $this->anggota();
        $this->actingAs(User::factory()->admin()->create());
        Filament::setCurrentPanel('admin');

        Livewire::test(ListUsers::class)->callTableAction('resetPassword', $user);

        $this->assertTrue(Hash::check('123', $user->fresh()->password));
    }

    public function test_aksi_nonaktifkan_dari_panel_berfungsi(): void
    {
        $user = $this->anggota();
        $this->actingAs(User::factory()->admin()->create());
        Filament::setCurrentPanel('admin');

        Livewire::test(ListUsers::class)->callTableAction('nonaktifkan', $user);

        $this->assertSame('nonaktif', $user->fresh()->status);
    }

    public function test_token_lama_ditolak_setelah_anggota_dinonaktifkan(): void
    {
        $user = $this->anggota();
        Sanctum::actingAs($user, ['*']);

        // Selama aktif, endpoint anggota boleh diakses
        $this->getJson('/api/anggota/ewallet')->assertOk();

        MemberAccountActions::setActive($user, false);

        // Token lama ikut tidak berlaku
        $this->getJson('/api/anggota/ewallet')
            ->assertForbidden()
            ->assertJsonPath('code', 'ACCOUNT_DISABLED');

        // Setelah diaktifkan lagi, akses kembali normal
        $user->refresh();
        MemberAccountActions::setActive($user, true);

        // Guard menyimpan instance user sebelumnya, jadi set ulang
        Sanctum::actingAs($user->fresh(), ['*']);
        $this->getJson('/api/anggota/ewallet')->assertOk();
    }

    public function test_halaman_anggota_render_punya_http(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get('/admin/users');

        $this->assertTrue(
            $response->isSuccessful(),
            'Halaman anggota gagal dengan HTTP '.$response->getStatusCode()
        );
    }
}