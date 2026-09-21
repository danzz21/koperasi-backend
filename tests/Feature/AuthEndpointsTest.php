<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Regression test endpoint auth Google & passkey.
 *
 * Data DB tidak dihapus: memakai DatabaseTransactions (di-rollback otomatis)
 * dan factory yang membuat user sementara dengan username/email unik.
 */
class AuthEndpointsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_passkey_register_options_requires_authentication(): void
    {
        $this->postJson('/api/auth/passkey/register/options')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_passkey_register_options_returns_valid_payload(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/auth/passkey/register/options')
            ->assertOk()
            ->assertJsonStructure([
                'options' => [
                    'challenge',
                    'rp' => ['id', 'name'],
                    'user' => ['id', 'name', 'displayName'],
                    'pubKeyCredParams',
                    'timeout',
                    'attestation',
                    'authenticatorSelection',
                ],
            ]);

        $options = $response->json('options');

        $this->assertNotEmpty($options['challenge']);
        $this->assertNotEmpty($options['user']['id']);
    }

    public function test_passkey_register_verify_rejects_expired_challenge(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/auth/passkey/register/verify', [
            'credential' => ['response' => []],
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Sesi pendaftaran kedaluwarsa. Coba ulangi.');
    }

    public function test_passkey_login_options_is_public_and_scoped_by_username(): void
    {
        $user = User::factory()->create();

        // Tanpa username → discoverable (allowCredentials kosong)
        $anonymous = $this->postJson('/api/auth/passkey/login/options')->assertOk();

        $anonymous->assertJsonStructure(['ceremony_id', 'options' => ['challenge', 'rpId', 'userVerification']]);
        $this->assertSame([], $anonymous->json('options.allowCredentials'));

        // Username tak dikenal → 422
        $this->postJson('/api/auth/passkey/login/options', ['username' => 'tidak-ada'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Akun tidak ditemukan.');

        // Username dikenal tapi belum punya passkey → allowCredentials tetap kosong
        $scoped = $this->postJson('/api/auth/passkey/login/options', ['username' => $user->username])
            ->assertOk();

        $this->assertSame([], $scoped->json('options.allowCredentials'));
    }

    public function test_passkey_login_verify_rejects_unknown_ceremony(): void
    {
        $this->postJson('/api/auth/passkey/login/verify', ['ceremony_id' => 'tidak-ada'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Sesi login kedaluwarsa. Coba lagi.');
    }

    public function test_passkey_device_list_requires_authentication(): void
    {
        $this->getJson('/api/auth/passkey')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_google_login_validates_credential(): void
    {
        $this->postJson('/api/auth/google')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'ID token Google wajib dikirim.');
    }

    public function test_google_login_rejects_forged_token(): void
    {
        // Token palsu gagal diverifikasi ke Google → 401, tanpa memanggil database user.
        $this->postJson('/api/auth/google', ['credential' => 'header.payload.signature'])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Token Google tidak valid atau sudah kedaluwarsa.');
    }
}
