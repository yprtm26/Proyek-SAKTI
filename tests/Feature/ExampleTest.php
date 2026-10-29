<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Halaman awal mengarahkan pengunjung ke halaman login.
     */
    public function test_halaman_awal_mengarah_ke_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }
}
