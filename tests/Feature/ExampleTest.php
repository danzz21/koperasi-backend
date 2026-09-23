<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Endpoint health bawaan Laravel harus tersedia.
     * (Halaman "/" memang 404 karena aplikasi ini API + panel Filament.)
     */
    public function test_the_health_endpoint_returns_a_successful_response(): void
    {
        $this->get('/up')->assertStatus(200);
    }

    public function test_the_root_page_has_no_web_route(): void
    {
        $this->get('/')->assertStatus(404);
    }
}
