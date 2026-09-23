<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Alur onboarding: anggota yang belum lengkap datanya (mis. login Google)
 * diblokir dari fitur anggota sampai form onboarding disubmit.
 */
class OnboardingTest extends TestCase
{
    use DatabaseTransactions;

    /** Anggota yang datanya belum lengkap (tanpa jenis_kelamin/tgl_lahir/pekerjaan/alamat). */
    private function incompleteUser(): User
    {
        return User::factory()->create();
    }

    /** Anggota dengan data lengkap → model menandai profil selesai otomatis. */
    private function completeUser(): User
    {
        return User::factory()->create([
            'jenis_kelamin' => 'L',
            'tgl_lahir'     => '1990-01-01',
            'pekerjaan'     => 'Karyawan',
            'alamat'        => 'Jl. Merdeka No. 1, Bandung',
        ]);
    }

    public function test_incomplete_member_is_blocked_from_member_features(): void
    {
        $user = $this->incompleteUser();

        Sanctum::actingAs($user, ['*']);

        $this->assertTrue($user->requiresProfileCompletion());

        $this->getJson('/api/anggota/dashboard')
            ->assertForbidden()
            ->assertJsonPath('code', 'PROFILE_INCOMPLETE')
            ->assertJsonPath('needs_onboarding', true)
            ->assertJsonStructure(['message', 'missing_fields']);
    }

    public function test_complete_member_passes_the_guard(): void
    {
        $user = $this->completeUser();

        Sanctum::actingAs($user, ['*']);

        $this->assertFalse($user->requiresProfileCompletion());
        $this->assertNotNull($user->profile_completed_at);

        // Bukan 403 → guard profil terlewati (isi dasbor diuji terpisah)
        $this->assertNotSame(403, $this->getJson('/api/anggota/dashboard')->status());
    }

    public function test_onboarding_endpoint_lists_missing_fields(): void
    {
        $user = $this->incompleteUser();

        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/anggota/onboarding')
            ->assertOk()
            ->assertJsonPath('needs_onboarding', true)
            ->assertJsonStructure([
                'user'   => ['id', 'nama', 'profile_completed', 'needs_onboarding'],
                'profile',
                'missing_fields',
                'required_fields',
            ])
            ->assertJsonFragment(['nomor_ktp'])
            ->assertJsonFragment(['jenis_kelamin']);
    }

    public function test_submitting_onboarding_completes_the_profile(): void
    {
        $user = $this->incompleteUser();

        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/anggota/onboarding', [
            'nama_lengkap'          => 'Budi Google',
            'nomor_ktp'             => '3271234567890001',
            'nomor_hp'              => '081298765432',
            'jenis_kelamin'         => 'L',
            'tgl_lahir'             => '1995-05-20',
            'pekerjaan'             => 'Wiraswasta',
            'alamat'                => 'Jl. Contoh No. 10, Bandung',
            'password'              => 'rahasia12345',
            'password_confirmation' => 'rahasia12345',
        ])
            ->assertOk()
            ->assertJsonPath('user.needs_onboarding', false)
            ->assertJsonPath('user.profile_completed', true);

        $user->refresh();

        $this->assertNotNull($user->profile_completed_at);
        $this->assertSame('3271234567890001', $user->nomor_ktp);
        $this->assertSame('081298765432', $user->nomor_hp);
        $this->assertTrue(Hash::check('rahasia12345', $user->password));

        $this->getJson('/api/anggota/onboarding')
            ->assertOk()
            ->assertJsonPath('needs_onboarding', false);
    }

    public function test_onboarding_validates_required_fields(): void
    {
        $user = $this->incompleteUser();

        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/anggota/onboarding', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nama_lengkap', 'nomor_ktp', 'nomor_hp',
                'jenis_kelamin', 'tgl_lahir', 'pekerjaan', 'alamat',
            ]);
    }

    public function test_onboarding_rejects_invalid_values(): void
    {
        $user = $this->incompleteUser();

        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/anggota/onboarding', [
            'nama_lengkap'  => 'X',
            'nomor_ktp'     => '123',
            'nomor_hp'      => 'bukan-nomor',
            'jenis_kelamin' => 'X',
            'tgl_lahir'     => '2999-01-01',
            'pekerjaan'     => 'X',
            'alamat'        => 'X',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nomor_ktp', 'nomor_hp', 'jenis_kelamin', 'tgl_lahir']);
    }

    public function test_onboarding_rejects_duplicate_ktp(): void
    {
        $existing = $this->completeUser();
        $user     = $this->incompleteUser();

        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/anggota/onboarding', [
            'nama_lengkap'  => 'Ktp Duplikat',
            'nomor_ktp'     => $existing->nomor_ktp,
            'nomor_hp'      => '081211112222',
            'jenis_kelamin' => 'P',
            'tgl_lahir'     => '1999-09-09',
            'pekerjaan'     => 'Guru',
            'alamat'        => 'Jl. Duplikat No. 2',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nomor_ktp']);
    }

    public function test_admin_accounts_never_require_onboarding(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertFalse($admin->requiresProfileCompletion());
        $this->assertTrue($admin->hasCompletedProfile());
    }

    public function test_login_response_carries_onboarding_flag(): void
    {
        $user = $this->completeUser();

        $this->postJson('/api/auth/login', [
            'username' => $user->username,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('user.needs_onboarding', false)
            ->assertJsonStructure([
                'token',
                'user' => ['profile_completed', 'needs_onboarding', 'missing_fields'],
            ]);
    }
}
